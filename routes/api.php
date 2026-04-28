<?php

use App\Http\Controllers\MobileCourierApiController;
use App\Http\Middleware\IdempotencyMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/courier')->middleware(['auth:sanctum', 'role:courier'])->group(function () {
    Route::get('/assigned-shipments', [MobileCourierApiController::class, 'assignedShipments']);
    Route::get('/shipments/{shipment}', [MobileCourierApiController::class, 'shipmentDetail']);
    
    // Idempotent update for mobile reliability
    Route::post('/shipments/{shipment}/update-status', [MobileCourierApiController::class, 'updateStatus'])
        ->middleware(IdempotencyMiddleware::class);
        
    Route::get('/delivery-history', [MobileCourierApiController::class, 'deliveryHistory']);
});
