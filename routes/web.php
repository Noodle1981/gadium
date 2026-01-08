<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    
    // Gestión de Usuarios (Solo Admin y Super Admin)
    Route::middleware(['role:Super Admin|Admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
    
    // Gestión de Roles (Solo Super Admin)
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('roles', RoleController::class);
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])
            ->name('roles.permissions');
        Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
            ->name('roles.permissions.update');
    });
});

require __DIR__.'/auth.php';


