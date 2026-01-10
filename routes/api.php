<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Endpoints para Grafana (Inteligencia de Negocios)
    Route::prefix('v1/metrics')->group(function () {
        Route::get('/sales-concentration', [App\Http\Controllers\Api\MetricsController::class, 'salesConcentration']);
        Route::get('/production-efficiency', [App\Http\Controllers\Api\MetricsController::class, 'productionEfficiency']);
    });
});
