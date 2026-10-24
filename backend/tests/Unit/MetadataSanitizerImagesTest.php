<?php

namespace Tests\Unit;

use App\Services\MetadataSanitizer;
use PHPUnit\Framework\TestCase;
use Tests\Concerns\FabriqueFichiers;

/**
 * Le suppression des métadonnées d'image repose sur GD seul : pas besoin de
 * l'application Laravel, ni d'exiftool.
 */
class MetadataSanitizerImagesTest extends TestCase
{
    use FabriqueFichiers;

    private function ecrire(string $octets, string $extension): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'sg') . '.' . $extension;
        file_put_contents($chemin, $octets);

        return $chemin;
    }

    public function test_le_jeu_d_essai_contient_bien_des_coordonnees_gps(): void
    {
        // Garde-fou : sans cette preuve, les tests de suppression ne
        // prouveraient rien si la fabrique produisait un EXIF invalide.
        $chemin = $this->ecrire($this->octetsJpegAvecGps(), 'jpg');

        $exif = @exif_read_data($chemin, 'ANY_TAG', true);

        $this->assertIsArray($exif);
        $this->assertArrayHasKey('GPS', $exif);
        $this->assertSame('N', $exif['GPS']['GPSLatitudeRef']);
        $this->assertArrayHasKey('GPSLatitude', $exif['GPS']);
        unlink($chemin);
    }

    public function test_les_coordonnees_gps_d_un_jpeg_sont_supprimees(): void
    {
        $chemin = $this->ecrire($this->octetsJpegAvecGps(), 'jpg');

        MetadataSanitizer::strip($chemin);

        $exif = @exif_read_data($chemin, 'ANY_TAG', true);
        $this->assertTrue(
            $exif === false || ! isset($exif['GPS']),
            'Des coordonnées GPS subsistent dans le JPEG nettoyé.'
        );
        $this->assertStringNotContainsString('Exif', file_get_contents($chemin));
        unlink($chemin);
    }

    public function test_l_image_nettoyee_reste_une_image_valide(): void
    {
        $chemin = $this->ecrire($this->octetsJpegAvecGps(), 'jpg');

        MetadataSanitizer::strip($chemin);

        $taille = getimagesize($chemin);
        $this->assertSame([32, 32], [$taille[0], $taille[1]]);
        $this->assertSame('image/jpeg', $taille['mime']);
        unlink($chemin);
    }

    public function test_le_texte_integre_a_un_png_est_supprime(): void
    {
        $chemin = $this->ecrire($this->octetsPngAvecTexte('Author', 'Jean Mboumba'), 'png');
        $this->assertStringContainsString('Jean Mboumba', file_get_contents($chemin));

        MetadataSanitizer::strip($chemin);

        $this->assertStringNotContainsString('Jean Mboumba', file_get_contents($chemin));
        $this->assertSame('image/png', getimagesize($chemin)['mime']);
        unlink($chemin);
    }

    public function test_un_jpeg_deguise_en_png_est_traite_selon_son_contenu(): void
    {
        // L'extension ne compte pas : seul le contenu décide du traitement.
        $chemin = $this->ecrire($this->octetsJpegAvecGps(), 'png');

        MetadataSanitizer::strip($chemin);

        $exif = @exif_read_data($chemin, 'ANY_TAG', true);
        $this->assertTrue($exif === false || ! isset($exif['GPS']));
        unlink($chemin);
    }
}
