<?php

use App\Http\Controllers\MidtransPaymentController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\LandingPageContentController;
use App\Http\Controllers\DashboardDataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentItemController;
use App\Http\Controllers\ShipmentStatusController;
use App\Http\Controllers\ShipmentTrackingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\OperationalProofController;
use App\Http\Controllers\RateCardController;
use App\Http\Controllers\TrackingLookupController;
use App\Http\Controllers\AdminTrashController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\CourierPortalController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicLandingController::class)->name('landing');
Route::get('/api/track', [PublicLandingController::class, 'track'])->name('api.track');
Route::get('/api/quote', [PublicLandingController::class, 'quote'])->name('api.quote');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'role:admin,kasir,manager'])
    ->name('dashboard');

Route::get('/tracking/{trackingNumber}', [TrackingLookupController::class, 'show'])
    ->middleware('throttle:20,1')
    ->name('tracking.lookup');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/data', DashboardDataController::class)
        ->middleware('role:admin,kasir,manager')
        ->name('dashboard.data');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/payments/{shipment}/midtrans/snap-token', [MidtransPaymentController::class, 'createSnapToken'])
        ->middleware('role:kasir,manager,customer')
        ->name('payments.midtrans.snap-token');

    Route::post('/profile/otp/send-email', [\App\Http\Controllers\ProfileOtpController::class, 'sendEmailOtp'])->name('profile.otp.email');
    Route::post('/profile/otp/send-password', [\App\Http\Controllers\ProfileOtpController::class, 'sendPasswordOtp'])->name('profile.otp.password');
    Route::post('/profile/otp/verify', [\App\Http\Controllers\ProfileOtpController::class, 'verifyOtp'])->name('profile.otp.verify');

    Route::middleware('role:admin,kasir,manager,courier')->group(function () {
        Route::resource('shipments', ShipmentController::class)->except(['create', 'edit']);
        Route::post('shipments/{shipment}/assign-courier', [ShipmentController::class, 'assignCourier'])
            ->name('shipments.assign-courier');
        Route::post('shipments/{shipment}/transition-status', [ShipmentController::class, 'transitionStatus'])
            ->name('shipments.transition-status');
        Route::resource('shipment-trackings', ShipmentTrackingController::class)->except(['create', 'edit']);
    });

    Route::get('shipments/{shipment}/label', [ShipmentController::class, 'label'])
        ->middleware('role:admin,kasir,manager,courier,customer')
        ->name('shipments.label');

    Route::middleware('role:admin,kasir,manager')->group(function () {
        Route::post('shipments/{shipment}/pricing-override/request', [ShipmentController::class, 'requestPricingOverride'])
            ->name('shipments.pricing-override.request');
        Route::resource('shipments.items', ShipmentItemController::class)->except(['create', 'edit'])->shallow();
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

        Route::get('/audit-logs/manual-corrections', [AuditLogController::class, 'manualCorrections'])->name('audit-logs.manual-corrections');
        Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);

        Route::resource('customers', CustomerController::class)->except(['create', 'edit']);

        Route::resource('branches', BranchController::class)->except(['create', 'edit']);
        Route::resource('vehicles', VehicleController::class)->except(['create', 'edit']);
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
        Route::get('/notifications/admin', [NotificationController::class, 'adminIndex'])->name('admin.notifications.index');
        Route::get('/notifications/push-ready', [NotificationController::class, 'pushReady'])->name('admin.notifications.push-ready');
        Route::get('/operational-proofs/investigation', [OperationalProofController::class, 'adminInvestigationIndex'])->name('operational-proofs.investigation.index');
        Route::get('/pricing-approvals/shipments/inbox', [ShipmentController::class, 'pricingApprovalInbox'])->name('shipments.pricing-approval-inbox');
        Route::post('shipments/{shipment}/pricing-override/approve', [ShipmentController::class, 'approvePricingOverride'])
            ->name('shipments.pricing-override.approve');
        Route::post('shipments/{shipment}/pricing-override/reject', [ShipmentController::class, 'rejectPricingOverride'])
            ->name('shipments.pricing-override.reject');
        Route::get('/error-logs/dead-letter-monitoring', [ErrorLogController::class, 'deadLetterMonitoring'])->name('error-logs.dead-letter-monitoring');
        Route::get('/error-logs/manual-dead-letter-queue', [ErrorLogController::class, 'manualDeadLetterQueue'])->name('error-logs.manual-dead-letter-queue');
        Route::get('/error-logs/unresolved-queue', [ErrorLogController::class, 'unresolvedQueue'])->name('error-logs.unresolved-queue');
        Route::resource('error-logs', ErrorLogController::class)->only(['index', 'show']);
        Route::post('/error-logs/{errorLog}/escalate-manual', [ErrorLogController::class, 'escalateToManual'])->name('error-logs.escalate-manual');
        Route::post('/error-logs/{errorLog}/retry', [ErrorLogController::class, 'retry'])->name('error-logs.retry');
        Route::post('/error-logs/{errorLog}/resolve', [ErrorLogController::class, 'resolve'])->name('error-logs.resolve');
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/tasks/{task}/approve', [ApprovalController::class, 'approveTask'])->name('approvals.tasks.approve');
        Route::post('/approvals/tasks/{task}/reject', [ApprovalController::class, 'rejectTask'])->name('approvals.tasks.reject');
        Route::get('/approvals/rate-cards', [ApprovalController::class, 'rateCardIndex'])->name('approvals.rate-cards.index');
        Route::post('/approvals/rate-cards/{rateCardApproval}/approve', [ApprovalController::class, 'approveRateCard'])->name('approvals.rate-cards.approve');
        Route::post('/approvals/rate-cards/{rateCardApproval}/reject', [ApprovalController::class, 'rejectRateCard'])->name('approvals.rate-cards.reject');
    });
});

