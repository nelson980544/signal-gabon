<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Supprime les métadonnées des preuves avant stockage (anonymat des lanceurs d'alerte).
 * - JPEG/PNG : ré-encodage GD (supprime EXIF/GPS).
 * - PDF/MP3/WAV/MP4 : exiftool (PDF, MP3, WAV) ou ffmpeg (MP4) si disponibles.
 *   Si l'outil est absent : avertissement dans les logs, ou rejet si
 *   PREUVES_STRICT_SANITIZE=true.
 */
class ImageSanitizer
{
    private const MAX_PIXELS = 50_000_000;

    /** Retourne false si le fichier doit être refusé. */
    public static function strip(string $tmpPath): bool
    {
        $mime = mime_content_type($tmpPath);

        if (in_array($mime, ['image/jpeg', 'image/jpg'])) {
            return self::reencodeImage($tmpPath, 'imagecreatefromjpeg', fn ($i, $p) => imagejpeg($i, $p, 85));
        }
        if ($mime === 'image/png') {
            return self::reencodeImage($tmpPath, 'imagecreatefrompng', function ($i, $p) {
                imagealphablending($i, false);
                imagesavealpha($i, true);
                return imagepng($i, $p, 6);
            });
        }

        return match ($mime) {
            'application/pdf', 'audio/mpeg', 'audio/x-wav', 'audio/wav', 'audio/vnd.wave'
                => self::run(['exiftool', '-all=', '-overwrite_original', '-q', '-q', $tmpPath], 'exiftool', $tmpPath),
            'video/mp4'
                => self::stripWithFfmpeg($tmpPath),
            default => true,
        };
    }

    /** Nom de fichier neutre : l'original peut contenir des informations personnelles. */
    public static function safeName(UploadedFile $file, int $index): string
    {
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');
        return 'preuve-' . ($index + 1) . '.' . preg_replace('/[^a-z0-9]/', '', $ext);
    }

    private static function reencodeImage(string $path, string $loader, callable $writer): bool
    {
        $size = @getimagesize($path);
        if (!$size || ($size[0] * $size[1]) > self::MAX_PIXELS) {
            return false;
        }
        $img = @$loader($path);
        if (!$img) {
            return false;
        }
        $ok = $writer($img, $path);
        imagedestroy($img);
        return (bool) $ok;
    }

    private static function stripWithFfmpeg(string $path): bool
    {
        $out = $path . '.clean.mp4';
        $ok  = self::run(['ffmpeg', '-y', '-i', $path, '-map_metadata', '-1', '-c', 'copy', '-f', 'mp4', $out], 'ffmpeg', $path);
        if ($ok && is_file($out) && filesize($out) > 0) {
            rename($out, $path);
        } elseif (is_file($out)) {
            @unlink($out);
        }
        return $ok;
    }

    private static function run(array $cmd, string $tool, string $path): bool
    {
        try {
            $p = new Process($cmd);
            $p->setTimeout(60);
            $p->run();
            if ($p->isSuccessful()) {
                return true;
            }
            Log::warning("Nettoyage des métadonnées échoué ({$tool}) : " . trim($p->getErrorOutput()));
        } catch (\Throwable $e) {
            Log::warning("Nettoyage des métadonnées indisponible ({$tool}) : " . $e->getMessage());
        }

        return !config('services.preuves.strict_sanitize', false);
    }
}
