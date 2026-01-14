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
        Schema::table('budgets', function (Blueprint $table) {
            // Columnas adicionales del formato de Presupuestos (12 columnas nuevas)
            $table->string('centro_costo')->nullable()->after('hash'); // Centro de Costo
            $table->string('nombre_proyecto')->nullable(); // Nombre Proyecto
            $table->date('fecha_oc')->nullable(); // Fecha de OC
            $table->date('fecha_estimada_culminacion')->nullable(); // Fecha estimada de culminación
            $table->integer('estado_proyecto_dias')->nullable(); // Estado del proyecto en días
            $table->date('fecha_culminacion_real')->nullable(); // Fecha de culminación real
            $table->string('estado')->nullable(); // Estado (Aprobado, Pendiente, etc.)
            $table->string('enviado_facturar')->nullable(); // Enviado a facturar
            $table->string('nro_factura')->nullable(); // Nº de Factura
            $table->string('porc_facturacion')->nullable(); // % Facturación
            $table->decimal('saldo', 12, 2)->nullable(); // Saldo [$]
            $table->decimal('horas_ponderadas', 10, 2)->nullable(); // Horas ponderadas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn([
                'centro_costo', 'nombre_proyecto', 'fecha_oc', 'fecha_estimada_culminacion',
                'estado_proyecto_dias', 'fecha_culminacion_real', 'estado', 'enviado_facturar',
                'nro_factura', 'porc_facturacion', 'saldo', 'horas_ponderadas'
            ]);
        });
    }
};
