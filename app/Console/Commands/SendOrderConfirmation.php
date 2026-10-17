<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderNotificationService;
use Illuminate\Console\Command;

class SendOrderConfirmation extends Command
{
    protected $signature = 'order:send-confirmation
                            {order? : Order number (defaults to the most recent order)}';

    protected $description = 'Send (or resend) the confirmation email for an order';

    public function handle(OrderNotificationService $notifier): int
    {
        $order = $this->argument('order')
            ? Order::where('order_number', $this->argument('order'))->first()
            : Order::latest('id')->first();

        if (! $order) {
            $this->error('No order found. Create one with: php artisan db:seed --class=DemoOrderSeeder');

            return self::FAILURE;
        }

        if (blank($order->customer_email)) {
            $this->warn("Order {$order->order_number} has no customer email — nothing to send.");

            return self::SUCCESS;
        }

        $sent = $notifier->sendOrderConfirmation($order);

        $sent
            ? $this->info("Confirmation for {$order->order_number} sent to {$order->customer_email}.")
            : $this->error("Sending failed for {$order->order_number}. Check storage/logs/laravel.log.");

        return $sent ? self::SUCCESS : self::FAILURE;
    }
}
