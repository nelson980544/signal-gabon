<?php

namespace Tests\Feature;

use App\Models\Preuve;
use App\Models\Signalement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\FabriqueDossiers;
use Tests\Concerns\FabriqueFichiers;
use Tests\TestCase;

/**
 * Parcours 1 : dépôt de preuves (types, tailles, quotas, métadonnées).
 */
class PreuvesUploadTest extends TestCase
{
    use FabriqueDossiers;
    use FabriqueFichiers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function test_les_types_autorises_sont_acceptes(): void
    {
        $fichiers = [
            $this->fichierReel('photo.jpg', $this->octetsJpeg(), 'image/jpeg'),
            $this->fichierReel('image.png', $this->octetsPng(), 'image/png'),
            UploadedFile::fake()->create('rapport.pdf', 20, 'application/pdf'),
            UploadedFile::fake()->create('son.mp3', 20, 'audio/mpeg'),
            UploadedFile::fake()->create('film.mp4', 20, 'video/mp4'),
        ];

        $this->postJson('/api/signalements', $this->corpsSignalement(['fichiers' => $fichiers]))
            ->assertCreated()
            ->assertJsonPath('preuves', 5);

        $this->assertSame(5, Preuve::count());
    }

    public function test_les_extensions_interdites_sont_refusees(): void
    {
        foreach (['shell.php' => 'application/x-php', 'virus.exe' => 'application/x-msdownload',
            'page.html' => 'text/html', 'script.js' => 'application/javascript',
            'archive.zip' => 'application/zip', 'image.svg' => 'image/svg+xml'] as $nom => $mime) {
            $this->postJson('/api/signalements', $this->corpsSignalement([
                'fichiers' => [UploadedFile::fake()->create($nom, 10, $mime)],
            ]))->assertUnprocessable()->assertJsonValidationErrors('fichiers.0');
        }

        $this->assertSame(0, Signalement::count());
        $this->assertSame(0, Preuve::count());
    }

    public function test_un_script_deguise_en_image_est_refuse_selon_son_contenu(): void
    {
        $faux = $this->fichierReel('photo.jpg', "<?php system(\$_GET['c']); ?>", 'image/jpeg');

        $this->postJson('/api/signalements', $this->corpsSignalement(['fichiers' => [$faux]]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fichiers.0');

        $this->assertSame(0, Preuve::count());
    }

    public function test_un_executable_deguise_en_pdf_est_refuse_selon_son_contenu(): void
    {
        $faux = $this->fichierReel('facture.pdf', "MZ\x90\x00\x03\x00\x00\x00" . str_repeat("\0", 200), 'application/pdf');

        $this->postJson('/api/signalements', $this->corpsSignalement(['fichiers' => [$faux]]))
            ->assertUnprocessable();

        $this->assertSame(0, Preuve::count());
    }

    public function test_un_fichier_de_plus_de_10_mo_est_refuse(): void
    {
        $this->postJson('/api/signalements', $this->corpsSignalement([
            'fichiers' => [UploadedFile::fake()->create('gros.pdf', 10241, 'application/pdf')],
        ]))->assertUnprocessable()->assertJsonValidationErrors('fichiers.0');

        $this->assertSame(0, Signalement::count());
    }

    public function test_un_fichier_de_10_mo_pile_est_accepte(): void
    {
        $this->postJson('/api/signalements', $this->corpsSignalement([
            'fichiers' => [UploadedFile::fake()->create('limite.pdf', 10240, 'application/pdf')],
        ]))->assertCreated();
    }

    public function test_plus_de_5_fichiers_a_la_creation_sont_refuses(): void
    {
        $fichiers = array_map(
            fn ($i) => UploadedFile::fake()->create("p{$i}.pdf", 10, 'application/pdf'),
            range(1, 6)
        );

        $this->postJson('/api/signalements', $this->corpsSignalement(['fichiers' => $fichiers]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fichiers');

        $this->assertSame(0, Signalement::count());
    }

    public function test_un_refus_de_fichier_ne_laisse_pas_de_dossier_orphelin(): void
    {
        $this->postJson('/api/signalements', $this->corpsSignalement([
            'fichiers' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')],
        ]))->assertUnprocessable();

        $this->assertSame(0, Signalement::count());
        $this->assertEmpty(Storage::allFiles());
    }

    public function test_l_ajout_ulterieur_refuse_les_types_interdits(): void
    {
        $dossier = $this->dossier();

        $this->postJson("/api/signalements/{$dossier->code}/preuves", [
            'fichiers' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')],
        ])->assertUnprocessable();

        $this->postJson("/api/signalements/{$dossier->code}/preuves", [
            'fichiers' => [UploadedFile::fake()->create('gros.pdf', 10241, 'application/pdf')],
        ])->assertUnprocessable();

        $this->assertSame(0, $dossier->preuves()->count());
    }

    public function test_un_dossier_ne_peut_pas_depasser_10_preuves(): void
    {
        $dossier = $this->dossier();

        for ($lot = 0; $lot < 2; $lot++) {
            $this->postJson("/api/signalements/{$dossier->code}/preuves", [
                'fichiers' => array_map(
                    fn ($i) => UploadedFile::fake()->create("p{$i}.pdf", 5, 'application/pdf'),
                    range(1, 5)
                ),
            ])->assertCreated();
        }

        $this->postJson("/api/signalements/{$dossier->code}/preuves", [
            'fichiers' => [UploadedFile::fake()->create('onzieme.pdf', 5, 'application/pdf')],
        ])->assertUnprocessable();

        $this->assertSame(10, $dossier->preuves()->count());
    }

    public function test_le_gps_d_une_photo_deposee_n_atteint_jamais_le_stockage(): void
    {
        $photo = $this->fichierReel('IMG_0001.jpg', $this->octetsJpegAvecGps(), 'image/jpeg');

        $this->postJson('/api/signalements', $this->corpsSignalement(['fichiers' => [$photo]]))
            ->assertCreated();

        $preuve = Preuve::firstOrFail();
        $stocke = tempnam(sys_get_temp_dir(), 'sg') . '.jpg';
        file_put_contents($stocke, Storage::get($preuve->chemin));

        $exif = @exif_read_data($stocke, 'ANY_TAG', true);
        $this->assertTrue($exif === false || ! isset($exif['GPS']), 'Le GPS a été conservé dans le fichier stocké.');
        $this->assertSame('image/jpeg', getimagesize($stocke)['mime']);
        unlink($stocke);
    }

    public function test_le_chemin_de_stockage_n_est_pas_expose(): void
    {
        $dossier = $this->dossier();
        $this->postJson("/api/signalements/{$dossier->code}/preuves", [
            'fichiers' => [UploadedFile::fake()->create('a.pdf', 5, 'application/pdf')],
        ])->assertCreated();

        $this->assertArrayNotHasKey('chemin', Preuve::firstOrFail()->toArray());
    }
}
