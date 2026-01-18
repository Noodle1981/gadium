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
        // Tabla para funciones de trabajo (e.g. Oficial, Ayudante)
        Schema::create('job_functions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla para tipos de guardia (e.g. Pasiva, Activa)
        Schema::create('guardias', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla para alias de usuarios (para normalización de nombres de importación)
        Schema::create('user_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('alias')->unique(); // Nombre tal cual viene en el Excel o normalizado
            $table->timestamps();
            
            $table->index('alias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_aliases');
        Schema::dropIfExists('guardias');
        Schema::dropIfExists('job_functions');
    }
};
