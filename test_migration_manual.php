<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Attempting to create table automation_projects...\n";
    
    if (Schema::hasTable('automation_projects')) {
        echo "Table already exists! Dropping it...\n";
        Schema::drop('automation_projects');
    }

    Schema::create('automation_projects', function (Blueprint $table) {
        $table->id();
        $table->string('proyecto_id');
        $table->string('cliente');
        $table->text('proyecto_descripcion');
        $table->string('fat')->default('NO');
        $table->string('pem')->default('NO');
        $table->string('hash')->unique();
        $table->timestamps();
        
        $table->index('proyecto_id');
        $table->index('cliente');
        $table->index('fat');
        $table->index('pem');
    });
    echo "Table created successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
