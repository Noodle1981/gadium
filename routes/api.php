<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicDataController;

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

/*
|--------------------------------------------------------------------------
| Public API Routes (No Authentication Required)
|--------------------------------------------------------------------------
|
| These routes are available without authentication for external applications
| to consume operational data.
|
| Base URL: /api/v1/public
|
| Available endpoints:
| - GET /sales              - List all sales (paginated)
| - GET /sales/{id}         - Get a single sale
| - GET /budgets            - List all budgets (paginated)
| - GET /budgets/{id}       - Get a single budget
| - GET /hours              - List all hour details (paginated)
| - GET /hours/{id}         - Get a single hour detail
| - GET /purchases          - List all purchases (paginated)
| - GET /purchases/{id}     - Get a single purchase
| - GET /boards             - List all board details (paginated)
| - GET /boards/{id}        - Get a single board detail
| - GET /automation-projects - List all automation projects (paginated)
| - GET /automation-projects/{id} - Get a single automation project
| - GET /client-satisfaction - List all client satisfaction responses (paginated)
| - GET /client-satisfaction/{id} - Get a single client satisfaction response
| - GET /staff-satisfaction  - List all staff satisfaction responses (paginated)
| - GET /staff-satisfaction/{id} - Get a single staff satisfaction response
|
| Common query parameters:
| - per_page: Number of records per page (default: 100, max: 1000)
| - page: Page number
| - fecha_desde: Filter from date (Y-m-d)
| - fecha_hasta: Filter to date (Y-m-d)
|
*/
Route::prefix('v1/public')->group(function () {
    // Sales
    Route::get('/sales', [PublicDataController::class, 'sales']);
    Route::get('/sales/{id}', [PublicDataController::class, 'showSale']);

    // Budgets
    Route::get('/budgets', [PublicDataController::class, 'budgets']);
    Route::get('/budgets/{id}', [PublicDataController::class, 'showBudget']);

    // Hours
    Route::get('/hours', [PublicDataController::class, 'hours']);
    Route::get('/hours/{id}', [PublicDataController::class, 'showHour']);

    // Purchases
    Route::get('/purchases', [PublicDataController::class, 'purchases']);
    Route::get('/purchases/{id}', [PublicDataController::class, 'showPurchase']);

    // Boards
    Route::get('/boards', [PublicDataController::class, 'boards']);
    Route::get('/boards/{id}', [PublicDataController::class, 'showBoard']);

    // Automation Projects
    Route::get('/automation-projects', [PublicDataController::class, 'automationProjects']);
    Route::get('/automation-projects/{id}', [PublicDataController::class, 'showAutomationProject']);

    // Client Satisfaction
    Route::get('/client-satisfaction', [PublicDataController::class, 'clientSatisfaction']);
    Route::get('/client-satisfaction/{id}', [PublicDataController::class, 'showClientSatisfaction']);

    // Staff Satisfaction
    Route::get('/staff-satisfaction', [PublicDataController::class, 'staffSatisfaction']);
    Route::get('/staff-satisfaction/{id}', [PublicDataController::class, 'showStaffSatisfaction']);

    // ========================================
    // METRICS / ANALYTICS
    // ========================================

    // Sales by client with percentages
    Route::get('/metrics/sales-by-client', [PublicDataController::class, 'salesByClient']);

    // Top 20% clients sales (Pareto)
    Route::get('/metrics/sales-top-20-clients', [PublicDataController::class, 'salesTop20Clients']);

    // Approved budgets percentage
    Route::get('/metrics/budgets-approved-percentage', [PublicDataController::class, 'budgetsApprovedPercentage']);

    // Budgets deadline deviations
    Route::get('/metrics/budgets-deadline-deviations', [PublicDataController::class, 'budgetsDeadlineDeviations']);

    // Total weighted hours from budgets
    Route::get('/metrics/budgets-total-weighted-hours', [PublicDataController::class, 'budgetsTotalWeightedHours']);

    // Hours percentage for projects < 1001
    Route::get('/metrics/hours-projects-under-1001', [PublicDataController::class, 'hoursProjectsUnder1001']);

    // Hours for project 606
    Route::get('/metrics/hours-project-606', [PublicDataController::class, 'hoursProject606']);

    // Budgets count by status
    Route::get('/metrics/budgets-by-status', [PublicDataController::class, 'budgetsByStatus']);
});
