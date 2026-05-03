<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Shipment;
use App\Policies\PaymentPolicy;
use App\Policies\ShipmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
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
        Gate::policy(\App\Models\Vehicle::class, \App\Policies\VehiclePolicy::class);
        Gate::policy(\App\Models\RateCard::class, \App\Policies\RateCardPolicy::class);
        Gate::policy(\App\Models\Branch::class, \App\Policies\BranchPolicy::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Model::shouldBeStrict(! $this->app->environment('production'));
    }
}
