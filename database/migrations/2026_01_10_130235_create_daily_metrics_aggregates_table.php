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
        Schema::create('daily_metrics_aggregates', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date');
            $table->string('metric_type'); // 'sales_concentration', 'production_efficiency', etc.
            $table->json('metric_data'); // Datos calculados
            $table->timestamps();
            
            $table->unique(['metric_date', 'metric_type']);
            $table->index('metric_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_metrics_aggregates');
    }
};
