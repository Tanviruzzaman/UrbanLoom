<x-app-layout title="Your Cart">

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 md:py-14 lg:px-8">

        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Your Cart</h1>

        @if ($lines->isEmpty())
            <div class="mt-10 rounded-xl border border-dashed border-neutral-300 p-16 text-center">
                <p class="text-sm text-neutral-600">Your cart is empty.</p>
                <a href="{{ route('shop.index') }}"
                   class="mt-5 inline-block rounded-full bg-brand-700 px-7 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                    Start shopping
                </a>
            </div>
        @else
            <div class="mt-10 gap-12 lg:flex">

                {{-- Lines --}}
                <div class="lg:flex-1">
                    <ul class="divide-y divide-neutral-200 border-y border-neutral-200">
                        @foreach ($lines as $line)
                            <li class="flex gap-4 py-6 sm:gap-6">

                                <a href="{{ route('product.show', $line->product) }}" class="shrink-0">
                                    <img src="{{ $line->product->primaryImageUrl() }}"
                                         alt="{{ $line->product->name }}"
                                         class="size-24 rounded-lg object-cover sm:size-28">
                                </a>

                                <div class="flex flex-1 flex-col justify-between gap-3 sm:flex-row sm:gap-6">
                                    <div class="min-w-0">
                                        <a href="{{ route('product.show', $line->product) }}"
                                           class="text-sm font-medium text-neutral-900 transition hover:text-neutral-600">
                                            {{ $line->product->name }}
                                        </a>

                                        @if ($line->variant->label)
                                            <p class="mt-1 text-sm text-neutral-500">{{ $line->variant->label }}</p>
                                        @endif

                                        <p class="mt-1 text-sm text-neutral-500">{{ bdt($line->unit_price) }} each</p>

                                        @if (! $line->available)
                                            <p class="mt-2 text-xs font-medium text-red-600">
                                                No longer available — please remove this item to check out.
                                            </p>
                                        @elseif (! $line->in_stock)
                                            <p class="mt-2 text-xs font-medium text-red-600">
                                                Only {{ $line->variant->stock_quantity }} left — please reduce the quantity.
                                            </p>
                                        @endif

                                        {{-- Quantity --}}
                                        <form method="POST" action="{{ route('cart.update', $line->variant) }}" class="mt-3 flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <label for="qty-{{ $line->variant->id }}" class="sr-only">Quantity</label>
                                            <input type="number"
                                                   name="quantity"
                                                   id="qty-{{ $line->variant->id }}"
                                                   value="{{ $line->quantity }}"
                                                   min="0"
                                                   max="{{ $line->max_quantity }}"
                                                   inputmode="numeric"
                                                   class="w-20 rounded-lg border border-neutral-300 px-3 py-1.5 text-sm focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900">
                                            <button type="submit" class="text-sm font-medium text-neutral-900 underline underline-offset-4 transition hover:text-neutral-600">
                                                Update
                                            </button>
                                        </form>
                                    </div>

                                    <div class="flex items-start justify-between gap-4 sm:flex-col sm:items-end sm:justify-between">
                                        <p class="text-sm font-semibold text-neutral-900">{{ bdt($line->line_total) }}</p>

                                        <form method="POST" action="{{ route('cart.destroy', $line->variant) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-neutral-500 underline underline-offset-4 transition hover:text-red-600">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('shop.index') }}" class="mt-6 inline-block text-sm font-medium text-neutral-600 underline underline-offset-4 transition hover:text-neutral-900">
                        Continue shopping
                    </a>
                </div>

                {{-- Summary --}}
                <div class="mt-10 lg:mt-0 lg:w-80 lg:shrink-0">
                    <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-900">Order summary</h2>

                        <dl class="mt-5 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-neutral-600">Subtotal</dt>
                                <dd class="font-medium text-neutral-900">{{ bdt($subtotal) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-neutral-600">Delivery</dt>
                                <dd class="text-neutral-500">Calculated at checkout</dd>
                            </div>
                        </dl>

                        <p class="mt-5 border-t border-neutral-200 pt-4 text-xs leading-relaxed text-neutral-500">
                            Delivery is ৳{{ config('store.delivery.inside_dhaka') }} inside Dhaka and
                            ৳{{ config('store.delivery.outside_dhaka') }} outside Dhaka.
                        </p>

                        <a href="{{ route('checkout.create') }}"
                           class="mt-6 block rounded-full bg-brand-700 px-8 py-3.5 text-center text-sm font-medium text-white transition hover:bg-brand-600">
                            Proceed to Checkout
                        </a>

                        <p class="mt-3 text-center text-xs text-neutral-500">Cash on delivery — no advance payment</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

</x-app-layout>
