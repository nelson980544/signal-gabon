<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->index('statut');
            $table->index('categorie');
            $table->index('province');
            $table->index('agent_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropIndex(['categorie']);
            $table->dropIndex(['province']);
            $table->dropIndex(['agent_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
