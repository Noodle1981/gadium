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
        Schema::create('hour_details', function (Blueprint $table) {
            $table->id();
            
            // Campos principales del Excel
            $table->string('dia');
            $table->date('fecha');
            $table->integer('ano');
            $table->integer('mes');
            $table->string('personal');
            $table->string('funcion');
            $table->string('proyecto');
            
            // Campos numéricos / métricas
            $table->decimal('horas_ponderadas', 10, 4);
            $table->decimal('ponderador', 8, 4);
            $table->decimal('hs', 8, 2);
            $table->decimal('hs_comun', 8, 2);
            $table->decimal('hs_50', 8, 2);
            $table->decimal('hs_100', 8, 2);
            $table->decimal('hs_viaje', 8, 2);
            $table->string('hs_pernoctada')->default('No'); // Sí/No
            $table->decimal('hs_adeudadas', 8, 2);
            $table->string('vianda')->default('0');
            
            // Campos adicionales / metadatos
            $table->text('observacion')->nullable();
            $table->string('programacion')->nullable();
            
            // Control de idempotencia y duplicados
            $table->string('hash')->unique();
            
            $table->timestamps();
            
            // Índices para búsquedas frecuentes
            $table->index('fecha');
            $table->index('personal');
            $table->index('proyecto');
            $table->index('mes');
            $table->index('ano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hour_details');
    }
};
