<?php

use App\Http\Controllers\MidtransPaymentController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\LandingPageContentController;
use App\Http\Controllers\DashboardDataController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentItemController;
use App\Http\Controllers\ShipmentStatusController;
use App\Http\Controllers\ShipmentTrackingController;
use App\Http\Controllers\RateCardController;
use App\Http\Controllers\TrackingLookupController;
use App\Http\Controllers\AdminTrashController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ZoneController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicLandingController::class)->name('landing');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'role:admin,kasir,courier,manager'])->name('dashboard');

Route::get('/tracking/{trackingNumber}', [TrackingLookupController::class, 'show'])
    ->middleware('throttle:20,1')
    ->name('tracking.lookup');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/data', DashboardDataController::class)->name('dashboard.data');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/payments/{payment}/midtrans/snap-token', [MidtransPaymentController::class, 'createSnapToken'])
        ->middleware('role:admin,kasir,manager,customer')
        ->name('payments.midtrans.snap-token');

    Route::middleware('role:admin,kasir,courier,manager')->group(function () {
        Route::resource('shipments', ShipmentController::class)->except(['create', 'edit']);
        Route::get('shipments/{shipment}/label', [ShipmentController::class, 'label'])->name('shipments.label');
        Route::post('shipments/{shipment}/assign-courier', [ShipmentController::class, 'assignCourier'])
            ->name('shipments.assign-courier');
        Route::post('shipments/{shipment}/transition-status', [ShipmentController::class, 'transitionStatus'])
            ->name('shipments.transition-status');
        Route::resource('shipment-trackings', ShipmentTrackingController::class)->except(['create', 'edit']);
    });

    Route::middleware('role:admin,kasir,manager')->group(function () {
        Route::resource('shipments.items', ShipmentItemController::class)->except(['create', 'edit'])->shallow();
    });

    Route::middleware('role:admin,kasir,manager,customer')->group(function () {
        Route::resource('payments', PaymentController::class)->except(['create', 'edit']);
    });

    Route::middleware('role:admin,kasir,manager')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->middleware('role:admin,manager');
        Route::get('/reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
        Route::get('/reports/summary/export', [ReportController::class, 'summaryExport'])->name('reports.summary.export');
        Route::get('/reports/branch-performance', [ReportController::class, 'branchPerformance'])->name('reports.branch-performance');
        Route::get('/reports/branch/{branch}', [ReportController::class, 'branchDetail'])->name('reports.branch-detail');
        Route::get('/reports/branch-performance/export', [ReportController::class, 'branchPerformanceExport'])->name('reports.branch-performance.export');
        Route::get('/reports/courier-performance', [ReportController::class, 'courierPerformance'])->name('reports.courier-performance');
        Route::get('/reports/payment-overview', [ReportController::class, 'paymentOverview'])->name('reports.payment-overview');
        Route::get('/reports/branch-balances', [ReportController::class, 'branchBalances'])->name('reports.branch-balances');
        Route::get('/reports/branch-balances/export', [ReportController::class, 'branchBalancesExport'])->name('reports.branch-balances.export');
        Route::get('/reports/courier-earnings', [ReportController::class, 'courierEarnings'])->name('reports.courier-earnings');
        Route::get('/reports/courier-earnings/export', [ReportController::class, 'courierEarningsExport'])->name('reports.courier-earnings.export');

        Route::get('/audit-logs/manual-corrections', [AuditLogController::class, 'manualCorrections'])->name('audit-logs.manual-corrections');
        Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);

        Route::resource('customers', CustomerController::class)->except(['create', 'edit']);

        Route::resource('branches', BranchController::class)->except(['create', 'edit']);
        Route::resource('vehicles', VehicleController::class)->except(['create', 'edit']);
        Route::resource('zones', ZoneController::class)->except(['create', 'edit']);
        Route::resource('rate-cards', RateCardController::class)->except(['create', 'edit']);
        Route::resource('shipment-statuses', ShipmentStatusController::class)->except(['create', 'edit']);
        Route::resource('landing-page-contents', LandingPageContentController::class)->except(['create', 'edit']);
    });

    Route::middleware('role:manager,admin')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['index', 'create', 'edit']);
        Route::get('/admin/trash', [AdminTrashController::class, 'index'])->name('admin.trash.index');
        Route::post('/admin/trash/{type}/{id}/restore', [AdminTrashController::class, 'restore'])->name('admin.trash.restore');
    });

    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/error-logs/dead-letter-monitoring', [ErrorLogController::class, 'deadLetterMonitoring'])->name('error-logs.dead-letter-monitoring');
        Route::get('/error-logs/unresolved-queue', [ErrorLogController::class, 'unresolvedQueue'])->name('error-logs.unresolved-queue');
        Route::resource('error-logs', ErrorLogController::class)->only(['index', 'show']);
        Route::post('/error-logs/{errorLog}/retry', [ErrorLogController::class, 'retry'])->name('error-logs.retry');
        Route::post('/error-logs/{errorLog}/resolve', [ErrorLogController::class, 'resolve'])->name('error-logs.resolve');
    });

    Route::middleware('role:customer')->group(function () {
        Route::get('/customer-portal/dashboard', [CustomerPortalController::class, 'dashboard'])->name('customer-portal.dashboard');
        Route::get('/customer-portal/shipments', [CustomerPortalController::class, 'shipments'])->name('customer-portal.shipments');
        Route::get('/customer-portal/payments', [CustomerPortalController::class, 'payments'])->name('customer-portal.payments');
        Route::get('/customer-portal/shipments/{shipment}', [CustomerPortalController::class, 'shipmentDetail'])->name('customer-portal.shipment-detail');
        Route::get('/customer-portal/payments/{payment}', [CustomerPortalController::class, 'paymentDetail'])->name('customer-portal.payment-detail');
    });
});

Route::post('/payments/midtrans/callback', MidtransWebhookController::class)
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('payments.midtrans.callback');

require __DIR__.'/auth.php';
