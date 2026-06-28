<?php

namespace App\Services;

/**
 * Supprime les métadonnées EXIF des images avant stockage.
 * Utilise l'extension GD (standard PHP 8+) sans dépendance externe.
 */
class ImageSanitizer
{
    public static function strip(string $tmpPath): void
    {
        $mime = mime_content_type($tmpPath);

        if (in_array($mime, ['image/jpeg', 'image/jpg'])) {
            $img = @imagecreatefromjpeg($tmpPath);
            if ($img) {
                imagejpeg($img, $tmpPath, 85);
                imagedestroy($img);
            }
        } elseif ($mime === 'image/png') {
            $img = @imagecreatefrompng($tmpPath);
            if ($img) {
                imagealphablending($img, false);
                imagesavealpha($img, true);
                imagepng($img, $tmpPath, 6);
                imagedestroy($img);
            }
        }
        // PDF, MP3, WAV, MP4 : aucun traitement nécessaire
    }
}
