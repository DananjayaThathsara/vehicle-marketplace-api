<?php

namespace App\Listeners;

use App\Events\OrderEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderNotificationToSeller
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderEvent $event): void
    {
        Log::info('Notifying seller of new sale', [
            'order_id' => $event->order->id,
            'seller_id' => $event->order->seller_id
        ]);

        sleep(1);

        Log::info('Seller notified');
    }
}
