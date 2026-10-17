<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic HTTP bulk-SMS gateway.
 *
 * Nothing in here throws. An SMS is a courtesy: losing one must never roll back
 * an order or break an admin action.
 */
class SmsService
{
    /**
     * Send a text message.
     *
     * @return bool Whether the gateway accepted the message. Returns false when
     *              SMS is not configured (the message is logged instead).
     */
    public function send(string $phone, string $message): bool
    {
        $phone = $this->normalise($phone);

        if (blank($phone)) {
            Log::warning('SMS skipped: no phone number.', ['message' => $message]);

            return false;
        }

        if (! $this->isConfigured()) {
            // Local development path: no account needed, nothing silently lost.
            Log::info('SMS not configured — message logged instead of sent.', [
                'to' => $phone,
                'message' => $message,
            ]);

            return false;
        }

        try {
            $response = Http::timeout(config('services.sms.timeout', 10))
                ->asForm()
                ->post(config('services.sms.url'), [
                    'api_key' => config('services.sms.key'),
                    'senderid' => config('services.sms.sender_id'),
                    'number' => $phone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info('SMS sent.', ['to' => $phone]);

                return true;
            }

            Log::error('SMS gateway rejected the message.', [
                'to' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('SMS gateway request failed.', [
                'to' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isConfigured(): bool
    {
        return filled(config('services.sms.url')) && filled(config('services.sms.key'));
    }

    /**
     * Normalise a Bangladeshi mobile number to 8801XXXXXXXXX, which is what
     * local gateways expect.
     */
    private function normalise(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return match (true) {
            str_starts_with($digits, '880') => $digits,
            str_starts_with($digits, '01') => '88'.$digits,
            str_starts_with($digits, '1') && strlen($digits) === 10 => '880'.$digits,
            default => $digits,
        };
    }
}
