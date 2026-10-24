<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\Concerns\FabriqueDossiers;
use Tests\TestCase;

/**
 * Parcours 2 : connexion, expiration et renouvellement de la session.
 */
class AuthentificationTest extends TestCase
{
    use FabriqueDossiers;
    use RefreshDatabase;

    public function test_de_bons_identifiants_donnent_un_token_et_le_profil(): void
    {
        $agent = $this->utilisateur('agent');

        $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => self::MOT_DE_PASSE])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']])
            ->assertJsonPath('user.role', 'agent')
            ->assertJsonMissingPath('user.password');
    }

    public function test_un_mauvais_mot_de_passe_est_refuse(): void
    {
        $agent = $this->utilisateur();

        $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => 'mauvais'])
            ->assertUnauthorized()
            ->assertJsonMissingPath('token');
    }

    public function test_un_email_inconnu_recoit_la_meme_reponse_qu_un_mauvais_mot_de_passe(): void
    {
        $agent = $this->utilisateur();

        $inconnu = $this->postJson('/api/auth/login', ['email' => 'inconnu@cnlcei.ga', 'password' => 'x']);
        $mauvais = $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => 'x']);

        $this->assertSame($inconnu->status(), $mauvais->status());
        $this->assertSame($inconnu->json(), $mauvais->json());
    }

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $agent = $this->utilisateur('agent', ['is_active' => false]);

        $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => self::MOT_DE_PASSE])
            ->assertForbidden()
            ->assertJsonMissingPath('token');

        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_les_champs_de_connexion_sont_obligatoires(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_la_sixieme_tentative_en_une_minute_est_bloquee(): void
    {
        $agent = $this->utilisateur();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => 'faux'])
                ->assertUnauthorized();
        }

        // Même avec le bon mot de passe : le blocage protège contre la force brute.
        $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => self::MOT_DE_PASSE])
            ->assertStatus(429);
    }

    public function test_le_blocage_se_leve_apres_une_minute(): void
    {
        $agent = $this->utilisateur();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => 'faux']);
        }
        $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => 'faux'])->assertStatus(429);

        $this->travel(61)->seconds();

        $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => self::MOT_DE_PASSE])
            ->assertOk();
    }

    public function test_les_requetes_publiques_ne_consomment_pas_le_quota_de_connexion(): void
    {
        // Un bureau partage souvent une seule adresse IP : un citoyen qui
        // suit son dossier ne doit pas empêcher un agent de se connecter.
        $agent = $this->utilisateur();
        $dossier = $this->dossier();

        for ($i = 0; $i < 6; $i++) {
            $this->getJson("/api/signalements/{$dossier->code}/statut")->assertOk();
        }

        $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => self::MOT_DE_PASSE])
            ->assertOk();
    }

    public function test_les_tentatives_de_connexion_ne_consomment_pas_le_quota_de_depot(): void
    {
        $agent = $this->utilisateur();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => 'faux']);
        }

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/signalements', $this->corpsSignalement())->assertCreated();
        }
    }

    public function test_le_token_est_valable_6_heures(): void
    {
        $this->connecter($this->utilisateur());

        $jeton = PersonalAccessToken::firstOrFail();
        $this->assertEqualsWithDelta(6 * 3600, now()->diffInSeconds($jeton->expires_at, false), 5);
    }

    public function test_le_token_fonctionne_avant_6_heures_et_expire_apres(): void
    {
        $token = $this->connecter($this->utilisateur());

        $this->travel(5)->hours();
        $this->avecToken($token)->getJson('/api/auth/me')->assertOk();

        $this->travel(61)->minutes();
        $this->avecToken($token)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_un_token_expire_ne_peut_pas_etre_renouvele(): void
    {
        $token = $this->connecter($this->utilisateur());

        $this->travel(7)->hours();

        $this->avecToken($token)->postJson('/api/auth/refresh')->assertUnauthorized();
    }

    public function test_le_renouvellement_revoque_l_ancien_token_et_en_emet_un_nouveau(): void
    {
        $agent = $this->utilisateur();
        $ancien = $this->connecter($agent);

        $this->travel(5)->hours();
        $reponse = $this->avecToken($ancien)->postJson('/api/auth/refresh')
            ->assertOk()
            ->assertJsonStructure(['token', 'expires_at']);
        $nouveau = $reponse->json('token');

        $this->assertNotSame($ancien, $nouveau);
        $this->assertSame(1, PersonalAccessToken::count());

        // Le nouveau token vit 6 h à partir du renouvellement, pas de la connexion.
        $this->travel(2)->hours();
        $this->avecToken($nouveau)->getJson('/api/auth/me')->assertOk();
        $this->avecToken($ancien)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_expires_at_annonce_par_le_renouvellement_correspond_au_token(): void
    {
        $token = $this->connecter($this->utilisateur());

        $reponse = $this->avecToken($token)->postJson('/api/auth/refresh')->assertOk();

        $jeton = PersonalAccessToken::firstOrFail();
        $this->assertEqualsWithDelta(
            $jeton->expires_at->timestamp,
            \Carbon\Carbon::parse($reponse->json('expires_at'))->timestamp,
            5
        );
    }

    public function test_la_deconnexion_revoque_le_token(): void
    {
        $token = $this->connecter($this->utilisateur());

        $this->avecToken($token)->postJson('/api/auth/logout')->assertOk();

        $this->assertSame(0, PersonalAccessToken::count());
        $this->avecToken($token)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_un_agent_desactive_apres_sa_connexion_perd_l_acces(): void
    {
        $agent = $this->utilisateur();
        $token = $this->connecter($agent);

        $agent->update(['is_active' => false]);

        $this->avecToken($token)->getJson('/api/admin/signalements')->assertForbidden();
        $this->avecToken($token)->getJson('/api/auth/me')->assertForbidden();
    }

    public function test_la_reponse_de_connexion_n_expose_ni_mot_de_passe_ni_remember_token(): void
    {
        $agent = $this->utilisateur();

        $corps = $this->postJson('/api/auth/login', ['email' => $agent->email, 'password' => self::MOT_DE_PASSE])
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('password', $corps);
        $this->assertStringNotContainsString('remember_token', $corps);
        $this->assertStringNotContainsString(User::first()->password, $corps);
    }
}
