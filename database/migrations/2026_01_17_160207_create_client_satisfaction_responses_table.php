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
        Schema::create('client_satisfaction_responses', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('cliente_nombre'); // Nombre original (para referencia)
            $table->string('proyecto')->nullable();
            
            // Preguntas (Ratings 1-5)
            $table->tinyInteger('pregunta_1')->default(0); // Obra/Producto
            $table->tinyInteger('pregunta_2')->default(0); // Desempeño Técnico
            $table->tinyInteger('pregunta_3')->default(0); // Necesidades
            $table->tinyInteger('pregunta_4')->default(0); // Plazo
            
            $table->string('hash')->unique(); // Idempotencia
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_satisfaction_responses');
    }
};
