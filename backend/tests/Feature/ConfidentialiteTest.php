<?php

namespace Tests\Feature;

use App\Models\Preuve;
use App\Models\Signalement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\FabriqueDossiers;
use Tests\Concerns\FabriqueFichiers;
use Tests\TestCase;

/**
 * Parcours 4 : ce que l'API publique ne doit jamais révéler, et ce que les
 * journaux ne doivent jamais contenir.
 */
class ConfidentialiteTest extends TestCase
{
    use FabriqueDossiers;
    use FabriqueFichiers;
    use RefreshDatabase;

    private const IP_CITOYEN = '203.0.113.77';

    private const EMAIL_CITOYEN = 'lanceur.alerte@exemple.ga';

    private const MARQUEUR = 'MARQUEURSECRETDESFAITS';

    private string $fichierLog;

    /** Clés qui ne doivent jamais figurer dans une réponse publique. */
    private const CLES_INTERDITES = [
        'id', 'ip', 'ip_address', 'user_agent', 'email', 'email_contact', 'agent', 'agent_id',
        'user', 'user_id', 'chemin', 'path', 'password', 'token', 'nom_fichier', 'description',
        'ville', 'historiques', 'preuves_detail', 'trace', 'file', 'line', 'exception',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();

        $this->fichierLog = tempnam(sys_get_temp_dir(), 'log');
        config([
            'logging.default' => 'single',
            'logging.channels.single.path' => $this->fichierLog,
            'logging.channels.single.level' => 'debug',
        ]);
        $this->app['log']->forgetChannel('single');
    }

    protected function tearDown(): void
    {
        @unlink($this->fichierLog);
        parent::tearDown();
    }

    private function parIpDuCitoyen(): static
    {
        return $this->withServerVariables(['REMOTE_ADDR' => self::IP_CITOYEN])
            ->withHeader('User-Agent', 'NavigateurDuCitoyen/9.9');
    }

    /** @param  array<mixed>  $donnees */
    private function aplatirCles(array $donnees): array
    {
        $cles = [];
        array_walk_recursive($donnees, fn () => null);
        $parcours = function ($noeud) use (&$parcours, &$cles) {
            foreach ((array) $noeud as $cle => $valeur) {
                if (is_string($cle)) {
                    $cles[] = $cle;
                }
                if (is_array($valeur) || is_object($valeur)) {
                    $parcours($valeur);
                }
            }
        };
        $parcours($donnees);

        return $cles;
    }

    private function assertReponsePublique(\Illuminate\Testing\TestResponse $reponse, string $contexte): void
    {
        $corps = $reponse->getContent();
        $json = json_decode($corps, true);

        if (is_array($json)) {
            $interdites = array_intersect(self::CLES_INTERDITES, $this->aplatirCles($json));
            $this->assertSame([], array_values($interdites), "{$contexte} : clé(s) interne(s) exposée(s).");
        }

        foreach ([self::IP_CITOYEN, self::EMAIL_CITOYEN, self::MARQUEUR, 'NavigateurDuCitoyen',
            'Jean Mboumba', 'jean@cnlcei.ga', 'App\\', '/home/', 'vendor/', 'storage/', 'preuves/'] as $secret) {
            $this->assertStringNotContainsString($secret, $corps, "{$contexte} : « {$secret} » exposé.");
        }

        $this->assertNull($reponse->headers->get('Set-Cookie'), "{$contexte} : un cookie suivrait le citoyen.");
        $this->assertNull($reponse->headers->get('X-Powered-By'));
    }

