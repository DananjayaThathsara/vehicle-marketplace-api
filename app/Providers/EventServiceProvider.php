<?php

namespace App\Providers;

use App\Events\OrderEvent;
use App\Listeners\LogOrderAnalytics;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\SendOrderNotificationToSeller;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderEvent::class => [
            SendOrderConfirmationEmail::class,
            SendOrderNotificationToSeller::class,
            LogOrderAnalytics::class,
        ],
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}
