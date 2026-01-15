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
        Schema::create('board_details', function (Blueprint $table) {
            $table->id();
            $table->integer('ano');
            $table->string('proyecto_numero');
            $table->string('cliente');
            $table->text('descripcion_proyecto');
            
            // Numeric fields, default to 0 as per plan (Excel has 0 or number)
            $table->integer('columnas')->default(0);
            $table->integer('gabinetes')->default(0);
            $table->integer('potencia')->default(0);
            $table->integer('pot_control')->default(0);
            $table->integer('control')->default(0);
            $table->integer('intervencion')->default(0);
            $table->integer('documento_correccion_fallas')->default(0);
            
            $table->string('hash')->unique(); // SHA256(ano + proyecto_numero + cliente + descripcion_proyecto)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_details');
    }
};
