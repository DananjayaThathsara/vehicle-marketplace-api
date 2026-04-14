<?php

namespace App\Listeners;

use App\Events\OrderEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail
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
        $order = $event->order;

        Log::info('Sending order confirmation email', [
            'order_id' => $order->id,
            'buyer_email' => $order->buyer->email,
            'subject' => "Order Confirmation #{$order->order_number}"
        ]);

        // email sending
        sleep(2);

        Log::info('Order confirmation email sent', [
            'order_id' => $order->id
        ]);
    }
}
