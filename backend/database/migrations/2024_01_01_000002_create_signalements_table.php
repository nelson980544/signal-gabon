<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('signalements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('categorie');
            $table->text('description');
            $table->date('date_faits');
            $table->string('province');
            $table->string('ville');
            $table->enum('statut', ['recu','en_examen','attribue','en_instruction','traite','classe'])->default('recu');
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message_agent')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('signalements'); }
};
