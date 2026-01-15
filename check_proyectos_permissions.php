<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'proyectos@gadium.com')->first();

if ($user) {
    echo "Current Status:\n";
    echo "  Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "  All Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";
    echo "  Has view_sales: " . ($user->can('view_sales') ? 'YES ❌' : 'NO ✓') . "\n";
    echo "  Has view_automation: " . ($user->can('view_automation') ? 'YES ✓' : 'NO ❌') . "\n\n";
    
    // Fix if needed
    if ($user->can('view_sales') || $user->getAllPermissions()->count() > 1) {
        echo "Fixing permissions...\n";
        $user->syncPermissions([]);
        $user->syncRoles(['Gestor de Proyectos']);
        
        echo "\n✓ User permissions fixed\n";
        echo "  Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
        echo "  Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";
    } else {
        echo "✓ Permissions are correct, no changes needed\n";
    }
} else {
    echo "✗ User not found\n";
}
