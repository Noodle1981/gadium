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
        Schema::table('manufacturing_logs', function (Blueprint $table) {
            $table->decimal('hours_clock', 8, 2)->after('correction_documents')->default(0);
            $table->decimal('hours_weighted', 10, 2)->after('hours_clock')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manufacturing_logs', function (Blueprint $table) {
            //
        });
    }
};
