<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_satisfaction_responses', function (Blueprint $table) {
            $table->id();
            $table->string('personal'); // Nombre del operario
            $table->date('fecha')->nullable(); // Fecha de la encuesta (asignada manual)

            // Pregunta 1: Trato del jefe
            $table->boolean('p1_mal')->default(false);
            $table->boolean('p1_normal')->default(false);
            $table->boolean('p1_bien')->default(false);
            
            // Pregunta 2: Trato de compañeros
            $table->boolean('p2_mal')->default(false);
            $table->boolean('p2_normal')->default(false);
            $table->boolean('p2_bien')->default(false);
            
            // Pregunta 3: Clima laboral
            $table->boolean('p3_mal')->default(false);
            $table->boolean('p3_normal')->default(false);
            $table->boolean('p3_bien')->default(false);
            
            // Pregunta 4: Comodidad
            $table->boolean('p4_mal')->default(false);
            $table->boolean('p4_normal')->default(false);
            $table->boolean('p4_bien')->default(false);
            
            $table->string('hash')->unique(); // Idempotencia
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_satisfaction_responses');
    }
};
