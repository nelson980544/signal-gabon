<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deux mesures de protection du lanceur d'alerte :
 *
 *  - email_contact passe en `text` pour accueillir sa version chiffrée,
 *    plus longue que les 255 caractères d'origine ;
 *  - les noms de fichiers d'origine déjà enregistrés sont remplacés par un
 *    libellé neutre, car ils peuvent désigner celui qui les a déposés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->text('email_contact')->nullable()->change();
        });

        $this->renommerPreuvesExistantes();
    }

    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->string('email_contact')->nullable()->change();
        });

        // Les noms d'origine ne sont pas restaurables : c'est le but.
    }

    private function renommerPreuvesExistantes(): void
    {
        $rangs = [];

        DB::table('preuves')->orderBy('signalement_id')->orderBy('id')
            ->select('id', 'signalement_id', 'nom_fichier')
            ->each(function ($preuve) use (&$rangs) {
                $rang = ($rangs[$preuve->signalement_id] ?? 0) + 1;
                $rangs[$preuve->signalement_id] = $rang;

                $extension = strtolower(pathinfo($preuve->nom_fichier, PATHINFO_EXTENSION));
                $libelle = 'Preuve ' . $rang . ($extension !== '' ? '.' . $extension : '');

                DB::table('preuves')->where('id', $preuve->id)
                    ->update(['nom_fichier' => $libelle]);
            });
    }
};
