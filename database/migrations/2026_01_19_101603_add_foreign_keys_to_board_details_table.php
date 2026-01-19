<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('board_details', function (Blueprint $table) {
            // Agregar project_id FK (nullable para no romper datos existentes)
            $table->string('project_id')->nullable()->after('proyecto_numero');
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            
            // Agregar client_id FK (nullable)
            $table->foreignId('client_id')->nullable()->after('cliente')
                  ->constrained()->nullOnDelete();
            
            // Índices para performance
            $table->index('project_id');
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_details', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['client_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['client_id']);
            $table->dropColumn(['project_id', 'client_id']);
        });
    }
};
