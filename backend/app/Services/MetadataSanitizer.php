<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Supprime les métadonnées d'un fichier déposé avant son stockage.
 *
 * Un fichier de preuve trahit son auteur plus sûrement que son contenu : une
 * photo porte les coordonnées GPS du lieu où elle a été prise, un PDF exporté
 * depuis un traitement de texte porte le nom du compte qui l'a produit, une
 * vidéo porte le modèle du téléphone et parfois sa position.
 *
 * Les images passent par GD, qui réécrit le fichier sans aucun de ses segments
 * de métadonnées. Les autres formats passent par exiftool.
 */
class MetadataSanitizer
{
    /** Formats traités par GD, sans dépendance externe. */
    private const IMAGES = ['image/jpeg', 'image/jpg', 'image/png'];

    /** Formats confiés à exiftool. */
    private const AUTRES = [
        'application/pdf',
        'audio/mpeg',
        'audio/wav',
        'audio/x-wav',
        'video/mp4',
    ];

    public static function strip(string $tmpPath): void
    {
        $mime = mime_content_type($tmpPath);

        if (in_array($mime, self::IMAGES, true)) {
            self::nettoyerImage($tmpPath, $mime);

            return;
        }

        if (in_array($mime, self::AUTRES, true)) {
            self::nettoyerAvecExiftool($tmpPath, $mime);
        }

        if ($mime === 'application/pdf') {
            // exiftool procède par mise à jour incrémentale : il marque les
            // métadonnées comme supprimées mais laisse les octets d'origine
            // dans le fichier, d'où son propre avertissement « PDF edits are
            // reversible ». Réécrire le fichier avec qpdf les fait disparaître.
            self::reecrirePdf($tmpPath);
        }
    }

    private static function nettoyerImage(string $tmpPath, string $mime): void
    {
        if ($mime === 'image/png') {
            $img = @imagecreatefrompng($tmpPath);
            if ($img) {
                imagealphablending($img, false);
                imagesavealpha($img, true);
                imagepng($img, $tmpPath, 6);
                imagedestroy($img);
            }

            return;
        }

        $img = @imagecreatefromjpeg($tmpPath);
        if ($img) {
            imagejpeg($img, $tmpPath, 85);
            imagedestroy($img);
        }
    }

    private static function nettoyerAvecExiftool(string $tmpPath, string $mime): void
    {
        $exiftool = self::chemin('exiftool');

        if ($exiftool === null) {
            // Jamais d'échec silencieux : la plateforme annonce au citoyen que
            // les métadonnées de ses fichiers sont supprimées.
            Log::warning('exiftool introuvable : métadonnées non supprimées', [
                'type_fichier' => $mime,
            ]);

            return;
        }

        try {
            $process = new Process([$exiftool, '-all=', '-overwrite_original', '-q', $tmpPath]);
            $process->setTimeout(30);
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            Log::warning('exiftool a échoué : métadonnées non supprimées', [
                'type_fichier' => $mime,
                'sortie' => $process->getErrorOutput(),
            ]);
        }
    }

    private static function reecrirePdf(string $tmpPath): void
    {
        $qpdf = self::chemin('qpdf');

        if ($qpdf === null) {
            Log::warning('qpdf introuvable : métadonnées du PDF récupérables dans les octets du fichier');

            return;
        }

        $sortie = $tmpPath . '.propre';

        $process = new Process([$qpdf, '--linearize', $tmpPath, $sortie]);
        $process->setTimeout(30);
        $process->run();

        // qpdf sort en code 3 sur un avertissement tout en produisant un
        // fichier valide ; seul un fichier de sortie exploitable compte.
        if (is_file($sortie) && filesize($sortie) > 0) {
            rename($sortie, $tmpPath);

            return;
        }

        @unlink($sortie);
        Log::warning('qpdf a échoué : métadonnées du PDF récupérables', [
            'sortie' => $process->getErrorOutput(),
        ]);
    }

    private static function chemin(string $outil): ?string
    {
        static $chemins = [];

        if (! array_key_exists($outil, $chemins)) {
            $recherche = new Process(['which', $outil]);
            $recherche->run();
            $chemins[$outil] = $recherche->isSuccessful() ? trim($recherche->getOutput()) : null;
        }

        return $chemins[$outil];
    }
}
