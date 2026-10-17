<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\OrderNotificationService;
use App\Services\SmsService;
use App\Services\SteadfastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attributes = []): Order
    {
        return Order::create([
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
    }

    /*
    |--------------------------------------------------------------------------
    | SMS
    |--------------------------------------------------------------------------
    */

    public function test_sms_is_not_sent_when_the_gateway_is_unconfigured(): void
    {
        config(['services.sms.url' => '', 'services.sms.key' => '']);
        Http::fake();

        $sent = app(SmsService::class)->send('01712345678', 'Hello');

        $this->assertFalse($sent);
        Http::assertNothingSent();
    }

    public function test_sms_posts_to_the_gateway_when_configured(): void
    {
        config([
            'services.sms.url' => 'https://sms.example.com/send',
            'services.sms.key' => 'test-key',
            'services.sms.sender_id' => 'MyStore',
        ]);
        Http::fake(['sms.example.com/*' => Http::response(['status' => 'ok'], 200)]);

        $sent = app(SmsService::class)->send('01712345678', 'Hello');

        $this->assertTrue($sent);
        Http::assertSent(fn ($request) => $request->url() === 'https://sms.example.com/send'
            // Normalised to the international form the gateway expects.
            && $request['number'] === '8801712345678'
            && $request['message'] === 'Hello');
    }

    public function test_sms_returns_false_rather_than_throwing_when_the_gateway_errors(): void
    {
        config(['services.sms.url' => 'https://sms.example.com/send', 'services.sms.key' => 'k']);
        Http::fake(['sms.example.com/*' => Http::response('server exploded', 500)]);

        $this->assertFalse(app(SmsService::class)->send('01712345678', 'Hello'));
    }

    public function test_sms_is_skipped_for_a_blank_phone_number(): void
    {
        config(['services.sms.url' => 'https://sms.example.com/send', 'services.sms.key' => 'k']);
        Http::fake();

        $this->assertFalse(app(SmsService::class)->send('', 'Hello'));
        Http::assertNothingSent();
    }

    public function test_order_placed_sends_the_received_sms(): void
    {
        config(['services.sms.url' => 'https://sms.example.com/send', 'services.sms.key' => 'k']);
        Http::fake(['sms.example.com/*' => Http::response(['ok' => true], 200)]);

        app(OrderNotificationService::class)->sendOrderReceivedSms($this->order());

        Http::assertSent(fn ($request) => str_contains($request['message'], 'Thanks for your order')
            && str_contains($request['message'], '৳560'));
    }

    public function test_shipped_sms_fires_when_status_changes_to_shipped(): void
    {
        config(['services.sms.url' => 'https://sms.example.com/send', 'services.sms.key' => 'k']);
        Http::fake(['sms.example.com/*' => Http::response(['ok' => true], 200)]);

        $order = $this->order();
        Http::fake(['sms.example.com/*' => Http::response(['ok' => true], 200)]);

        $order->update(['status' => 'shipped', 'tracking_id' => 'CN123', 'courier' => 'steadfast']);

        Http::assertSent(fn ($request) => str_contains($request['message'], 'has been shipped')
            && str_contains($request['message'], 'CN123'));
    }

    public function test_no_shipped_sms_for_other_status_changes(): void
    {
        config(['services.sms.url' => 'https://sms.example.com/send', 'services.sms.key' => 'k']);
        $order = $this->order();

        Http::fake();
        $order->update(['status' => 'confirmed']);

        Http::assertNothingSent();
    }

    /*
    |--------------------------------------------------------------------------
    | Steadfast
    |--------------------------------------------------------------------------
    */

    public function test_courier_reports_when_not_configured(): void
    {
        config(['services.steadfast.api_key' => '', 'services.steadfast.secret' => '']);
        Http::fake();

        $result = app(SteadfastService::class)->createOrder($this->order());

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not configured', $result['message']);
        Http::assertNothingSent();
    }

    private function configureSteadfast(): void
    {
        config([
            'services.steadfast.base_url' => 'https://courier.example.com/api/v1',
            'services.steadfast.api_key' => 'key',
            'services.steadfast.secret' => 'secret',
        ]);
    }

    public function test_courier_books_a_parcel_and_records_it_on_the_order(): void
    {
        $this->configureSteadfast();
        Http::fake(['courier.example.com/*' => Http::response([
            'status' => 200,
            'consignment' => ['consignment_id' => 987654, 'tracking_code' => 'TRK-987'],
        ], 200)]);

        $order = $this->order();
        $result = app(SteadfastService::class)->bookAndRecord($order);

        $this->assertTrue($result['success']);
        $this->assertSame('987654', $result['consignment_id']);

        $order->refresh();
        $this->assertSame('steadfast', $order->courier);
        $this->assertSame('TRK-987', $order->tracking_id);
        $this->assertSame('shipped', $order->status);
    }

    public function test_courier_sends_the_order_total_as_cod_amount(): void
    {
        $this->configureSteadfast();
        Http::fake(['courier.example.com/*' => Http::response([
            'consignment' => ['consignment_id' => 1],
        ], 200)]);

        $order = $this->order(['total' => 780]);
        app(SteadfastService::class)->createOrder($order);

        Http::assertSent(fn ($request) => $request['cod_amount'] === 780.0
            && $request['invoice'] === $order->order_number
            && $request['recipient_phone'] === '01712345678'
            && str_contains($request['recipient_address'], 'Dhaka'));
    }

    public function test_courier_sends_zero_cod_for_an_already_paid_order(): void
    {
        $this->configureSteadfast();
        Http::fake(['courier.example.com/*' => Http::response([
            'consignment' => ['consignment_id' => 1],
        ], 200)]);

        app(SteadfastService::class)->createOrder($this->order(['payment_status' => 'paid']));

        Http::assertSent(fn ($request) => $request['cod_amount'] === 0);
    }

    public function test_courier_refuses_to_double_book_an_order(): void
    {
        $this->configureSteadfast();
        Http::fake();

        $result = app(SteadfastService::class)->createOrder($this->order(['tracking_id' => 'CN-EXISTING']));

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('already has tracking ID', $result['message']);
        Http::assertNothingSent();
    }

    public function test_courier_failure_leaves_the_order_untouched(): void
    {
        $this->configureSteadfast();
        Http::fake(['courier.example.com/*' => Http::response(['message' => 'Invalid address'], 422)]);

        $order = $this->order();
        $result = app(SteadfastService::class)->bookAndRecord($order);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('422', $result['message']);

        $order->refresh();
        $this->assertNull($order->tracking_id);
        $this->assertSame('pending', $order->status);
    }

    public function test_courier_handles_a_response_with_no_consignment_id(): void
    {
        $this->configureSteadfast();
        Http::fake(['courier.example.com/*' => Http::response(['status' => 200], 200)]);

        $result = app(SteadfastService::class)->createOrder($this->order());

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('no consignment ID', $result['message']);
    }

    public function test_courier_network_failure_does_not_throw(): void
    {
        $this->configureSteadfast();
        Http::fake(fn () => throw new \RuntimeException('connection refused'));

        $result = app(SteadfastService::class)->createOrder($this->order());

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Could not reach Steadfast', $result['message']);
    }
}
