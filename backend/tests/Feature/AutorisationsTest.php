<?php

namespace Tests\Feature;

use App\Models\Signalement;
use App\Models\SignalementHistorique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\FabriqueDossiers;
use Tests\TestCase;

/**
 * Parcours 2 : ce qu'un agent n'a pas le droit de faire, de bout en bout.
 */
class AutorisationsTest extends TestCase
{
    use FabriqueDossiers;
    use RefreshDatabase;

    public function test_l_agent_ne_liste_que_ses_dossiers(): void
    {
        $agent = $this->utilisateur();
        $collegue = $this->utilisateur();
        $mien = $this->dossier(['agent_id' => $agent->id]);
        $this->dossier(['agent_id' => $collegue->id]);
        $this->dossier();

        $codes = collect(
            $this->actingAs($agent, 'sanctum')->getJson('/api/admin/signalements')->assertOk()->json('data')
        )->pluck('code');

        $this->assertSame([$mien->code], $codes->all());
    }

    public function test_l_agent_ne_contourne_pas_son_perimetre_avec_le_filtre_agent_id(): void
    {
        $agent = $this->utilisateur();
        $collegue = $this->utilisateur();
        $this->dossier(['agent_id' => $collegue->id]);

        $reponse = $this->actingAs($agent, 'sanctum')
            ->getJson('/api/admin/signalements?agent_id=' . $collegue->id)->assertOk();

        $this->assertCount(0, $reponse->json('data'));
    }

    public function test_le_superviseur_liste_tous_les_dossiers(): void
    {
        $this->dossier();
        $this->dossier(['agent_id' => $this->utilisateur()->id]);

        $this->actingAs($this->utilisateur('superviseur'), 'sanctum')
            ->getJson('/api/admin/signalements')
            ->assertOk()
            ->assertJsonPath('total', 2);
    }

    public function test_l_agent_ne_voit_pas_le_dossier_d_un_collegue(): void
    {
        $agent = $this->utilisateur();
        $autre = $this->dossier(['agent_id' => $this->utilisateur()->id]);

        $this->actingAs($agent, 'sanctum')->getJson("/api/admin/signalements/{$autre->id}")->assertForbidden();
    }

    public function test_l_agent_ne_voit_pas_un_dossier_non_attribue(): void
    {
        $agent = $this->utilisateur();
        $libre = $this->dossier();

        $this->actingAs($agent, 'sanctum')->getJson("/api/admin/signalements/{$libre->id}")->assertForbidden();
    }

    public function test_l_agent_voit_son_dossier(): void
    {
        $agent = $this->utilisateur();
        $mien = $this->dossier(['agent_id' => $agent->id]);

        $this->actingAs($agent, 'sanctum')->getJson("/api/admin/signalements/{$mien->id}")
            ->assertOk()->assertJsonPath('code', $mien->code);
    }

    public function test_l_agent_ne_modifie_pas_le_dossier_d_un_collegue(): void
    {
        $agent = $this->utilisateur();
        $autre = $this->dossier(['agent_id' => $this->utilisateur()->id]);

        $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/admin/signalements/{$autre->id}", ['statut' => 'en_examen'])
            ->assertForbidden();

        $this->assertSame('recu', $autre->fresh()->statut);
        $this->assertSame(0, SignalementHistorique::count());
    }

    public function test_l_agent_ne_reattribue_pas_un_dossier(): void
    {
        $agent = $this->utilisateur();
        $collegue = $this->utilisateur();
        $mien = $this->dossier(['agent_id' => $agent->id]);

        $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/admin/signalements/{$mien->id}", ['agent_id' => $collegue->id, 'message_agent' => 'ok'])
            ->assertOk();

        $this->assertSame($agent->id, $mien->fresh()->agent_id);
    }

    public function test_l_agent_ne_s_approprie_pas_un_dossier_libre(): void
    {
        $agent = $this->utilisateur();
        $libre = $this->dossier();

        $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/admin/signalements/{$libre->id}", ['agent_id' => $agent->id])
            ->assertForbidden();

        $this->assertNull($libre->fresh()->agent_id);
    }

    public function test_le_superviseur_attribue_un_dossier(): void
    {
        $superviseur = $this->utilisateur('superviseur');
        $agent = $this->utilisateur();
        $dossier = $this->dossier();

        $this->actingAs($superviseur, 'sanctum')
            ->patchJson("/api/admin/signalements/{$dossier->id}", ['agent_id' => $agent->id])
            ->assertOk();

        $this->assertSame($agent->id, $dossier->fresh()->agent_id);
    }

    public function test_la_liste_des_agents_est_reservee_au_superviseur(): void
    {
        $this->utilisateur();

        $this->actingAs($this->utilisateur(), 'sanctum')->getJson('/api/admin/agents')->assertForbidden();
        $this->actingAs($this->utilisateur('superviseur'), 'sanctum')->getJson('/api/admin/agents')->assertOk();
    }

    public function test_les_statistiques_par_agent_sont_reservees_au_superviseur(): void
    {
        $this->dossier();

        $agent = $this->actingAs($this->utilisateur(), 'sanctum')->getJson('/api/admin/stats')->assertOk();
        // La charge de travail nominative des collègues n'est pas l'affaire d'un agent.
        $agent->assertJsonMissingPath('par_agent');

        $this->actingAs($this->utilisateur('superviseur'), 'sanctum')->getJson('/api/admin/stats')
            ->assertOk()->assertJsonStructure(['par_agent', 'non_attribues']);
    }

    public function test_l_agent_ne_peut_pas_consulter_la_preuve_d_un_collegue(): void
    {
        $agent = $this->utilisateur();
        $autre = $this->dossier(['agent_id' => $this->utilisateur()->id]);
        $preuve = $autre->preuves()->create([
            'nom_fichier' => 'Preuve 1.pdf', 'chemin' => 'preuves/x.pdf',
            'type_fichier' => 'application/pdf', 'taille' => 10,
        ]);

        $this->actingAs($agent, 'sanctum')
            ->get("/api/admin/signalements/{$autre->id}/preuves/{$preuve->id}")
            ->assertForbidden();
    }
}
