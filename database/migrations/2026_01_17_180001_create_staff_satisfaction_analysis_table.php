<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_satisfaction_analysis', function (Blueprint $table) {
            $table->id();
            $table->string('periodo')->unique(); // YYYY-MM
            
            // Pregunta 1 counts
            $table->integer('p1_mal_count')->default(0);
            $table->integer('p1_normal_count')->default(0);
            $table->integer('p1_bien_count')->default(0);
            $table->decimal('p1_mal_pct', 5, 2)->default(0);
            $table->decimal('p1_normal_pct', 5, 2)->default(0);
            $table->decimal('p1_bien_pct', 5, 2)->default(0);
            
            // Pregunta 2 counts
            $table->integer('p2_mal_count')->default(0);
            $table->integer('p2_normal_count')->default(0);
            $table->integer('p2_bien_count')->default(0);
            $table->decimal('p2_mal_pct', 5, 2)->default(0);
            $table->decimal('p2_normal_pct', 5, 2)->default(0);
            $table->decimal('p2_bien_pct', 5, 2)->default(0);
            
            // Pregunta 3 counts
            $table->integer('p3_mal_count')->default(0);
            $table->integer('p3_normal_count')->default(0);
            $table->integer('p3_bien_count')->default(0);
            $table->decimal('p3_mal_pct', 5, 2)->default(0);
            $table->decimal('p3_normal_pct', 5, 2)->default(0);
            $table->decimal('p3_bien_pct', 5, 2)->default(0);
            
            // Pregunta 4 counts
            $table->integer('p4_mal_count')->default(0);
            $table->integer('p4_normal_count')->default(0);
            $table->integer('p4_bien_count')->default(0);
            $table->decimal('p4_mal_pct', 5, 2)->default(0);
            $table->decimal('p4_normal_pct', 5, 2)->default(0);
            $table->decimal('p4_bien_pct', 5, 2)->default(0);
            
            $table->integer('total_respuestas')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_satisfaction_analysis');
    }
};
