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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('cliente_nombre'); // Nombre original del CSV
            $table->decimal('monto', 12, 2);
            $table->string('comprobante');
            $table->string('hash')->unique(); // SHA-256 para idempotencia
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
