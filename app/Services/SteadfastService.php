<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Steadfast merchant API — books a parcel for an order.
 *
 * Every failure mode returns a result array rather than throwing, so the
 * Filament action can always show the operator a clear message.
 */
class SteadfastService
{
    public function isConfigured(): bool
    {
        return filled(config('services.steadfast.api_key'))
            && filled(config('services.steadfast.secret'));
    }

    /**
     * Create a parcel for an order.
     *
     * @return array{success: bool, message: string, consignment_id: ?string, tracking_code: ?string}
     */
    public function createOrder(Order $order): array
    {
        if (! $this->isConfigured()) {
            return $this->failure(
                'Steadfast is not configured. Add STEADFAST_API_KEY and STEADFAST_SECRET to your .env file.'
            );
        }

        if (filled($order->tracking_id)) {
            return $this->failure(
                "Order {$order->order_number} already has tracking ID {$order->tracking_id}."
            );
        }

        try {
            $response = Http::timeout(config('services.steadfast.timeout', 15))
                ->withHeaders([
                    'Api-Key' => config('services.steadfast.api_key'),
                    'Secret-Key' => config('services.steadfast.secret'),
                    'Content-Type' => 'application/json',
                ])
                ->post(rtrim(config('services.steadfast.base_url'), '/').'/create_order', [
                    'invoice' => $order->order_number,
                    'recipient_name' => $order->customer_name,
                    'recipient_phone' => $this->normalisePhone($order->customer_phone),
                    'recipient_address' => trim($order->shipping_address.', '.$order->city),
                    // COD amount is zero once the customer has already paid.
                    'cod_amount' => $order->payment_status === 'paid' ? 0 : (float) $order->total,
                    'note' => (string) $order->notes,
                ]);
        } catch (\Throwable $e) {
            Log::error('Steadfast request failed.', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return $this->failure('Could not reach Steadfast: '.$e->getMessage());
        }

        if (! $response->successful()) {
            Log::error('Steadfast rejected the booking.', [
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->failure(
                'Steadfast returned HTTP '.$response->status().'. '.$this->extractMessage($response->json())
            );
        }

        $body = $response->json() ?? [];
        $consignment = $body['consignment'] ?? [];
        $consignmentId = $consignment['consignment_id'] ?? null;

        if (blank($consignmentId)) {
            Log::error('Steadfast response had no consignment id.', [
                'order_number' => $order->order_number,
                'body' => $body,
            ]);

            return $this->failure('Steadfast accepted the request but returned no consignment ID.');
        }

        Log::info('Steadfast parcel booked.', [
            'order_number' => $order->order_number,
            'consignment_id' => $consignmentId,
        ]);

        return [
            'success' => true,
            'message' => 'Parcel booked with Steadfast.',
            'consignment_id' => (string) $consignmentId,
            'tracking_code' => isset($consignment['tracking_code']) ? (string) $consignment['tracking_code'] : null,
        ];
    }

    /**
     * Book the parcel and, on success, record it on the order.
     *
     * The status change is a normal save so the OrderObserver fires the
     * "shipped" SMS.
     *
     * @return array{success: bool, message: string, consignment_id: ?string, tracking_code: ?string}
     */
    public function bookAndRecord(Order $order): array
    {
        $result = $this->createOrder($order);

        if ($result['success']) {
            $order->update([
                'courier' => 'steadfast',
                'tracking_id' => $result['tracking_code'] ?: $result['consignment_id'],
                'status' => 'shipped',
            ]);
        }

        return $result;
    }

    /**
     * @return array{success: bool, message: string, consignment_id: null, tracking_code: null}
     */
    private function failure(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'consignment_id' => null,
            'tracking_code' => null,
        ];
    }

    private function extractMessage(mixed $body): string
    {
        if (! is_array($body)) {
            return '';
        }

        if (isset($body['errors']) && is_array($body['errors'])) {
            return collect($body['errors'])->flatten()->implode(' ');
        }

        return (string) ($body['message'] ?? '');
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        // Steadfast expects the local 11-digit form, e.g. 01712345678.
        return str_starts_with($digits, '880') ? substr($digits, 2) : $digits;
    }
}
