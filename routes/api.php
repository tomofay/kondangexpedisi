<?php

use App\Http\Controllers\MobileRoleApiController;
use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile/auth')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login'])->name('mobile.auth.login');
});

Route::middleware(['require.bearer', 'auth:sanctum'])->group(function () {
    Route::prefix('mobile/auth')->group(function () {
        Route::get('/me', [MobileAuthController::class, 'me'])->name('mobile.auth.me');
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('mobile.auth.logout');
    });

    Route::get('/notifications/push-ready', [NotificationController::class, 'pushReady'])->name('notifications.push-ready');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::middleware('role:customer')->group(function () {
        Route::get('/notifications/customer', [NotificationController::class, 'customerIndex'])->name('notifications.customer.index');

        Route::prefix('mobile/customer')->group(function () {
            Route::get('/shipments', [MobileRoleApiController::class, 'customerShipments'])->name('mobile.customer.shipments.index');
            Route::get('/shipments/{shipment}', [MobileRoleApiController::class, 'customerShipmentDetail'])->name('mobile.customer.shipments.show');
        });
    });

    Route::middleware('role:courier')->prefix('mobile/courier')->group(function () {
        Route::get('/shipments', [MobileRoleApiController::class, 'courierShipments'])->name('mobile.courier.shipments.index');
        Route::get('/shipments/{shipment}', [MobileRoleApiController::class, 'courierShipmentDetail'])->name('mobile.courier.shipments.show');
        Route::get('/shipments/{shipment}/operational-proofs', [MobileRoleApiController::class, 'courierOperationalProofIndex'])->name('mobile.courier.operational-proofs.index');
        Route::post('/shipments/{shipment}/operational-proofs', [MobileRoleApiController::class, 'courierOperationalProofStore'])->name('mobile.courier.operational-proofs.store');
        Route::post('/offline-sync', [MobileRoleApiController::class, 'courierOfflineSync'])->name('mobile.courier.offline-sync');
    });
});