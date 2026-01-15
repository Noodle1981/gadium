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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            
            // Excel Columns
            $table->string('moneda')->default('USD');
            $table->string('cc'); // Centro de Costo
            $table->integer('ano');
            $table->string('empresa');
            $table->string('descripcion');
            
            // Financials
            $table->decimal('materiales_presupuestados', 15, 2)->default(0);
            $table->decimal('materiales_comprados', 15, 2)->default(0);
            $table->decimal('resto_valor', 15, 2)->default(0);
            $table->decimal('resto_porcentaje', 10, 2)->default(0); // Can be negative
            $table->decimal('porcentaje_facturacion', 8, 2)->default(0);

            // System
            $table->string('hash')->unique(); // For duplicate detection (CC + Year + Company + Description)
            
            $table->timestamps();
            
            // Indexes
            $table->index(['ano', 'cc']);
            $table->index('empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
