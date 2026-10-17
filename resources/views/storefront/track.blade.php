<x-app-layout title="Track your order"
              description="Check the status of your order with your order number and mobile number.">

    @php
        // The journey every order takes. `cancelled` and `returned` are handled
        // separately — neither is a step forward.
        $steps = [
            'pending'   => ['Order received', 'We have your order and will call to confirm it.'],
            'confirmed' => ['Confirmed', 'Confirmed by phone. We are packing it now.'],
            'shipped'   => ['Shipped', 'Handed to the courier and on its way to you.'],
            'delivered' => ['Delivered', 'Delivered. Thank you for shopping with us.'],
        ];
        $order = $order ?? null;
        $closedStatuses = ['cancelled', 'returned'];
        $isClosed = $order && in_array($order->status, $closedStatuses, true);
        $currentIndex = $order && ! $isClosed
            ? array_search($order->status, array_keys($steps), true)
            : null;
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">

        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Track your order</h1>
        <p class="mt-3 max-w-xl text-[15px] leading-relaxed text-neutral-600">
            Enter your order number and the mobile number you ordered with. Your order number is in
            your confirmation email and looks like <span class="font-mono text-sm">ORD-20260914-A1B2C</span>.
        </p>

        {{-- Lookup form --}}
        <form method="POST" action="{{ route('track.show') }}" class="mt-8 rounded-xl border border-neutral-200 bg-neutral-50 p-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="order_number" class="block text-sm font-medium text-neutral-900">
                        Order number <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="order_number" id="order_number"
                           value="{{ old('order_number') }}" required
                           placeholder="ORD-20260914-A1B2C"
                           @class([
                               'mt-2 block w-full rounded-lg border bg-white px-4 py-3 font-mono text-sm focus:outline-none focus:ring-1',
                               'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('order_number'),
                               'border-neutral-300 focus:border-brand-600 focus:ring-brand-600' => ! $errors->has('order_number'),
                           ])>
                    @error('order_number')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-neutral-900">
                        Mobile number <span class="text-red-600">*</span>
                    </label>
                    <input type="tel" name="customer_phone" id="customer_phone"
                           value="{{ old('customer_phone') }}" required inputmode="tel"
                           placeholder="01712345678"
                           @class([
                               'mt-2 block w-full rounded-lg border bg-white px-4 py-3 text-sm focus:outline-none focus:ring-1',
                               'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('customer_phone'),
                               'border-neutral-300 focus:border-brand-600 focus:ring-brand-600' => ! $errors->has('customer_phone'),
                           ])>
                    @error('customer_phone')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <button type="submit"
                    class="mt-5 rounded-full bg-brand-700 px-8 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                Find my order
            </button>
        </form>

        {{-- Not found --}}
        @if (! empty($notFound))
            <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
                <p class="text-sm font-medium text-amber-900">We couldn't find that order</p>
                <p class="mt-1.5 text-sm leading-relaxed text-amber-800">
                    Check the order number and make sure the mobile number is the one you ordered
                    with. Still stuck? Call us on
                    <a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}"
                       class="font-medium underline underline-offset-2">{{ config('store.phone') }}</a>.
                </p>
            </div>
        @endif

        {{-- Result --}}
        @if ($order)
            <div class="mt-10">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-neutral-500">Order</p>
                        <p class="mt-1 font-mono text-xl font-semibold">{{ $order->order_number }}</p>
                    </div>
                    <p class="text-sm text-neutral-600">Placed {{ $order->created_at->format('j M Y') }}</p>
                </div>

                @if ($isClosed)
                    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
                        <p class="text-sm font-medium text-red-900">
                            {{ $order->status === 'returned' ? 'This order was returned to us' : 'This order was cancelled' }}
                        </p>
                        <p class="mt-1.5 text-sm text-red-800">
                            Nothing is owed. If you think this is a mistake, call us on
                            {{ config('store.phone') }}.
                        </p>
                    </div>
                @else
                    {{-- Progress --}}
                    <ol class="mt-8 space-y-0">
                        @foreach ($steps as $key => [$label, $blurb])
                            @php
                                $index = array_search($key, array_keys($steps), true);
                                $done = $currentIndex !== false && $currentIndex !== null && $index <= $currentIndex;
                                $isCurrent = $index === $currentIndex;
                            @endphp
                            <li class="flex gap-4">
                                {{-- Rail --}}
                                <div class="flex flex-col items-center">
                                    <span @class([
                                        'flex size-8 shrink-0 items-center justify-center rounded-full border-2 text-xs font-semibold',
                                        'border-brand-700 bg-brand-700 text-white' => $done,
                                        'border-neutral-300 bg-white text-neutral-400' => ! $done,
                                    ])>
                                        @if ($done)
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </span>
                                    @if (! $loop->last)
                                        <span @class([
                                            'w-0.5 flex-1 min-h-10',
                                            'bg-brand-700' => $done && ! $isCurrent,
                                            'bg-neutral-200' => ! $done || $isCurrent,
                                        ])></span>
                                    @endif
                                </div>

                                <div class="pb-8">
                                    <p @class([
                                        'text-sm font-semibold',
                                        'text-neutral-900' => $done,
                                        'text-neutral-400' => ! $done,
                                    ])>{{ $label }}</p>
                                    <p class="mt-0.5 text-sm text-neutral-600">{{ $blurb }}</p>

                                    @if ($isCurrent && $order->status === 'shipped' && $order->tracking_id)
                                        <p class="mt-2 text-sm">
                                            <span class="text-neutral-500">Tracking:</span>
                                            <span class="font-mono font-medium">{{ $order->tracking_id }}</span>
                                            @if ($order->courier)
                                                <span class="text-neutral-500">via {{ ucfirst($order->courier) }}</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif

                {{-- Items --}}
                <div class="mt-4 rounded-xl border border-neutral-200">
                    <div class="border-b border-neutral-200 px-5 py-3">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-900">Your items</h2>
                    </div>
                    <ul class="divide-y divide-neutral-200">
                        @foreach ($order->items as $item)
                            <li class="flex items-start justify-between gap-4 px-5 py-3.5">
                                <div>
                                    <p class="text-sm font-medium">{{ $item->product_name }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-500">
                                        @if ($item->variant_label){{ $item->variant_label }} · @endif
                                        {{ bdt($item->price) }} × {{ $item->quantity }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-medium">{{ bdt($item->line_total) }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <dl class="space-y-2 border-t border-neutral-200 px-5 py-4 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-neutral-600">Subtotal</dt><dd>{{ bdt($order->subtotal) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-neutral-600">Delivery</dt><dd>{{ bdt($order->delivery_charge) }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-neutral-200 pt-2 text-base font-semibold">
                            <dt>{{ $order->payment_status === 'paid' ? 'Total paid' : 'Total to pay on delivery' }}</dt>
                            <dd>{{ bdt($order->total) }}</dd>
                        </div>
                    </dl>
                </div>

                <p class="mt-5 text-sm text-neutral-500">
                    Questions about this order? Call {{ config('store.phone') }} and quote
                    {{ $order->order_number }}.
                </p>
            </div>
        @endif
    </div>

</x-app-layout>
