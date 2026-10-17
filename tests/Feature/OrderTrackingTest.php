<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attributes = []): Order
    {
        $order = Order::create([
            'customer_name' => 'Rahim Uddin',
            'customer_phone' => '01712345678',
            'shipping_address' => 'House 42, Banani',
            'city' => 'Dhaka',
            'subtotal' => 500,
            'delivery_charge' => 60,
            'total' => 560,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            ...$attributes,
        ]);

        $order->items()->create([
            'product_name' => 'Solid Cotton Tee',
            'variant_label' => 'M / Black',
            'price' => 500,
            'quantity' => 1,
            'line_total' => 500,
        ]);

        return $order;
    }

    public function test_the_tracking_form_loads(): void
    {
        $this->get('/track')->assertSuccessful()->assertSee('Track your order');
    }

    public function test_a_customer_finds_their_order(): void
    {
        $order = $this->order();

        $this->post('/track', [
            'order_number' => $order->order_number,
            'customer_phone' => '01712345678',
        ])
            ->assertSuccessful()
            ->assertSee($order->order_number)
            ->assertSee('Solid Cotton Tee')
            ->assertSee('Order received');
    }

    public function test_the_phone_number_must_match(): void
    {
        $order = $this->order();

        // Right order number, wrong phone — must reveal nothing.
        $this->post('/track', [
            'order_number' => $order->order_number,
            'customer_phone' => '01999999999',
        ])
            ->assertSuccessful()
            ->assertSee('find that order')
            ->assertDontSee('House 42, Banani')
            ->assertDontSee('Rahim Uddin');
    }

    public function test_an_unknown_order_number_reveals_nothing(): void
    {
        $this->order();

        $this->post('/track', [
            'order_number' => 'ORD-20990101-ZZZZZ',
            'customer_phone' => '01712345678',
        ])
            ->assertSuccessful()
            ->assertSee('find that order');
    }

    public function test_phone_number_formats_all_work(): void
    {
        $order = $this->order();

        foreach (['01712345678', '8801712345678', '+8801712345678', '017 1234 5678'] as $format) {
            $this->post('/track', [
                'order_number' => $order->order_number,
                'customer_phone' => $format,
            ])->assertSee($order->order_number);
        }
    }

    public function test_both_fields_are_required(): void
    {
        $this->post('/track', ['order_number' => 'ORD-1'])
            ->assertSessionHasErrors('customer_phone');

        $this->post('/track', ['customer_phone' => '01712345678'])
            ->assertSessionHasErrors('order_number');
    }

    public function test_a_cancelled_order_says_so(): void
    {
        $order = $this->order(['status' => 'cancelled']);

        $this->post('/track', [
            'order_number' => $order->order_number,
            'customer_phone' => '01712345678',
        ])->assertSee('This order was cancelled');
    }

    public function test_a_shipped_order_shows_its_tracking_number(): void
    {
        $order = $this->order(['status' => 'shipped', 'tracking_id' => 'CN-99887', 'courier' => 'steadfast']);

        $this->post('/track', [
            'order_number' => $order->order_number,
            'customer_phone' => '01712345678',
        ])
            ->assertSee('CN-99887')
            ->assertSee('Shipped');
    }

    /*
    |--------------------------------------------------------------------------
    | Invoices — staff only
    |--------------------------------------------------------------------------
    */

    public function test_invoices_are_not_public(): void
    {
        $order = $this->order();

        $this->get("/invoices/{$order->order_number}")->assertRedirect();
    }

    public function test_staff_can_print_an_invoice(): void
    {
        $order = $this->order();

        $this->actingAs(User::factory()->create())
            ->get("/invoices/{$order->order_number}")
            ->assertSuccessful()
            ->assertSee($order->order_number)
            ->assertSee('Solid Cotton Tee')
            ->assertSee('Collect from customer')
            ->assertSee('৳560');
    }

    public function test_a_paid_invoice_says_collect_nothing(): void
    {
        $order = $this->order(['payment_status' => 'paid']);

        $this->actingAs(User::factory()->create())
            ->get("/invoices/{$order->order_number}")
            ->assertSee('Already paid — collect nothing');
    }

    public function test_staff_can_print_several_invoices_at_once(): void
    {
        $a = $this->order(['customer_name' => 'Customer A']);
        $b = $this->order(['customer_name' => 'Customer B']);

        $this->actingAs(User::factory()->create())
            ->get('/invoices/batch?ids='.$a->id.','.$b->id)
            ->assertSuccessful()
            ->assertSee('Customer A')
            ->assertSee('Customer B')
            ->assertSee('2 invoices ready');
    }
}
