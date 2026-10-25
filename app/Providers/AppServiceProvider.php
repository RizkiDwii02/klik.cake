<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\FakeGateway;
use App\Services\MidtransGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            if ($this->app->environment('testing') || empty(config('shop.midtrans.server_key'))) {
                return new FakeGateway();
            }

            return new MidtransGateway();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
