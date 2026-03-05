<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceRecordController;
use App\Http\Controllers\Api\StylistController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('dashboard', DashboardController::class);
    Route::get('inventory', [InventoryController::class, 'index']);

    Route::apiResource('items', ItemController::class);
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{client}/history', [ClientController::class, 'history']);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('stylists', StylistController::class);

    Route::post('purchases', [PurchaseController::class, 'store']);
    Route::get('sales', [SaleController::class, 'index']);
    Route::post('sales', [SaleController::class, 'store']);
    Route::get('service-records', [ServiceRecordController::class, 'index']);
    Route::post('service-records', [ServiceRecordController::class, 'store']);
    Route::patch('service-records/{record}', [ServiceRecordController::class, 'update']);
    Route::patch('service-records/{record}/cancel', [ServiceRecordController::class, 'cancel']);
    Route::post('returns', [ReturnController::class, 'store']);

    Route::get('commissions', [CommissionController::class, 'index']);
    Route::get('commissions/summary', [CommissionController::class, 'summary']);

    Route::get('reports/stock-low', [ReportController::class, 'stockLow']);
    Route::get('reports/sales-summary', [ReportController::class, 'salesSummary']);
    Route::get('reports/consumption-summary', [ReportController::class, 'consumptionSummary']);
    Route::get('audit-logs', [AuditLogController::class, 'index']);
});
