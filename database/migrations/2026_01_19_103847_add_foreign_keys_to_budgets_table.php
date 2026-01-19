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
        Schema::table('budgets', function (Blueprint $table) {
            // Agregar project_id FK (nullable)
            $table->string('project_id')->nullable()->after('nombre_proyecto');
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            
            // Agregar cost_center_id FK (nullable)
            $table->foreignId('cost_center_id')->nullable()->after('centro_costo')
                  ->constrained()->nullOnDelete();
            
            // Índices para performance
            $table->index('project_id');
            $table->index('cost_center_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['cost_center_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['cost_center_id']);
            $table->dropColumn(['project_id', 'cost_center_id']);
        });
    }
};
