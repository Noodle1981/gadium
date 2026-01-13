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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->index();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->decimal('monto', 15, 2);
            $table->string('moneda', 10)->default('USD');
            $table->string('hash', 64)->unique()->comment('Hash único para idempotencia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
