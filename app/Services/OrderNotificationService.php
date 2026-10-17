<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Single entry point for order notifications.
 *
 * Checkout calls orderPlaced() once; nothing in here is allowed to throw,
 * because a failed email or SMS must never lose an order that is already
 * committed.
 */
class OrderNotificationService
{
    public function __construct(private SmsService $sms) {}

    /**
     * Everything that should happen when an order is placed.
     *
     * @return array{email: bool, sms: bool}
     */
    public function orderPlaced(Order $order): array
    {
        return [
            'email' => $this->sendOrderConfirmation($order),
            'sms' => $this->sendOrderReceivedSms($order),
        ];
    }

    /**
     * Send the customer their order confirmation email.
     *
     * @return bool Whether the email was actually sent.
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        if (blank($order->customer_email)) {
            Log::info('Order confirmation email skipped: no customer email.', [
                'order_number' => $order->order_number,
            ]);

            return false;
        }

        try {
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));

            Log::info('Order confirmation email sent.', [
                'order_number' => $order->order_number,
                'email' => $order->customer_email,
            ]);

            return true;
        } catch (\Throwable $e) {
            // Swallow: the order is already saved and the customer will still
            // get a phone call. Surfacing this would break checkout.
            Log::error('Order confirmation email failed.', [
                'order_number' => $order->order_number,
                'email' => $order->customer_email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendOrderReceivedSms(Order $order): bool
    {
        return $this->sms->send($order->customer_phone, $this->orderReceivedMessage($order));
    }

    public function sendOrderShippedSms(Order $order): bool
    {
        return $this->sms->send($order->customer_phone, $this->orderShippedMessage($order));
    }

    private function orderReceivedMessage(Order $order): string
    {
        return sprintf(
            '%s: Thanks for your order %s. Total %s (cash on delivery). We will call you shortly to confirm.',
            config('store.name'),
            $order->order_number,
            Money::bdt($order->total),
        );
    }

    private function orderShippedMessage(Order $order): string
    {
        $tracking = filled($order->tracking_id)
            ? sprintf(' Tracking: %s (%s).', $order->tracking_id, $order->courier ?: 'courier')
            : '';

        return sprintf(
            '%s: Your order %s has been shipped.%s Please keep %s ready for the courier.',
            config('store.name'),
            $order->order_number,
            $tracking,
            Money::bdt($order->total),
        );
    }
}
