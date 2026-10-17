<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStockTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $product = Product::create(['name' => 'Test Tee', 'price' => 560, 'is_active' => true]);
        $this->variant = $product->variants()->create(['size' => 'M', 'stock_quantity' => 10]);
    }

    private function order(int $quantity = 2, string $status = 'pending'): Order
    {
        $order = Order::create([
            'customer_name' => 'Rahim Uddin',
            'customer_phone' => '01712345678',
            'shipping_address' => 'House 42',
            'city' => 'Dhaka',
            'subtotal' => 560 * $quantity,
            'delivery_charge' => 60,
            'total' => 560 * $quantity + 60,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => $status,
        ]);

        $order->items()->create([
            'product_id' => $this->variant->product_id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Test Tee',
            'variant_label' => 'M',
            'price' => 560,
            'quantity' => $quantity,
            'line_total' => 560 * $quantity,
        ]);

        // Checkout takes the stock at the moment the order is placed.
        $this->variant->decrement('stock_quantity', $quantity);

        return $order;
    }

    public function test_cancelling_an_order_puts_the_stock_back(): void
    {
        $order = $this->order(2);
        $this->assertSame(8, $this->variant->refresh()->stock_quantity);

        $order->update(['status' => 'cancelled']);

        $this->assertSame(10, $this->variant->refresh()->stock_quantity);
    }

    public function test_un_cancelling_takes_the_stock_out_again(): void
    {
        $order = $this->order(2);
        $order->update(['status' => 'cancelled']);
        $this->assertSame(10, $this->variant->refresh()->stock_quantity);

        $order->update(['status' => 'confirmed']);

        $this->assertSame(8, $this->variant->refresh()->stock_quantity);
    }

    public function test_stock_is_not_returned_twice(): void
    {
        $order = $this->order(2);
        $order->update(['status' => 'cancelled']);
        // Saving again without a status change must not move stock.
        $order->update(['customer_name' => 'Someone Else']);
        $order->update(['status' => 'cancelled']);

        $this->assertSame(10, $this->variant->refresh()->stock_quantity);
    }

    public function test_ordinary_status_changes_leave_stock_alone(): void
    {
        $order = $this->order(2);

        $order->update(['status' => 'confirmed']);
        $order->update(['status' => 'shipped']);
        $order->update(['status' => 'delivered']);

        $this->assertSame(8, $this->variant->refresh()->stock_quantity);
    }

    public function test_stock_never_goes_below_zero(): void
    {
        $order = $this->order(2);
        $order->update(['status' => 'cancelled']);

        // Someone empties the shelf by hand, then un-cancels the order.
        $this->variant->update(['stock_quantity' => 1]);
        $order->update(['status' => 'confirmed']);

        $this->assertSame(0, $this->variant->refresh()->stock_quantity);
    }

    public function test_an_order_whose_variant_was_deleted_does_not_error(): void
    {
        $order = $this->order(2);
        $this->variant->delete();

        $order->update(['status' => 'cancelled']);

        $this->assertSame('cancelled', $order->refresh()->status);
    }

    public function test_marking_an_order_returned_puts_the_stock_back(): void
    {
        $order = $this->order(2);
        $this->assertSame(8, $this->variant->refresh()->stock_quantity);

        $order->update(['status' => 'returned']);

        $this->assertSame(10, $this->variant->refresh()->stock_quantity);
    }

    public function test_moving_between_cancelled_and_returned_does_not_double_count(): void
    {
        $order = $this->order(2);

        $order->update(['status' => 'cancelled']);
        $this->assertSame(10, $this->variant->refresh()->stock_quantity);

        // Both mean the goods are back; switching between them must not add more.
        $order->update(['status' => 'returned']);
        $this->assertSame(10, $this->variant->refresh()->stock_quantity);
    }

    public function test_reopening_a_returned_order_takes_the_stock_again(): void
    {
        $order = $this->order(2);
        $order->update(['status' => 'returned']);

        $order->update(['status' => 'shipped']);

        $this->assertSame(8, $this->variant->refresh()->stock_quantity);
    }
}
