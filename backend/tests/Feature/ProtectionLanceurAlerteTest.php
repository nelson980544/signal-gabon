<?php

namespace Tests\Feature;

use App\Models\Preuve;
use App\Models\Signalement;
use App\Models\User;
use App\Services\MetadataSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ces tests gardent les promesses faites au citoyen sur les pages publiques.
 * Chacun correspond à une phrase affichée dans l'application.
 */
class ProtectionLanceurAlerteTest extends TestCase
{
    use RefreshDatabase;

    private function signalementValide(array $extra = []): array
    {
        return array_merge([
            'categorie' => 'Marchés publics',
            'description' => str_repeat('Faits dénoncés avec suffisamment de détails. ', 3),
            'date_faits' => '2026-05-10',
            'province' => 'Estuaire',
            'ville' => 'Libreville',
        ], $extra);
    }

    /**
     * Un PDF valide portant un auteur, comme en produit tout traitement de
     * texte. La table xref doit être correcte, sinon exiftool refuse le
     * fichier et le test ne prouverait rien.
     */
    private function pdfAvecAuteur(string $auteur): string
    {
        $objets = [
            '<</Type/Catalog/Pages 2 0 R>>',
            '<</Type/Pages/Kids[3 0 R]/Count 1>>',
            '<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>',
            "<</Author($auteur)/Creator($auteur)/Producer($auteur)>>",
        ];

        $pdf = "%PDF-1.4\n";
        $positions = [];

        foreach ($objets as $i => $objet) {
            $positions[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $objet . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref' . "\n" . '0 ' . (count($objets) + 1) . "\n" . "0000000000 65535 f \n";
        foreach ($positions as $position) {
            $pdf .= sprintf("%010d 00000 n \n", $position);
        }
        $pdf .= 'trailer<</Size ' . (count($objets) + 1) . '/Root 1 0 R/Info 4 0 R>>'
            . "\nstartxref\n" . $xref . "\n%%EOF\n";

        $chemin = tempnam(sys_get_temp_dir(), 'preuve') . '.pdf';
        file_put_contents($chemin, $pdf);

        return $chemin;
    }

    public function test_le_nom_d_origine_du_fichier_n_est_pas_conserve(): void
    {
        Storage::fake();

        $this->postJson('/api/signalements', $this->signalementValide([
            'fichiers' => [UploadedFile::fake()->create('rapport_de_jean_mboumba.pdf', 20, 'application/pdf')],
        ]))->assertCreated();

        $preuve = Preuve::firstOrFail();

        $this->assertSame('Preuve 1.pdf', $preuve->nom_fichier);
        $this->assertStringNotContainsString('jean', strtolower($preuve->nom_fichier));
    }

    public function test_les_preuves_ajoutees_ensuite_continuent_la_numerotation(): void
    {
        Storage::fake();

        $reponse = $this->postJson('/api/signalements', $this->signalementValide([
            'fichiers' => [UploadedFile::fake()->create('premier.pdf', 10, 'application/pdf')],
        ]))->assertCreated();

        $code = $reponse->json('code');

        $this->postJson("/api/signalements/{$code}/preuves", [
            'fichiers' => [UploadedFile::fake()->create('second.pdf', 10, 'application/pdf')],
        ])->assertCreated();

        $this->assertSame(
            ['Preuve 1.pdf', 'Preuve 2.pdf'],
            Preuve::orderBy('id')->pluck('nom_fichier')->all()
        );
    }

    public function test_l_email_de_contact_est_chiffre_en_base(): void
    {
        Storage::fake();

        $this->postJson('/api/signalements', $this->signalementValide([
            'email_contact' => 'temoin@exemple.ga',
        ]))->assertCreated();

        $brut = DB::table('signalements')->value('email_contact');

        $this->assertNotSame('temoin@exemple.ga', $brut);
        $this->assertStringNotContainsString('temoin', $brut);
        // Et l'application le relit correctement.
        $this->assertSame('temoin@exemple.ga', Signalement::firstOrFail()->email_contact);
    }

    public function test_les_metadonnees_d_un_pdf_sont_supprimees(): void
    {
        $exiftool = trim((string) shell_exec('which exiftool 2>/dev/null'));

        if ($exiftool === '') {
            $this->markTestSkipped('exiftool absent de cet environnement.');
        }

        $chemin = $this->pdfAvecAuteur('Jean Mboumba');
        $this->assertStringContainsString('Jean Mboumba', file_get_contents($chemin));

        MetadataSanitizer::strip($chemin);

        $this->assertStringNotContainsString('Jean Mboumba', file_get_contents($chemin));
        unlink($chemin);
    }

    /**
     * Cas dégradé : un fichier qu'exiftool ne sait pas lire passe quand même,
     * avec une trace dans le journal. C'est la limite que la page
     * « Protection » annonce au citoyen, et elle ne doit pas faire échouer un
     * dépôt de signalement.
     */
    public function test_un_fichier_illisible_ne_bloque_pas_le_depot(): void
    {
        $chemin = tempnam(sys_get_temp_dir(), 'casse') . '.pdf';
        file_put_contents($chemin, "%PDF-1.4\nfichier tronqué sans table xref\n");

        MetadataSanitizer::strip($chemin);

        $this->assertFileExists($chemin);
        unlink($chemin);
    }

    public function test_les_metadonnees_d_une_image_sont_supprimees(): void
    {
        $chemin = tempnam(sys_get_temp_dir(), 'photo') . '.jpg';
        $image = imagecreatetruecolor(40, 40);
        imagejpeg($image, $chemin);
        imagedestroy($image);

        // Un commentaire JPEG tient lieu ici de métadonnée identifiante.
        $avec = file_get_contents($chemin);
        $avec = substr_replace($avec, "\xFF\xFE\x00\x14Appareil de Jean", 2, 0);
        file_put_contents($chemin, $avec);
        $this->assertStringContainsString('Appareil de Jean', file_get_contents($chemin));

        MetadataSanitizer::strip($chemin);

        $this->assertStringNotContainsString('Appareil de Jean', file_get_contents($chemin));
        unlink($chemin);
    }

    public function test_la_purge_en_simulation_ne_supprime_rien(): void
    {
        Storage::fake();
        $this->dossierClosAncien();

        $this->artisan('cnlcei:purger-dossiers', ['--mois' => 12])
            ->expectsOutputToContain('Simulation')
            ->assertSuccessful();

        $this->assertSame(1, Signalement::count());
        $this->assertSame(1, Preuve::count());
    }

    public function test_la_purge_confirmee_supprime_dossier_preuves_et_fichiers(): void
    {
        Storage::fake();
        $chemin = $this->dossierClosAncien();

        $this->artisan('cnlcei:purger-dossiers', ['--mois' => 12, '--confirmer' => true])
            ->assertSuccessful();

        $this->assertSame(0, Signalement::count());
        $this->assertSame(0, Preuve::count());
        Storage::assertMissing($chemin);
    }

    public function test_la_purge_epargne_un_dossier_en_cours_d_instruction(): void
    {
        Storage::fake();
        $this->dossierClosAncien();
        Signalement::query()->update(['statut' => 'en_instruction']);

        $this->artisan('cnlcei:purger-dossiers', ['--mois' => 12, '--confirmer' => true])
            ->assertSuccessful();

        $this->assertSame(1, Signalement::count());
    }

    public function test_la_purge_refuse_de_tourner_sans_duree_definie(): void
    {
        config(['signalgabon.retention_dossiers_mois' => 0]);

        $this->artisan('cnlcei:purger-dossiers')->assertFailed();
    }

    /** Crée un dossier clos il y a deux ans, avec une preuve sur le disque. */
    private function dossierClosAncien(): string
    {
        $signalement = Signalement::create([
            'code' => 'SG-TEST-0001',
            'categorie' => 'Marchés publics',
            'description' => str_repeat('Dossier ancien. ', 10),
            'date_faits' => '2023-01-01',
            'province' => 'Estuaire',
            'ville' => 'Libreville',
            'statut' => 'classe',
        ]);

        $chemin = UploadedFile::fake()->create('piece.pdf', 10, 'application/pdf')
            ->store('preuves/SG-TEST-0001');

        Preuve::create([
            'signalement_id' => $signalement->id,
            'nom_fichier' => 'Preuve 1.pdf',
            'chemin' => $chemin,
            'type_fichier' => 'application/pdf',
            'taille' => 10_000,
        ]);

        // updated_at est réécrit en base pour simuler l'ancienneté.
        DB::table('signalements')->where('id', $signalement->id)
            ->update(['updated_at' => now()->subYears(2)]);

        return $chemin;
    }

    public function test_l_application_n_enregistre_aucune_adresse_ip(): void
    {
        $sources = [];
        $dossier = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($dossier as $fichier) {
            if ($fichier->isFile() && $fichier->getExtension() === 'php') {
                $sources[] = file_get_contents($fichier->getPathname());
            }
        }

        $tout = implode("\n", $sources);

        foreach (['->ip()', 'getClientIp', 'REMOTE_ADDR', 'userAgent()'] as $motif) {
            $this->assertStringNotContainsString(
                $motif,
                $tout,
                "La page « Protection » affirme qu'aucune adresse IP n'est enregistrée : {$motif} contredit cette promesse."
            );
        }
    }
}
