<?php

namespace Tests\Feature;

use App\Models\Signalement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\FabriqueDossiers;
use Tests\TestCase;

/**
 * Parcours 1 : dépôt et suivi d'un signalement par le citoyen.
 */
class SignalementPublicTest extends TestCase
{
    use FabriqueDossiers;
    use RefreshDatabase;

    public function test_un_signalement_valide_retourne_un_code_de_suivi_et_le_statut_recu(): void
    {
        $reponse = $this->postJson('/api/signalements', $this->corpsSignalement())
            ->assertCreated();

        $code = $reponse->json('code');
        $this->assertMatchesRegularExpression('/^SG-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $code);

        $dossier = Signalement::where('code', $code)->firstOrFail();
        $this->assertSame('recu', $dossier->statut);
        $this->assertNull($dossier->agent_id);
    }

    public function test_deux_signalements_n_ont_jamais_le_meme_code(): void
    {
        $a = $this->postJson('/api/signalements', $this->corpsSignalement())->json('code');
        $b = $this->postJson('/api/signalements', $this->corpsSignalement())->json('code');

        $this->assertNotSame($a, $b);
    }

    public function test_le_statut_fourni_par_le_citoyen_est_ignore(): void
    {
        // Un citoyen ne doit pas pouvoir déposer un dossier déjà « traité » ni
        // se l'attribuer à un agent.
        $code = $this->postJson('/api/signalements', $this->corpsSignalement([
            'statut' => 'traite',
            'agent_id' => $this->utilisateur()->id,
            'message_agent' => 'Dossier clos.',
        ]))->assertCreated()->json('code');

        $dossier = Signalement::where('code', $code)->firstOrFail();
        $this->assertSame('recu', $dossier->statut);
        $this->assertNull($dossier->agent_id);
        $this->assertNull($dossier->message_agent);
    }

    public function test_une_categorie_hors_enum_est_rejetee(): void
    {
        $this->postJson('/api/signalements', $this->corpsSignalement(['categorie' => 'Football']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('categorie');

        $this->assertSame(0, Signalement::count());
    }

    public function test_une_province_hors_enum_est_rejetee(): void
    {
        $this->postJson('/api/signalements', $this->corpsSignalement(['province' => 'Paris']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('province');

        $this->assertSame(0, Signalement::count());
    }

    public function test_toutes_les_categories_et_provinces_autorisees_sont_acceptees(): void
    {
        $categories = ['Administration publique', 'Police et gendarmerie', 'Santé et hôpitaux',
            'Éducation et universités', 'Douanes et frontières', 'Justice et tribunaux',
            'Marchés publics', 'Autre'];
        $provinces = ['Estuaire', 'Haut-Ogooué', 'Moyen-Ogooué', 'Ngounié', 'Nyanga',
            'Ogooué-Ivindo', 'Ogooué-Lolo', 'Ogooué-Maritime', 'Woleu-Ntem'];

        // 9 requêtes au plus pour rester sous la limite de 10 par minute.
        foreach ($provinces as $i => $province) {
            $this->postJson('/api/signalements', $this->corpsSignalement([
                'categorie' => $categories[$i % count($categories)],
                'province' => $province,
            ]))->assertCreated();
        }

        foreach ($categories as $categorie) {
            Signalement::create([
                'code' => 'SG-CAT-' . crc32($categorie),
                'categorie' => $categorie,
                'description' => str_repeat('x', 60),
                'date_faits' => '2026-01-01',
                'province' => 'Estuaire',
                'ville' => 'Libreville',
            ]);
        }

        $this->assertSame(count($provinces) + count($categories), Signalement::count());
    }

    public function test_les_champs_obligatoires_sont_exiges(): void
    {
        $this->postJson('/api/signalements', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['categorie', 'description', 'date_faits', 'province', 'ville']);
    }

    public function test_une_description_trop_courte_est_rejetee(): void
    {
        $this->postJson('/api/signalements', $this->corpsSignalement(['description' => 'Trop court']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('description');
    }

    public function test_une_date_des_faits_dans_le_futur_est_rejetee(): void
    {
        $this->postJson('/api/signalements', $this->corpsSignalement([
            'date_faits' => now()->addYear()->toDateString(),
        ]))->assertUnprocessable()->assertJsonValidationErrors('date_faits');
    }

    public function test_un_email_de_contact_invalide_est_rejete(): void
    {
        $this->postJson('/api/signalements', $this->corpsSignalement(['email_contact' => 'pas-un-email']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email_contact');
    }

    public function test_le_depot_est_limite_a_dix_par_minute(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/signalements', $this->corpsSignalement())->assertCreated();
        }

        $this->postJson('/api/signalements', $this->corpsSignalement())->assertStatus(429);
    }

    public function test_le_suivi_par_code_retourne_le_statut(): void
    {
        $dossier = $this->dossier(['statut' => 'en_examen', 'message_agent' => 'Nous examinons votre dossier.']);

        $this->getJson("/api/signalements/{$dossier->code}/statut")
            ->assertOk()
            ->assertJsonPath('code', $dossier->code)
            ->assertJsonPath('statut', 'en_examen')
            ->assertJsonPath('message_agent', 'Nous examinons votre dossier.');
    }

    public function test_un_code_inexistant_renvoie_404_sans_rien_reveler(): void
    {
        $autre = $this->dossier(['description' => 'Description confidentielle de l\'autre dossier ' . str_repeat('.', 30)]);

        $reponse = $this->getJson('/api/signalements/SG-ZZZZ-ZZZZ/statut')->assertNotFound();

        $corps = $reponse->getContent();
        $this->assertStringNotContainsString($autre->code, $corps);
        $this->assertStringNotContainsString('confidentielle', $corps);
        // Aucun détail d'implémentation (classe du modèle, requête SQL).
        $this->assertStringNotContainsString('Signalement', $corps);
        $this->assertStringNotContainsString('select', strtolower($corps));
    }

    public function test_un_code_voisin_ne_donne_pas_acces_a_un_autre_dossier(): void
    {
        $dossier = $this->dossier(['code' => 'SG-AAAA-0001']);

        // Ni préfixe, ni joker SQL, ni casse différente ne doivent matcher.
        foreach (['SG-AAAA', 'SG-AAAA-000', 'SG-%', 'SG-AAAA-%', '%', '_G-AAAA-0001'] as $essai) {
            $this->getJson('/api/signalements/' . rawurlencode($essai) . '/statut')->assertNotFound();
        }

        $this->getJson("/api/signalements/{$dossier->code}/statut")->assertOk();
    }

    public function test_le_depot_de_preuves_sur_un_code_inexistant_renvoie_404(): void
    {
        Storage::fake();

        $this->postJson('/api/signalements/SG-ZZZZ-ZZZZ/preuves', [
            'fichiers' => [\Illuminate\Http\UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
        ])->assertNotFound();
    }
}
