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
        Schema::table('sales', function (Blueprint $table) {
            // Columnas adicionales del formato Tango (18 columnas nuevas)
            $table->string('cod_cli')->nullable()->after('hash'); // Código de cliente
            $table->string('n_remito')->nullable(); // Número de remito
            $table->string('t_comp')->nullable(); // Tipo de comprobante
            $table->string('cond_vta')->nullable(); // Condición de venta
            $table->decimal('porc_desc', 8, 2)->nullable(); // Porcentaje de descuento
            $table->decimal('cotiz', 10, 2)->nullable(); // Cotización
            $table->string('cod_transp')->nullable(); // Código de transporte
            $table->string('nom_transp')->nullable(); // Nombre de transporte
            $table->string('cod_articu')->nullable(); // Código de artículo
            $table->text('descripcio')->nullable(); // Descripción del artículo
            $table->string('cod_dep')->nullable(); // Código de depósito
            $table->string('um')->nullable(); // Unidad de medida
            $table->decimal('cantidad', 10, 2)->nullable(); // Cantidad
            $table->decimal('precio', 12, 2)->nullable(); // Precio unitario
            $table->decimal('tot_s_imp', 12, 2)->nullable(); // Total sin impuestos
            $table->string('n_comp_rem')->nullable(); // Número de comprobante remito
            $table->decimal('cant_rem', 10, 2)->nullable(); // Cantidad remito
            $table->date('fecha_rem')->nullable(); // Fecha de remito
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'cod_cli', 'n_remito', 't_comp', 'cond_vta', 'porc_desc', 'cotiz',
                'cod_transp', 'nom_transp', 'cod_articu', 'descripcio', 'cod_dep',
                'um', 'cantidad', 'precio', 'tot_s_imp', 'n_comp_rem', 'cant_rem', 'fecha_rem'
            ]);
        });
    }
};
