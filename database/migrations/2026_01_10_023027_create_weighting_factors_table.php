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
        Schema::create('weighting_factors', function (Blueprint $table) {
            $table->id();
            $table->string('role_name'); // Vinculado a Spatie Roles
            $table->decimal('value', 10, 8); // Alta precisión decimal
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices para búsqueda rápida de factor vigente
            $table->index(['role_name', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weighting_factors');
    }
};
