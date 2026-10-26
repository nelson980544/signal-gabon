<?php

namespace Tests\Feature;

use App\Models\Preuve;
use App\Models\Signalement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PreuveTelechargementTest extends TestCase
{
    use RefreshDatabase;

    private function signalementAvecPreuve(?User $agent = null): array
    {
        Storage::fake();

        $signalement = Signalement::create([
            'code' => 'SG-TEST-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'categorie' => 'Marchés publics',
            'description' => str_repeat('Faits dénoncés. ', 10),
            'date_faits' => '2026-01-15',
            'province' => 'Estuaire',
            'ville' => 'Libreville',
            'statut' => 'recu',
            'agent_id' => $agent?->id,
        ]);

        $chemin = UploadedFile::fake()->create('facture.pdf', 12, 'application/pdf')
            ->store("preuves/{$signalement->code}");

        $preuve = Preuve::create([
            'signalement_id' => $signalement->id,
            'nom_fichier' => 'facture.pdf',
            'chemin' => $chemin,
            'type_fichier' => 'application/pdf',
            'taille' => 12_000,
        ]);

        return [$signalement, $preuve];
    }

    private function agent(string $role = 'agent'): User
    {
        return User::create([
            'name' => 'Agent ' . uniqid(),
            'email' => uniqid() . '@cnlcei.ga',
            'password' => 'MotDePasse!2026x',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_le_superviseur_consulte_une_preuve_non_attribuee(): void
    {
        [$signalement, $preuve] = $this->signalementAvecPreuve();
        Sanctum::actingAs($this->agent('superviseur'));

        $reponse = $this->get("/api/admin/signalements/{$signalement->id}/preuves/{$preuve->id}");

        $reponse->assertOk();
        $reponse->assertHeader('content-type', 'application/pdf');
        $reponse->assertHeader('x-content-type-options', 'nosniff');
        $this->assertStringContainsString('inline', $reponse->headers->get('content-disposition'));
    }

    public function test_l_agent_attributaire_consulte_la_preuve_de_son_dossier(): void
    {
        $agent = $this->agent();
        [$signalement, $preuve] = $this->signalementAvecPreuve($agent);
        Sanctum::actingAs($agent);

        $this->get("/api/admin/signalements/{$signalement->id}/preuves/{$preuve->id}")
            ->assertOk();
    }

    public function test_le_parametre_telecharger_force_la_piece_jointe(): void
    {
        $agent = $this->agent();
        [$signalement, $preuve] = $this->signalementAvecPreuve($agent);
        Sanctum::actingAs($agent);

        $reponse = $this->get("/api/admin/signalements/{$signalement->id}/preuves/{$preuve->id}?telecharger=1");

        $reponse->assertOk();
        $this->assertStringContainsString('attachment', $reponse->headers->get('content-disposition'));
        $this->assertStringContainsString('facture.pdf', $reponse->headers->get('content-disposition'));
    }

    public function test_un_agent_non_attributaire_est_refuse(): void
    {
        [$signalement, $preuve] = $this->signalementAvecPreuve($this->agent());
        Sanctum::actingAs($this->agent());

        $this->get("/api/admin/signalements/{$signalement->id}/preuves/{$preuve->id}")
            ->assertForbidden();
    }

    public function test_une_requete_sans_jeton_est_refusee(): void
    {
        [$signalement, $preuve] = $this->signalementAvecPreuve();

        $this->getJson("/api/admin/signalements/{$signalement->id}/preuves/{$preuve->id}")
            ->assertUnauthorized();
    }

    public function test_une_preuve_d_un_autre_dossier_est_introuvable(): void
    {
        [$signalement] = $this->signalementAvecPreuve();
        [, $preuveAutreDossier] = $this->signalementAvecPreuve();
        Sanctum::actingAs($this->agent('superviseur'));

        $this->getJson("/api/admin/signalements/{$signalement->id}/preuves/{$preuveAutreDossier->id}")
            ->assertNotFound();
    }

    public function test_un_fichier_absent_du_stockage_renvoie_404(): void
    {
        [$signalement, $preuve] = $this->signalementAvecPreuve();
        Storage::delete($preuve->chemin);
        Sanctum::actingAs($this->agent('superviseur'));

        $this->getJson("/api/admin/signalements/{$signalement->id}/preuves/{$preuve->id}")
            ->assertNotFound();
    }

    /**
     * Garde-fou : sans le trait AuthorizesRequests sur le contrôleur de base,
     * $this->authorize() n'existe pas et le détail d'un dossier répond 500.
     */
    public function test_le_detail_d_un_dossier_s_ouvre_pour_l_agent_attributaire(): void
    {
        $agent = $this->agent();
        [$signalement] = $this->signalementAvecPreuve($agent);
        Sanctum::actingAs($agent);

        $this->getJson("/api/admin/signalements/{$signalement->id}")
            ->assertOk()
            ->assertJsonPath('id', $signalement->id);
    }

    public function test_le_detail_d_un_dossier_est_refuse_a_un_autre_agent(): void
    {
        [$signalement] = $this->signalementAvecPreuve($this->agent());
        Sanctum::actingAs($this->agent());

        $this->getJson("/api/admin/signalements/{$signalement->id}")
            ->assertForbidden();
    }

    public function test_le_chemin_de_stockage_n_est_pas_expose(): void
    {
        [$signalement] = $this->signalementAvecPreuve();
        Sanctum::actingAs($this->agent('superviseur'));

        $reponse = $this->getJson("/api/admin/signalements/{$signalement->id}");

        $reponse->assertOk();
        $this->assertArrayNotHasKey('chemin', $reponse->json('preuves.0'));
    }
}
