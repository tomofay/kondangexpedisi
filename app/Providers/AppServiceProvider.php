<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Shipment;
use App\Policies\PaymentPolicy;
use App\Policies\ShipmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
    }
}
