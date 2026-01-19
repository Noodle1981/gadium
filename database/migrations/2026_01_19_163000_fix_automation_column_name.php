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
        Schema::table('automation_projects', function (Blueprint $table) {
            if (Schema::hasColumn('automation_projects', 'proyecto_codigo')) {
                $table->renameColumn('proyecto_codigo', 'proyecto_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automation_projects', function (Blueprint $table) {
            $table->renameColumn('proyecto_id', 'proyecto_codigo');
        });
    }
};
