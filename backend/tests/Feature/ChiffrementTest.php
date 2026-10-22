<?php

namespace Tests\Feature;

use App\Models\Preuve;
use App\Models\Signalement;
use App\Models\User;
use App\Services\FileCrypto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class ChiffrementTest extends TestCase
{
    use RefreshDatabase;

    private const DESCRIPTION = 'Un agent des douanes exige systématiquement une somme en espèces pour libérer des marchandises au port.';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['filesystems.default' => 'local']);
    }

    private function soumettre(string $contenu = "%PDF-1.4 SECRET-CONTENU\n"): string
    {
        $r = $this->postJson('/api/signalements', [
            'categorie'     => 'Douanes et frontières',
            'description'   => self::DESCRIPTION,
            'date_faits'    => '2026-09-01',
            'province'      => 'Estuaire',
            'ville'         => 'Libreville',
            'email_contact' => 'temoin@example.com',
            'fichiers'      => [UploadedFile::fake()->createWithContent('preuve-secrete.pdf', $contenu)],
        ])->assertCreated();

        return $r->json('code');
    }

    private function utilisateur(string $role): User
    {
        return User::create([
            'name' => $role, 'email' => "$role@test.ga", 'password' => 'x', 'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_champs_sensibles_chiffres_en_base_mais_lisibles_par_le_modele(): void
    {
        $code = $this->soumettre();

        $brut = DB::table('signalements')->where('code', $code)->first();
        $this->assertStringNotContainsString('douanes', $brut->description);
        $this->assertStringNotContainsString('temoin@example.com', $brut->email_contact);

        $s = Signalement::where('code', $code)->first();
        $this->assertSame(self::DESCRIPTION, $s->description);
        $this->assertSame('temoin@example.com', $s->email_contact);

        $this->assertStringNotContainsString('preuve-secrete', DB::table('preuves')->value('nom_fichier'));
    }

    public function test_fichier_chiffre_sur_disque_et_restitue_a_un_superviseur(): void
    {
        $contenu = "%PDF-1.4 SECRET-CONTENU\n";
        $this->soumettre($contenu);

        $preuve = Preuve::first();
        $this->assertTrue($preuve->chiffre);
        $this->assertStringEndsWith('.enc', $preuve->chemin);
        $stocke = Storage::disk('local')->get($preuve->chemin);
        $this->assertStringNotContainsString('SECRET-CONTENU', $stocke);

        Sanctum::actingAs($this->utilisateur('superviseur'));
        $r = $this->get("/api/admin/preuves/{$preuve->id}/telecharger")->assertOk();
        $this->assertSame($contenu, $r->streamedContent());
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_agent_non_assigne_ne_peut_pas_telecharger(): void
    {
        $this->soumettre();
        $preuve = Preuve::first();

        Sanctum::actingAs($this->utilisateur('agent'));
        $this->get("/api/admin/preuves/{$preuve->id}/telecharger")->assertForbidden();
    }

    public function test_agent_assigne_peut_telecharger(): void
    {
        $code = $this->soumettre();
        $agent = $this->utilisateur('agent');
        Signalement::where('code', $code)->update(['agent_id' => $agent->id]);

        Sanctum::actingAs($agent);
        $this->get('/api/admin/preuves/' . Preuve::first()->id . '/telecharger')->assertOk();
    }

    public function test_chemin_interne_jamais_expose(): void
    {
        $code = $this->soumettre();
        Sanctum::actingAs($this->utilisateur('superviseur'));

        $id = Signalement::where('code', $code)->value('id');
        $r = $this->getJson("/api/admin/signalements/$id")->assertOk();
        $this->assertArrayNotHasKey('chemin', $r->json('preuves.0'));
        $this->assertSame('preuve-secrete.pdf', $r->json('preuves.0.nom_fichier'));
        $this->assertSame(self::DESCRIPTION, $r->json('description'));
    }

    public function test_gros_fichier_multi_blocs_et_fichier_vide(): void
    {
        $gros = random_bytes(200_000);
        $src = tempnam(sys_get_temp_dir(), 't');
        file_put_contents($src, $gros);
        FileCrypto::encryptToDisk($src, 'preuves/x/gros.enc');
        $this->assertSame($gros, implode('', iterator_to_array(FileCrypto::decryptStream('preuves/x/gros.enc'), false)));

        file_put_contents($src, '');
        FileCrypto::encryptToDisk($src, 'preuves/x/vide.enc');
        $this->assertSame('', implode('', iterator_to_array(FileCrypto::decryptStream('preuves/x/vide.enc'), false)));
        unlink($src);
    }

    public function test_fichier_altere_ou_tronque_est_refuse(): void
    {
        $src = tempnam(sys_get_temp_dir(), 't');
        file_put_contents($src, random_bytes(150_000));
        FileCrypto::encryptToDisk($src, 'preuves/x/a.enc');
        unlink($src);
        $octets = Storage::disk('local')->get('preuves/x/a.enc');

        $altere = $octets;
        $altere[100] = $altere[100] ^ "\x01";
        Storage::disk('local')->put('preuves/x/altere.enc', $altere);
        $this->expectException(RuntimeException::class);
        iterator_to_array(FileCrypto::decryptStream('preuves/x/altere.enc'));
    }

    public function test_troncature_aux_limites_de_bloc_detectee(): void
    {
        $src = tempnam(sys_get_temp_dir(), 't');
        file_put_contents($src, random_bytes(150_000));
        FileCrypto::encryptToDisk($src, 'preuves/x/a.enc');
        unlink($src);
        $octets = Storage::disk('local')->get('preuves/x/a.enc');

        // Coupe après le premier bloc complet : 4 (magic) + 24 (en-tête) + 4 + 65536 + 17
        Storage::disk('local')->put('preuves/x/coupe.enc', substr($octets, 0, 4 + 24 + 4 + 65536 + 17));
        $this->expectException(RuntimeException::class);
        iterator_to_array(FileCrypto::decryptStream('preuves/x/coupe.enc'));
    }

    public function test_fichier_deplace_vers_un_autre_chemin_est_refuse(): void
    {
        $src = tempnam(sys_get_temp_dir(), 't');
        file_put_contents($src, 'contenu');
        FileCrypto::encryptToDisk($src, 'preuves/A/a.enc');
        unlink($src);
        Storage::disk('local')->copy('preuves/A/a.enc', 'preuves/B/a.enc');

        $this->expectException(RuntimeException::class);
        iterator_to_array(FileCrypto::decryptStream('preuves/B/a.enc'));
    }

    public function test_commande_chiffre_les_anciens_fichiers_en_clair(): void
    {
        $s = Signalement::create([
            'code' => 'SG-TEST-0001', 'categorie' => 'Autre', 'description' => self::DESCRIPTION,
            'date_faits' => '2026-09-01', 'province' => 'Estuaire', 'ville' => 'Libreville',
        ]);
        Storage::disk('local')->put('preuves/SG-TEST-0001/vieux.pdf', 'ANCIEN-CLAIR');
        $p = Preuve::create([
            'signalement_id' => $s->id, 'nom_fichier' => 'vieux.pdf',
            'chemin' => 'preuves/SG-TEST-0001/vieux.pdf', 'type_fichier' => 'application/pdf', 'taille' => 12,
        ]);
        $this->assertFalse($p->fresh()->chiffre);

        $this->artisan('preuves:chiffrer')->assertSuccessful();
        // Idempotent
        $this->artisan('preuves:chiffrer')->assertSuccessful();

        $this->assertTrue($p->fresh()->chiffre);
        $this->assertStringNotContainsString('ANCIEN-CLAIR', Storage::disk('local')->get($p->chemin));

        Sanctum::actingAs($this->utilisateur('superviseur'));
        $this->get("/api/admin/preuves/{$p->id}/telecharger")->assertOk();
    }

    public function test_cle_de_fichiers_dediee_invalide_est_rejetee(): void
    {
        config(['security.files_key' => base64_encode('trop-courte')]);
        $src = tempnam(sys_get_temp_dir(), 't');
        file_put_contents($src, 'x');
        $this->expectException(RuntimeException::class);
        FileCrypto::encryptToDisk($src, 'preuves/x/k.enc');
    }
}
