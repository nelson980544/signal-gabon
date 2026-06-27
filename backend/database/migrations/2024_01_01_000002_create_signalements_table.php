<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('signalements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('categorie', ['Administration publique','Police et gendarmerie','Santé et hôpitaux','Éducation et universités','Douanes et frontières','Justice et tribunaux','Marchés publics','Autre']);
            $table->text('description');
            $table->date('date_faits');
            $table->enum('province', ['Estuaire','Haut-Ogooué','Moyen-Ogooué','Ngounié','Nyanga','Ogooué-Ivindo','Ogooué-Lolo','Ogooué-Maritime','Woleu-Ntem']);
            $table->string('ville');
            $table->enum('statut', ['recu','en_examen','attribue','en_instruction','traite','classe'])->default('recu');
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message_agent')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('signalements'); }
};
