<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->enum('categorie', [
                'Administration publique',
                'Police et gendarmerie',
                'Santé et hôpitaux',
                'Éducation et universités',
                'Douanes et frontières',
                'Justice et tribunaux',
                'Marchés publics',
                'Autre',
            ])->change();

            $table->enum('province', [
                'Estuaire',
                'Haut-Ogooué',
                'Moyen-Ogooué',
                'Ngounié',
                'Nyanga',
                'Ogooué-Ivindo',
                'Ogooué-Lolo',
                'Ogooué-Maritime',
                'Woleu-Ntem',
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->string('categorie')->change();
            $table->string('province')->change();
        });
    }
};
