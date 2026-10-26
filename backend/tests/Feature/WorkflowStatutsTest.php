<?php

namespace Tests\Feature;

use App\Models\SignalementHistorique;
use App\Notifications\StatutSignalementChange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FabriqueDossiers;
use Tests\TestCase;

/**
 * Parcours 3 : cycle de vie d'un dossier, traçabilité et notification.
 */
class WorkflowStatutsTest extends TestCase
{
    use FabriqueDossiers;
    use RefreshDatabase;

    private function patch_(int $id, array $corps, ?string $role = 'superviseur', $utilisateur = null)
    {
        return $this->actingAs($utilisateur ?? $this->utilisateur($role), 'sanctum')
            ->patchJson("/api/admin/signalements/{$id}", $corps);
    }

    // ---- Historique -------------------------------------------------

    public function test_un_changement_de_statut_cree_une_ligne_d_historique(): void
    {
        $superviseur = $this->utilisateur('superviseur');
        $dossier = $this->dossier(['statut' => 'recu']);

        $this->travelTo(now()->startOfSecond());
        $this->patch_($dossier->id, ['statut' => 'en_examen', 'message_agent' => 'Pris en charge.'], utilisateur: $superviseur)
            ->assertOk();

        $this->assertSame(1, SignalementHistorique::count());
        $ligne = SignalementHistorique::firstOrFail();
        $this->assertSame($dossier->id, $ligne->signalement_id);
        $this->assertSame($superviseur->id, $ligne->agent_id, 'Qui : l\'auteur du changement.');
        $this->assertSame('recu', $ligne->ancien_statut);
        $this->assertSame('en_examen', $ligne->nouveau_statut);
        $this->assertSame('Pris en charge.', $ligne->commentaire);
        $this->assertNotNull($ligne->created_at, 'Quand : la date du changement.');
        $this->assertEqualsWithDelta(now()->timestamp, $ligne->created_at->timestamp, 5);
    }

    public function test_une_suite_de_changements_est_tracee_dans_l_ordre(): void
    {
        $superviseur = $this->utilisateur('superviseur');
        $dossier = $this->dossier(['statut' => 'recu']);

        foreach (['en_examen', 'attribue', 'en_instruction', 'traite'] as $statut) {
            $this->patch_($dossier->id, ['statut' => $statut], utilisateur: $superviseur)->assertOk();
        }

        $this->assertSame(
            [['recu', 'en_examen'], ['en_examen', 'attribue'], ['attribue', 'en_instruction'], ['en_instruction', 'traite']],
            SignalementHistorique::orderBy('id')->get()->map(fn ($h) => [$h->ancien_statut, $h->nouveau_statut])->all()
        );
    }

    public function test_l_historique_est_visible_dans_le_detail_du_dossier(): void
    {
        $superviseur = $this->utilisateur('superviseur');
        $dossier = $this->dossier();
        $this->patch_($dossier->id, ['statut' => 'en_examen'], utilisateur: $superviseur)->assertOk();

        $this->actingAs($superviseur, 'sanctum')->getJson("/api/admin/signalements/{$dossier->id}")
            ->assertOk()
            ->assertJsonPath('historiques.0.ancien_statut', 'recu')
            ->assertJsonPath('historiques.0.nouveau_statut', 'en_examen')
            ->assertJsonPath('historiques.0.agent.id', $superviseur->id);
    }

    public function test_renvoyer_le_meme_statut_ne_cree_pas_d_historique(): void
    {
        // L'interface renvoie le statut courant à chaque sauvegarde.
        $dossier = $this->dossier(['statut' => 'en_examen']);

        $this->patch_($dossier->id, ['statut' => 'en_examen', 'message_agent' => 'Rappel.'])->assertOk();

        $this->assertSame(0, SignalementHistorique::count());
        $this->assertSame('Rappel.', $dossier->fresh()->message_agent);
    }

    public function test_une_attribution_est_tracee_sans_statut(): void
    {
        $agent = $this->utilisateur();
        $dossier = $this->dossier();

        $this->patch_($dossier->id, ['agent_id' => $agent->id])->assertOk();

        $ligne = SignalementHistorique::firstOrFail();
        $this->assertNull($ligne->ancien_statut);
        $this->assertNull($ligne->nouveau_statut);
        $this->assertNull($ligne->ancien_agent_id);
        $this->assertSame($agent->id, $ligne->nouveau_agent_id);
    }

