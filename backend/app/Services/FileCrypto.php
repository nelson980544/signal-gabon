<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Chiffrement authentifié des preuves avant stockage (disque local ou R2/S3).
 *
 * Format : "SGE1" | en-tête secretstream (24 o) | blocs [longueur 4 o | chiffré].
 * Algorithme : libsodium secretstream XChaCha20-Poly1305, par blocs de 64 Kio.
 * Le chemin de stockage est lié au chiffré (données associées) : un fichier
 * déplacé vers un autre dossier ou tronqué ne se déchiffre pas.
 */
class FileCrypto
{
    private const MAGIC = 'SGE1';
    private const CHUNK = 65536;

    /** Chiffre $srcPath et l'écrit sur $disk à $path. */
    public static function encryptToDisk(string $srcPath, string $path, ?string $disk = null): void
    {
        $in = fopen($srcPath, 'rb');
        $out = fopen('php://temp', 'w+b');
        if (! $in || ! $out) {
            throw new RuntimeException('Impossible de lire le fichier à chiffrer.');
        }

        [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push(self::key());
        fwrite($out, self::MAGIC . $header);

        $buffer = self::readChunk($in);
        do {
            $next = self::readChunk($in);
            $last = $next === '';
            $cipher = sodium_crypto_secretstream_xchacha20poly1305_push(
                $state,
                $buffer,
                $path,
                $last ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL : SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE,
            );
            fwrite($out, pack('N', strlen($cipher)) . $cipher);
            $buffer = $next;
        } while (! $last);

        fclose($in);
        rewind($out);
        Storage::disk($disk)->put($path, $out);
        fclose($out);
        sodium_memzero($buffer);
    }

    /** Chiffre un fichier déjà stocké en clair (migration de l'existant). */
    public static function encryptStoredInPlace(string $path, ?string $disk = null): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'sg');
        try {
            file_put_contents($tmp, Storage::disk($disk)->get($path));
            self::encryptToDisk($tmp, $path, $disk);
        } finally {
            @unlink($tmp);
        }
    }

    /** Génère le contenu déchiffré, bloc par bloc. */
    public static function decryptStream(string $path, ?string $disk = null): \Generator
    {
        $in = Storage::disk($disk)->readStream($path);
        if (! $in) {
            throw new RuntimeException('Fichier introuvable.');
        }

        try {
            if (fread($in, 4) !== self::MAGIC) {
                throw new RuntimeException('Format de fichier chiffré invalide.');
            }
            $header = fread($in, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES);
            if (strlen($header) !== SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES) {
                throw new RuntimeException('En-tête chiffré tronqué.');
            }
            $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, self::key());

            $final = false;
            while (! $final) {
                $len = fread($in, 4);
                if (strlen($len) !== 4) {
                    throw new RuntimeException('Fichier chiffré tronqué.');
                }
                $len = unpack('N', $len)[1];
                $cipher = self::readExactly($in, $len);
                $res = sodium_crypto_secretstream_xchacha20poly1305_pull($state, $cipher, $path);
                if ($res === false) {
                    throw new RuntimeException('Fichier chiffré corrompu ou altéré.');
                }
                [$plain, $tag] = $res;
                $final = $tag === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL;
                yield $plain;
            }
        } finally {
            fclose($in);
        }
    }

    /** Vrai si le fichier stocké porte déjà l'en-tête de chiffrement. */
    public static function isEncrypted(string $path, ?string $disk = null): bool
    {
        $in = Storage::disk($disk)->readStream($path);
        if (! $in) {
            return false;
        }
        $magic = fread($in, 4);
        fclose($in);

        return $magic === self::MAGIC;
    }

    private static function key(): string
    {
        $configured = config('security.files_key');
        if ($configured) {
            $key = base64_decode($configured, true);
            if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES) {
                throw new RuntimeException('FILES_ENCRYPTION_KEY doit être 32 octets encodés en base64.');
            }

            return $key;
        }

        $appKey = (string) config('app.key');
        if ($appKey === '') {
            throw new RuntimeException('Aucune clé de chiffrement configurée.');
        }
        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        return hash_hkdf('sha256', $appKey, 32, 'signalgabon-preuves-v1');
    }

    private static function readChunk($handle): string
    {
        return self::readExactly($handle, self::CHUNK, true);
    }

    private static function readExactly($handle, int $length, bool $allowShort = false): string
    {
        $data = '';
        while (strlen($data) < $length && ! feof($handle)) {
            $part = fread($handle, $length - strlen($data));
            if ($part === false || $part === '') {
                break;
            }
            $data .= $part;
        }
        if (! $allowShort && strlen($data) !== $length) {
            throw new RuntimeException('Fichier chiffré tronqué.');
        }

        return $data;
    }
}