Route::post('/payments/midtrans/callback', MidtransWebhookController::class)
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('payments.midtrans.callback');

Route::get('/payments/midtrans/finish', [MidtransPaymentController::class, 'finish'])->name('payments.midtrans.finish');
Route::get('/payments/midtrans/unfinish', [MidtransPaymentController::class, 'unfinish'])->name('payments.midtrans.unfinish');
Route::get('/payments/midtrans/error', [MidtransPaymentController::class, 'error'])->name('payments.midtrans.error');

    // Mobile Portals (Laravel Web UI instead of Flutter)
    Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [CustomerPortalController::class, 'index'])->name('dashboard');
        Route::get('/shipments', [CustomerPortalController::class, 'list'])->name('shipments.index');
        Route::get('/shipments/search', [CustomerPortalController::class, 'search'])->name('shipments.search');
        Route::get('/shipments/{shipment}/track', [CustomerPortalController::class, 'track'])->name('shipments.track');
        Route::get('/shipments/create', [CustomerPortalController::class, 'create'])->name('shipments.create');
        Route::post('/shipments', [CustomerPortalController::class, 'store'])->name('shipments.store');
        Route::get('/shipments/quote', [CustomerPortalController::class, 'quote'])->name('shipments.quote');
        Route::get('/shipments/rates', [CustomerPortalController::class, 'rates'])->name('shipments.rates');
        Route::get('/notifications', [NotificationController::class, 'customerIndex'])->name('notifications.index');
        Route::get('/notifications/push-ready', [NotificationController::class, 'pushReady'])->name('notifications.push-ready');
    });

    Route::middleware(['auth', 'role:courier'])->prefix('courier')->name('courier.')->group(function () {
        Route::get('/tasks', [CourierPortalController::class, 'tasks'])->name('tasks');
        Route::get('/shipments', [CourierPortalController::class, 'index'])->name('shipments.index');
        Route::get('/shipments/bulk-edit', [CourierPortalController::class, 'bulkEdit'])->name('shipments.bulk-edit');
        Route::post('/shipments/bulk-update', [CourierPortalController::class, 'bulkUpdate'])->name('shipments.bulk-update');
        Route::post('/shipments/claim', [CourierPortalController::class, 'claim'])->name('shipments.claim');
        Route::get('/shipments/{shipment}/update', [CourierPortalController::class, 'edit'])->name('shipments.edit');
        Route::post('/shipments/{shipment}/update', [CourierPortalController::class, 'update'])->name('shipments.update');
    });

require __DIR__.'/auth.php';
