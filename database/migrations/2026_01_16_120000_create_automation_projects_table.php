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
        Schema::create('automation_projects', function (Blueprint $table) {
            $table->id();
            
            // Campos principales del Excel
            $table->string('proyecto_id'); // ID del proyecto (ej: 3503, 3530)
            $table->string('cliente'); // Nombre del cliente
            $table->text('proyecto_descripcion'); // Descripción del proyecto
            $table->string('fat')->default('NO'); // FAT: SI/NO
            $table->string('pem')->default('NO'); // PEM: SI/NO
            
            // Control de idempotencia y duplicados
            $table->string('hash')->unique();
            
            $table->timestamps();
            
            // Índices para búsquedas frecuentes
            $table->index('proyecto_id');
            $table->index('cliente');
            $table->index('fat');
            $table->index('pem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_projects');
    }
};
