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
        Schema::create('client_satisfaction_analysis', function (Blueprint $table) {
            $table->id();
            $table->string('periodo'); // YYYY-MM
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade'); // Null = Global
            
            $table->integer('total_respuestas')->default(0);

            // Pregunta 1
            $table->integer('pregunta_1_esperado')->default(0);
            $table->integer('pregunta_1_obtenido')->default(0);
            $table->decimal('pregunta_1_porcentaje', 5, 2)->default(0);

            // Pregunta 2
            $table->integer('pregunta_2_esperado')->default(0);
            $table->integer('pregunta_2_obtenido')->default(0);
            $table->decimal('pregunta_2_porcentaje', 5, 2)->default(0);

            // Pregunta 3
            $table->integer('pregunta_3_esperado')->default(0);
            $table->integer('pregunta_3_obtenido')->default(0);
            $table->decimal('pregunta_3_porcentaje', 5, 2)->default(0);

            // Pregunta 4
            $table->integer('pregunta_4_esperado')->default(0);
            $table->integer('pregunta_4_obtenido')->default(0);
            $table->decimal('pregunta_4_porcentaje', 5, 2)->default(0);

            $table->timestamps();

            // Unique index para evitar duplicados del mismo periodo y cliente
            // Usamos una combinación única. Ojo, client_id puede ser null, en algunos motores unique con null funciona ok, en otros no.
            // Para SQLite/MySQL modernos funciona.
            $table->unique(['periodo', 'client_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_satisfaction_analysis');
    }
};
