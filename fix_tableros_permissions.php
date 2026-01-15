<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'tableros@gadium.com')->first();

if ($user) {
    // Remove all direct permissions
    $user->syncPermissions([]);
    
    // Ensure only has Gestor de Tableros role
    $user->syncRoles(['Gestor de Tableros']);
    
    echo "✓ User permissions fixed\n";
    echo "  Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "  Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";
} else {
    echo "✗ User not found\n";
}
