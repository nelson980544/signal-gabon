<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prépare le chiffrement applicatif (casts "encrypted") et chiffre les données
 * déjà présentes. Le texte chiffré est plus long que le clair : les colonnes
 * concernées passent en TEXT. Les fichiers déjà stockés sont chiffrés par la
 * commande `php artisan preuves:chiffrer` (le stockage peut être distant).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->text('email_contact')->nullable()->change();
        });
        Schema::table('preuves', function (Blueprint $table) {
            $table->text('nom_fichier')->change();
            $table->boolean('chiffre')->default(false)->after('taille');
        });

        $this->encryptColumn('signalements', 'description');
        $this->encryptColumn('signalements', 'message_agent');
        $this->encryptColumn('signalements', 'email_contact');
        $this->encryptColumn('signalement_historiques', 'commentaire');
        $this->encryptColumn('preuves', 'nom_fichier');
    }

    public function down(): void
    {
        $this->decryptColumn('signalements', 'description');
        $this->decryptColumn('signalements', 'message_agent');
        $this->decryptColumn('signalements', 'email_contact');
        $this->decryptColumn('signalement_historiques', 'commentaire');
        $this->decryptColumn('preuves', 'nom_fichier');

        Schema::table('preuves', function (Blueprint $table) {
            $table->dropColumn('chiffre');
        });
    }

    private function encryptColumn(string $table, string $column): void
    {
        DB::table($table)->whereNotNull($column)->orderBy('id')->chunkById(200, function ($rows) use ($table, $column) {
            foreach ($rows as $row) {
                if ($this->isEncrypted($row->$column)) {
                    continue;
                }
                DB::table($table)->where('id', $row->id)->update([$column => Crypt::encryptString($row->$column)]);
            }
        });
    }

    private function decryptColumn(string $table, string $column): void
    {
        DB::table($table)->whereNotNull($column)->orderBy('id')->chunkById(200, function ($rows) use ($table, $column) {
            foreach ($rows as $row) {
                try {
                    $clear = Crypt::decryptString($row->$column);
                } catch (DecryptException) {
                    continue;
                }
                DB::table($table)->where('id', $row->id)->update([$column => $clear]);
            }
        });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