    public function test_aucune_reponse_publique_ne_contient_d_ip_d_identite_ou_de_donnee_interne(): void
    {
        $agent = $this->utilisateur('agent', ['name' => 'Jean Mboumba', 'email' => 'jean@cnlcei.ga']);

        $depot = $this->parIpDuCitoyen()->postJson('/api/signalements', $this->corpsSignalement([
            'description' => self::MARQUEUR . str_repeat(' faits', 20),
            'email_contact' => self::EMAIL_CITOYEN,
            'fichiers' => [UploadedFile::fake()->create('rapport_de_jean.pdf', 10, 'application/pdf')],
        ]));
        $depot->assertCreated();
        $this->assertReponsePublique($depot, 'dépôt');
        $code = $depot->json('code');

        // Le dossier est ensuite attribué et commenté par l'administration.
        Signalement::where('code', $code)->update([
            'agent_id' => $agent->id, 'statut' => 'en_examen', 'message_agent' => 'Dossier en cours.',
        ]);

        $this->assertReponsePublique($this->parIpDuCitoyen()->getJson("/api/signalements/{$code}/statut")->assertOk(), 'suivi');
        $this->assertReponsePublique($this->parIpDuCitoyen()->getJson('/api/signalements/SG-ZZZZ-ZZZZ/statut')->assertNotFound(), 'suivi 404');
        $this->assertReponsePublique($this->parIpDuCitoyen()->getJson('/api/stats/publiques')->assertOk(), 'statistiques');
        $this->assertReponsePublique(
            $this->parIpDuCitoyen()->postJson("/api/signalements/{$code}/preuves", [
                'fichiers' => [UploadedFile::fake()->create('autre.pdf', 10, 'application/pdf')],
            ])->assertCreated(),
            'ajout de preuves'
        );
        $this->assertReponsePublique(
            $this->parIpDuCitoyen()->postJson('/api/signalements', [])->assertUnprocessable(),
            'erreur de validation'
        );
    }

    public function test_le_suivi_ne_retourne_que_les_champs_prevus(): void
    {
        $dossier = $this->dossier(['agent_id' => $this->utilisateur()->id, 'email_contact' => self::EMAIL_CITOYEN]);

        $cles = array_keys($this->getJson("/api/signalements/{$dossier->code}/statut")->assertOk()->json());
        sort($cles);

        $this->assertSame(['categorie', 'code', 'message_agent', 'province', 'statut', 'updated_at'], $cles);
    }

    public function test_les_statistiques_publiques_n_exposent_aucun_dossier_individuel(): void
    {
        $dossier = $this->dossier(['code' => 'SG-STAT-0001', 'email_contact' => self::EMAIL_CITOYEN]);

        $corps = $this->getJson('/api/stats/publiques')->assertOk()->getContent();

        $this->assertStringNotContainsString($dossier->code, $corps);
        $this->assertStringNotContainsString(self::EMAIL_CITOYEN, $corps);
        $this->assertStringNotContainsString('Faits dénoncés', $corps);
    }

    public function test_une_erreur_interne_en_production_ne_revele_ni_trace_ni_chemin(): void
    {
        config(['app.debug' => false]);
        Route::post('/api/_boom', fn () => throw new \RuntimeException('Secret interne /home/app/.env'));

        $reponse = $this->postJson('/api/_boom', ['email_contact' => self::EMAIL_CITOYEN])->assertStatus(500);

        $this->assertStringNotContainsString('Secret interne', $reponse->getContent());
        $this->assertStringNotContainsString('/home/', $reponse->getContent());
        $this->assertStringNotContainsString('vendor', $reponse->getContent());
    }

    public function test_aucune_table_ne_conserve_l_ip_ou_le_user_agent_du_citoyen(): void
    {
        $this->parIpDuCitoyen()->postJson('/api/signalements', $this->corpsSignalement([
            'fichiers' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
        ]))->assertCreated();
        $this->parIpDuCitoyen()->getJson('/api/signalements/SG-ZZZZ-ZZZZ/statut');

        $tables = DB::select("select name from sqlite_master where type = 'table' and name not like 'sqlite_%'");
        foreach ($tables as $table) {
            $contenu = json_encode(DB::table($table->name)->get());
            $this->assertStringNotContainsString(self::IP_CITOYEN, $contenu, "Table {$table->name}");
            $this->assertStringNotContainsString('NavigateurDuCitoyen', $contenu, "Table {$table->name}");
        }
    }

