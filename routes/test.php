<?php

use Illuminate\Support\Facades\Route;
use App\Services\CsvImportService;
use Illuminate\Http\Request;

Route::get('/test-import', function (Request $request) {
    // 1. Fetch History from Database (Proof of Import)
    $sales = \App\Models\Sale::with('client')->latest()->take(20)->get();
    $budgets = \App\Models\Budget::with('client')->latest()->take(20)->get();

    // 2. Setup Test Files (Optional context)
    $testFiles = [
        '1_ventas_test', '2_ventas_test', '3_ventas_test'
    ];
    $selectedFile = $request->get('file', '1_ventas_test');
    $type = $request->get('type', 'sale');

    // 3. Optional: Run Analysis if needed, but don't block invalid files
    $result = null;
    $error = null;

    // Return view with data
    return view('test-import', [
        'sales' => $sales,
        'budgets' => $budgets,
        'testFiles' => $testFiles,
        'selectedFile' => $selectedFile,
        'type' => $type,
        'result' => $result,
        'error' => $error
    ]);
})->name('test.import');
