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
        Schema::create('projects', function (Blueprint $table) {
            $table->string('id')->primary(); // Ej: "3400"
            $table->string('name');
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('activo');
            $table->string('quality_status')->default('normal'); // 'normal' | 'crítico'
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
