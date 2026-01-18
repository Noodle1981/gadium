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
        Schema::table('hour_details', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('personal')->constrained()->nullOnDelete();
            $table->foreignId('job_function_id')->nullable()->after('funcion')->constrained()->nullOnDelete();
            $table->foreignId('guardia_id')->nullable()->after('hs_pernoctada')->constrained()->nullOnDelete();
            
            // Índices para mejorar queries de Grafana/Dashboards
            $table->index('user_id');
            $table->index('job_function_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hour_details', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['job_function_id']);
            $table->dropForeign(['guardia_id']);
            
            $table->dropColumn(['user_id', 'job_function_id', 'guardia_id']);
        });
    }
};