    public function test_renvoyer_le_meme_agent_en_formulaire_ne_cree_pas_d_historique(): void
    {
        $agent = $this->utilisateur();
        $dossier = $this->dossier(['agent_id' => $agent->id]);

        // Un formulaire envoie « 3 » (texte), la base stocke 3 (entier).
        $this->actingAs($this->utilisateur('superviseur'), 'sanctum')
            ->patch("/api/admin/signalements/{$dossier->id}", ['agent_id' => (string) $agent->id], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertSame(0, SignalementHistorique::count());
    }

    public function test_un_refus_d_autorisation_ne_laisse_aucune_trace(): void
    {
        $dossier = $this->dossier(['agent_id' => $this->utilisateur()->id]);

        $this->patch_($dossier->id, ['statut' => 'en_examen'], 'agent')->assertForbidden();

        $this->assertSame(0, SignalementHistorique::count());
        $this->assertSame('recu', $dossier->fresh()->statut);
    }

    // ---- Transitions ------------------------------------------------

    /** @return array<string, array{0: string, 1: string}> */
    public static function transitionsValides(): array
    {
        return [
            'recu → en_examen' => ['recu', 'en_examen'],
            'recu → classe (sans suite)' => ['recu', 'classe'],
            'recu → attribue (attribution dès l\'arrivée)' => ['recu', 'attribue'],
            'en_examen → attribue' => ['en_examen', 'attribue'],
            'en_examen → en_instruction' => ['en_examen', 'en_instruction'],
            'en_examen → classe' => ['en_examen', 'classe'],
            'attribue → en_instruction' => ['attribue', 'en_instruction'],
            'en_instruction → traite' => ['en_instruction', 'traite'],
            'en_instruction → classe' => ['en_instruction', 'classe'],
        ];
    }

    #[DataProvider('transitionsValides')]
    public function test_les_transitions_valides_sont_acceptees(string $de, string $vers): void
    {
        $dossier = $this->dossier(['statut' => $de]);

        $this->patch_($dossier->id, ['statut' => $vers])->assertOk()->assertJsonPath('statut', $vers);

        $this->assertSame($vers, $dossier->fresh()->statut);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function transitionsInvalides(): array
    {
        return [
            'recu → traite (instruction sautée)' => ['recu', 'traite'],
            'recu → en_instruction' => ['recu', 'en_instruction'],
            'en_examen → traite' => ['en_examen', 'traite'],
            'attribue → recu (retour arrière)' => ['attribue', 'recu'],
            'en_instruction → recu' => ['en_instruction', 'recu'],
            'en_instruction → en_examen' => ['en_instruction', 'en_examen'],
            'traite → recu' => ['traite', 'recu'],
            'traite → classe' => ['traite', 'classe'],
            'classe → recu' => ['classe', 'recu'],
            'classe → traite' => ['classe', 'traite'],
        ];
    }

    #[DataProvider('transitionsInvalides')]
    public function test_les_transitions_invalides_sont_refusees_sans_effet(string $de, string $vers): void
    {
        Notification::fake();
        $dossier = $this->dossier(['statut' => $de, 'email_contact' => 'temoin@exemple.ga']);

        $this->patch_($dossier->id, ['statut' => $vers])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('statut');

        $this->assertSame($de, $dossier->fresh()->statut);
        $this->assertSame(0, SignalementHistorique::count());
        Notification::assertNothingSent();
    }

    public function test_un_statut_hors_liste_est_refuse(): void
    {
        $dossier = $this->dossier();

        $this->patch_($dossier->id, ['statut' => 'supprime'])->assertUnprocessable()->assertJsonValidationErrors('statut');
    }

    public function test_une_transition_refusee_n_applique_pas_le_reste_de_la_requete(): void
    {
        $dossier = $this->dossier(['statut' => 'recu']);

        $this->patch_($dossier->id, ['statut' => 'traite', 'message_agent' => 'Dossier clos.'])->assertUnprocessable();

        $this->assertNull($dossier->fresh()->message_agent, 'Le message du citoyen ne doit pas annoncer un dossier clos.');
    }

    public function test_le_superviseur_peut_rouvrir_un_dossier_clos(): void
    {
        $dossier = $this->dossier(['statut' => 'classe']);

        $this->patch_($dossier->id, ['statut' => 'en_instruction'])->assertOk();

        $this->assertSame('en_instruction', $dossier->fresh()->statut);
    }

    public function test_l_agent_ne_peut_pas_rouvrir_un_dossier_clos(): void
    {
        $agent = $this->utilisateur();
        $dossier = $this->dossier(['statut' => 'traite', 'agent_id' => $agent->id]);

        $this->patch_($dossier->id, ['statut' => 'en_instruction'], utilisateur: $agent)->assertUnprocessable();

        $this->assertSame('traite', $dossier->fresh()->statut);
    }

    // ---- Notification -----------------------------------------------

    public function test_le_citoyen_est_notifie_quand_un_email_de_contact_existe(): void
    {
        Notification::fake();
        $dossier = $this->dossier(['email_contact' => 'temoin@exemple.ga']);

        $this->patch_($dossier->id, ['statut' => 'en_examen'])->assertOk();

        Notification::assertSentOnDemandTimes(StatutSignalementChange::class, 1);
        Notification::assertSentOnDemand(
            StatutSignalementChange::class,
            fn ($notification, array $canaux, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'temoin@exemple.ga'
                && $canaux === ['mail']
        );
    }

    public function test_aucune_notification_sans_email_de_contact(): void
    {
        Notification::fake();
        $dossier = $this->dossier(['email_contact' => null]);

        $this->patch_($dossier->id, ['statut' => 'en_examen'])->assertOk();

        Notification::assertNothingSent();
        $this->assertSame(1, SignalementHistorique::count());
    }

    public function test_aucune_notification_quand_le_statut_ne_change_pas(): void
    {
        Notification::fake();
        $agent = $this->utilisateur();
        $dossier = $this->dossier(['email_contact' => 'temoin@exemple.ga', 'statut' => 'en_examen']);

        $this->patch_($dossier->id, ['statut' => 'en_examen', 'agent_id' => $agent->id, 'message_agent' => 'Salut'])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_le_courriel_mentionne_le_code_et_les_statuts_sans_donnee_interne(): void
    {
        $agent = $this->utilisateur('agent', ['name' => 'Jean Mboumba', 'email' => 'jean@cnlcei.ga']);
        $dossier = $this->dossier(['email_contact' => 'temoin@exemple.ga', 'agent_id' => $agent->id, 'message_agent' => 'Merci.']);
        $dossier->statut = 'en_examen';

        $mail = (new StatutSignalementChange($dossier, 'recu'))->toMail(new AnonymousNotifiable());
        $texte = $mail->subject . "\n" . implode("\n", array_merge($mail->introLines, $mail->outroLines));

        $this->assertStringContainsString($dossier->code, $texte);
        $this->assertStringContainsString('Reçu', $texte);
        $this->assertStringContainsString('En examen', $texte);
        $this->assertStringContainsString('Merci.', $texte);
        $this->assertStringNotContainsString('Jean Mboumba', $texte);
        $this->assertStringNotContainsString('jean@cnlcei.ga', $texte);
    }

    public function test_une_panne_du_courriel_ne_fait_pas_echouer_le_changement_de_statut(): void
    {
        // Le statut est déjà enregistré : une erreur 500 laisserait l'agent
        // croire que rien n'a été fait, et il recommencerait.
        $dossier = $this->dossier(['email_contact' => 'temoin@exemple.ga']);
        Mail::extend('en_panne', fn () => new class extends AbstractTransport {
            protected function doSend(SentMessage $message): void
            {
                throw new \RuntimeException('SMTP indisponible');
            }

            public function __toString(): string
            {
                return 'en_panne';
            }
        });
        config(['mail.default' => 'en_panne', 'mail.mailers.en_panne' => ['transport' => 'en_panne']]);

        $this->patch_($dossier->id, ['statut' => 'en_examen'])->assertOk();

        $this->assertSame('en_examen', $dossier->fresh()->statut);
        $this->assertSame(1, SignalementHistorique::count());
    }
}
