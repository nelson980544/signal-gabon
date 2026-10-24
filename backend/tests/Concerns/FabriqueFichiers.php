<?php

namespace Tests\Concerns;

use Illuminate\Http\UploadedFile;

/**
 * Fabrique de vrais fichiers (contenu réel, pas de simulation par extension) :
 * la validation Laravel se fie au contenu, un faux fichier ne prouverait rien.
 */
trait FabriqueFichiers
{
    /** Fichier uploadé réel, avec le contenu et le nom d'origine donnés. */
    protected function fichierReel(string $nom, string $contenu, string $mime = 'application/octet-stream'): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'sg');
        file_put_contents($chemin, $contenu);

        return new UploadedFile($chemin, $nom, $mime, null, true);
    }

    protected function octetsJpeg(): string
    {
        ob_start();
        $img = imagecreatetruecolor(32, 32);
        imagejpeg($img);
        imagedestroy($img);

        return (string) ob_get_clean();
    }

    protected function octetsPng(): string
    {
        ob_start();
        $img = imagecreatetruecolor(32, 32);
        imagepng($img);
        imagedestroy($img);

        return (string) ob_get_clean();
    }

    /**
     * JPEG contenant un segment EXIF avec des coordonnées GPS
     * (0°23'N, Libreville) et un commentaire identifiant.
     */
    protected function octetsJpegAvecGps(): string
    {
        $jpeg = $this->octetsJpeg();

        // TIFF big-endian : IFD0 { GPSInfo -> IFD GPS { LatitudeRef, Latitude } }
        $tiff = 'MM' . pack('n', 42) . pack('N', 8);
        $tiff .= pack('n', 1) . pack('nnNN', 0x8825, 4, 1, 26) . pack('N', 0);          // IFD0, finit à 26
        $tiff .= pack('n', 2);                                                            // IFD GPS : 2 entrées
        $tiff .= pack('nnN', 0x0001, 2, 2) . "N\0\0\0";                                   // LatitudeRef = N
        $tiff .= pack('nnNN', 0x0002, 5, 3, 56) . pack('N', 0);                           // Latitude -> offset 56
        $tiff .= pack('NNNNNN', 0, 1, 23, 1, 0, 1);                                       // 0° 23' 0"

        $app1 = "Exif\0\0" . $tiff;
        $segment = "\xFF\xE1" . pack('n', strlen($app1) + 2) . $app1;

        // Après le SOI, et après le segment JFIF (APP0) que GD écrit en tête.
        $position = 2;
        if (substr($jpeg, 2, 2) === "\xFF\xE0") {
            $position = 4 + unpack('n', substr($jpeg, 4, 2))[1];
        }

        return substr($jpeg, 0, $position) . $segment . substr($jpeg, $position);
    }

    /** PNG auquel on ajoute un bloc texte identifiant (tEXt) avant IEND. */
    protected function octetsPngAvecTexte(string $cle, string $valeur): string
    {
        $png = $this->octetsPng();
        $donnees = $cle . "\0" . $valeur;
        $bloc = pack('N', strlen($donnees)) . 'tEXt' . $donnees . pack('N', crc32('tEXt' . $donnees));
        $fin = strlen($png) - 12; // bloc IEND : 4 + 4 + 0 + 4 octets

        return substr($png, 0, $fin) . $bloc . substr($png, $fin);
    }
}