    public function test_les_journaux_ne_contiennent_aucune_donnee_sensible(): void
    {
        $superviseur = $this->utilisateur('superviseur', ['name' => 'Directeur Secret', 'email' => 'directeur@cnlcei.ga']);

        // 1. Dépôt par le citoyen (avec un PDF : le nettoyeur peut journaliser).
        $depot = $this->parIpDuCitoyen()->postJson('/api/signalements', $this->corpsSignalement([
            'description' => self::MARQUEUR . str_repeat(' faits', 20),
            'email_contact' => self::EMAIL_CITOYEN,
            'fichiers' => [
                UploadedFile::fake()->create('rapport_de_jean_mboumba.pdf', 10, 'application/pdf'),
                $this->fichierReel('photo.jpg', $this->octetsJpegAvecGps(), 'image/jpeg'),
            ],
        ]))->assertCreated();
        $code = $depot->json('code');

        // 2. Suivi, code inconnu et erreur de validation.
        $this->parIpDuCitoyen()->getJson("/api/signalements/{$code}/statut")->assertOk();
        $this->parIpDuCitoyen()->getJson('/api/signalements/SG-ZZZZ-ZZZZ/statut')->assertNotFound();
        $this->parIpDuCitoyen()->postJson('/api/signalements', ['email_contact' => 'invalide'])->assertUnprocessable();

        // 3. Connexion refusée puis réussie, consultation, changement de statut.
        $this->postJson('/api/auth/login', ['email' => $superviseur->email, 'password' => 'MotDePasseFaux!1'])->assertUnauthorized();
        $token = $this->connecter($superviseur);

        $dossier = Signalement::where('code', $code)->firstOrFail();
        $preuve = Preuve::where('signalement_id', $dossier->id)->firstOrFail();
        $this->avecToken($token)->get("/api/admin/signalements/{$dossier->id}/preuves/{$preuve->id}")->assertOk();
        $this->avecToken($token)->patchJson("/api/admin/signalements/{$dossier->id}", [
            'statut' => 'en_examen', 'message_agent' => 'MESSAGEAGENTSECRET',
        ])->assertOk();

        // 4. Une exception non gérée, requête contenant des données sensibles.
        Route::post('/api/_boom', fn () => throw new \RuntimeException('Échec technique'));
        $this->avecToken($token)->postJson('/api/_boom', [
            'email_contact' => self::EMAIL_CITOYEN, 'password' => self::MOT_DE_PASSE, 'description' => self::MARQUEUR,
        ])->assertStatus(500);

        $journal = (string) file_get_contents($this->fichierLog);
        $this->assertNotSame('', $journal, 'Le journal ne devrait pas être vide : le scénario doit au moins journaliser la consultation de la preuve.');

        $interdits = [
            'IP du citoyen' => self::IP_CITOYEN,
            'user-agent' => 'NavigateurDuCitoyen',
            'email du citoyen' => self::EMAIL_CITOYEN,
            'contenu du signalement' => self::MARQUEUR,
            'message de l\'agent' => 'MESSAGEAGENTSECRET',
            'nom de fichier d\'origine' => 'mboumba',
            'code de suivi' => $code,
            'mot de passe' => self::MOT_DE_PASSE,
            'mot de passe erroné' => 'MotDePasseFaux',
            'token' => explode('|', $token)[1],
            'email de l\'agent' => $superviseur->email,
            'nom de l\'agent' => 'Directeur Secret',
            'coordonnées GPS' => 'GPSLatitude',
        ];
        foreach ($interdits as $nature => $valeur) {
            $this->assertStringNotContainsString($valeur, $journal, "Le journal contient : {$nature}.");
        }
    }
}
