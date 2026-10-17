<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\OrderNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /** Statuses that mean the goods are back on your shelf. */
    private const RETURNS_STOCK = ['cancelled', 'returned'];

    public function __construct(private OrderNotificationService $notifier) {}

    /**
     * Fires on any saved change to an order.
     *
     * Note: Eloquent events do not fire for mass updates, so any bulk action
     * that should notify or move stock must save records individually.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $previous = $order->getOriginal('status');
        $current = $order->status;

        $wasReturned = in_array($previous, self::RETURNS_STOCK, true);
        $isReturned = in_array($current, self::RETURNS_STOCK, true);

        // Cancelling puts the goods back; un-cancelling takes them out again.
        // Doing both keeps stock correct however often the status is changed.
        if ($isReturned && ! $wasReturned) {
            $this->moveStock($order, 'increment');
        } elseif ($wasReturned && ! $isReturned) {
            $this->moveStock($order, 'decrement');
        }

        if ($current === 'shipped') {
            $this->notifier->sendOrderShippedSms($order);
        }
    }

    /**
     * Put an order's quantities back onto, or take them off, its variants.
     */
    private function moveStock(Order $order, string $direction): void
    {
        $items = $order->items()->whereNotNull('product_variant_id')->get();

        if ($items->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($items, $direction) {
            foreach ($items as $item) {
                $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

                if (! $variant) {
                    continue;
                }

                // Stock is unsigned, so never take it below zero.
                $direction === 'increment'
                    ? $variant->increment('stock_quantity', $item->quantity)
                    : $variant->decrement('stock_quantity', min($item->quantity, $variant->stock_quantity));
            }
        });

        Log::info('Order stock '.$direction.'ed.', [
            'order_number' => $order->order_number,
            'items' => $items->count(),
        ]);
    }
}
