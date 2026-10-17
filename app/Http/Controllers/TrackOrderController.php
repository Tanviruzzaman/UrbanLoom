<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackOrderRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class TrackOrderController extends Controller
{
    public function form(): View
    {
        return view('storefront.track', ['order' => null]);
    }

    /**
     * Look up an order by number *and* phone number.
     *
     * The phone check runs in PHP rather than SQL: it is a single-row lookup,
     * and keeping it out of the query avoids database-specific string functions.
     */
    public function show(TrackOrderRequest $request): View
    {
        $order = Order::with('items')
            ->where('order_number', $request->string('order_number')->toString())
            ->first();

        $matches = $order && $this->samePhone(
            $order->customer_phone,
            $request->string('customer_phone')->toString(),
        );

        return view('storefront.track', [
            'order' => $matches ? $order : null,
            // Deliberately vague: never reveal whether the order number exists.
            'notFound' => ! $matches,
        ]);
    }

    /**
     * Compare the last 10 digits, so 01712345678, 8801712345678 and
     * +880 1712-345678 all count as the same number.
     */
    private function samePhone(?string $stored, string $given): bool
    {
        $normalise = fn (?string $value): string => substr(
            preg_replace('/\D/', '', (string) $value) ?? '',
            -10,
        );

        $a = $normalise($stored);

        return strlen($a) === 10 && hash_equals($a, $normalise($given));
    }
}
