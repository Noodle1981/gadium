<?php

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
});

require __DIR__.'/auth.php';

