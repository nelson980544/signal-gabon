<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('preuves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signalement_id')->constrained()->cascadeOnDelete();
            $table->string('nom_fichier');
            $table->string('chemin');
            $table->string('type_fichier');
            $table->integer('taille');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('preuves'); }
};
