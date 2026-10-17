<x-app-layout title="Order confirmed">

    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">

        {{-- Confirmation --}}
        <div class="text-center">
            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-green-100">
                <svg class="size-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>

            <h1 class="mt-6 text-3xl font-semibold tracking-tight sm:text-4xl">Thank you for your order!</h1>
            <p class="mt-3 text-[15px] leading-relaxed text-neutral-600">
                We've received your order and will call you shortly on
                <span class="font-medium text-neutral-900">{{ $order->customer_phone }}</span> to confirm it.
            </p>

            <div class="mt-7 inline-block rounded-xl border border-neutral-200 bg-neutral-50 px-8 py-5">
                <p class="text-xs uppercase tracking-wider text-neutral-500">Your order number</p>
                <p class="mt-1.5 font-mono text-xl font-semibold text-neutral-900">{{ $order->order_number }}</p>
            </div>

            @if ($order->customer_email)
                <p class="mt-5 text-sm text-neutral-600">
                    A confirmation email is on its way to {{ $order->customer_email }}.
                </p>
            @endif
        </div>

        {{-- Summary --}}
        <div class="mt-12 rounded-xl border border-neutral-200">
            <div class="border-b border-neutral-200 px-6 py-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-900">Order summary</h2>
            </div>

            <ul class="divide-y divide-neutral-200">
                @foreach ($order->items as $item)
                    <li class="flex items-start justify-between gap-4 px-6 py-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-neutral-900">{{ $item->product_name }}</p>
                            @if ($item->variant_label)
                                <p class="mt-0.5 text-xs text-neutral-500">{{ $item->variant_label }}</p>
                            @endif
                            <p class="mt-0.5 text-xs text-neutral-500">
                                {{ bdt($item->price) }} &times; {{ $item->quantity }}
                            </p>
                        </div>
                        <p class="shrink-0 text-sm font-medium text-neutral-900">{{ bdt($item->line_total) }}</p>
                    </li>
                @endforeach
            </ul>

            <dl class="space-y-3 border-t border-neutral-200 px-6 py-5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-neutral-600">Subtotal</dt>
                    <dd class="text-neutral-900">{{ bdt($order->subtotal) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-600">Delivery charge</dt>
                    <dd class="text-neutral-900">{{ bdt($order->delivery_charge) }}</dd>
                </div>
                <div class="flex justify-between border-t border-neutral-200 pt-3 text-base font-semibold text-neutral-900">
                    <dt>Total payable</dt>
                    <dd>{{ bdt($order->total) }}</dd>
                </div>
            </dl>

            <div class="border-t border-neutral-200 bg-green-50 px-6 py-4">
                <p class="text-sm font-medium text-green-800">Payment method: Cash on Delivery</p>
                <p class="mt-1 text-xs leading-relaxed text-green-700">
                    Please keep {{ bdt($order->total) }} ready for the courier.
                </p>
            </div>
        </div>

        {{-- Delivery address --}}
        <div class="mt-8 rounded-xl border border-neutral-200 px-6 py-5">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-900">Delivery address</h2>
            <p class="mt-3 text-sm leading-relaxed text-neutral-700">
                {{ $order->customer_name }}<br>
                {{ $order->shipping_address }}<br>
                {{ $order->city }}<br>
                {{ $order->customer_phone }}
            </p>
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('shop.index') }}"
               class="inline-block rounded-full bg-brand-700 px-8 py-3.5 text-sm font-medium text-white transition hover:bg-brand-600">
                Continue shopping
            </a>
            <a href="{{ route('track.form') }}"
               class="ml-2 inline-block rounded-full border border-neutral-300 px-8 py-3.5 text-sm font-medium transition hover:border-neutral-900">
                Track this order
            </a>
            <p class="mt-4 text-xs text-neutral-500">
                Save your order number — quote it if you contact us about this order.
            </p>
        </div>
    </div>

</x-app-layout>
