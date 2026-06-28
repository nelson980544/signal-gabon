<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            // Email optionnel : le citoyen peut choisir d'en fournir un
            // pour recevoir des notifications de statut. Non obligatoire
            // afin de préserver l'anonymat par défaut.
            $table->string('email_contact')->nullable()->after('message_agent');
        });
    }

    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->dropColumn('email_contact');
        });
    }
};
