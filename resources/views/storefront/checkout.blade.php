<x-app-layout title="Checkout">

    @php
        $selectedCity = old('city');
        $deliveryCharge = $selectedCity ? delivery_charge($selectedCity) : null;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 md:py-14 lg:px-8">

        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Checkout</h1>
        <p class="mt-2 text-sm text-neutral-600">Cash on delivery — pay when your parcel arrives.</p>

        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-medium">Please fix the following:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-10 gap-12 lg:flex">
            @csrf

            {{-- Customer details --}}
            <div class="lg:flex-1">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-900">Delivery details</h2>

                <div class="mt-5 space-y-5">

                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-neutral-900">
                            Full name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="customer_name" id="customer_name"
                               value="{{ old('customer_name') }}" required autocomplete="name"
                               @class([
                                   'mt-2 block w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                   'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('customer_name'),
                                   'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('customer_name'),
                               ])>
                        @error('customer_name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-neutral-900">
                            Mobile number <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" name="customer_phone" id="customer_phone"
                               value="{{ old('customer_phone') }}" required inputmode="tel"
                               placeholder="01712345678" autocomplete="tel"
                               @class([
                                   'mt-2 block w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                   'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('customer_phone'),
                                   'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('customer_phone'),
                               ])>
                        @error('customer_phone')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1.5 text-xs text-neutral-500">We call this number to confirm your order.</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-neutral-900">
                            Email <span class="font-normal text-neutral-400">(optional)</span>
                        </label>
                        <input type="email" name="customer_email" id="customer_email"
                               value="{{ old('customer_email') }}" autocomplete="email"
                               @class([
                                   'mt-2 block w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                   'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('customer_email'),
                                   'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('customer_email'),
                               ])>
                        @error('customer_email')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1.5 text-xs text-neutral-500">Add it and we'll email your order confirmation.</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-neutral-900">
                            City <span class="text-red-600">*</span>
                        </label>
                        <select name="city" id="city" required
                                @class([
                                    'mt-2 block w-full rounded-lg border bg-white px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                    'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('city'),
                                    'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('city'),
                                ])>
                            <option value="">Select your city</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}"
                                        data-charge="{{ delivery_charge($city) }}"
                                        @selected(old('city') === $city)>
                                    {{ $city }}
                                    ({{ bdt(delivery_charge($city)) }} delivery)
                                </option>
                            @endforeach
                        </select>
                        @error('city')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="shipping_address" class="block text-sm font-medium text-neutral-900">
                            Full address <span class="text-red-600">*</span>
                        </label>
                        <textarea name="shipping_address" id="shipping_address" rows="3" required
                                  placeholder="House / flat, road, area, landmark"
                                  @class([
                                      'mt-2 block w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                      'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('shipping_address'),
                                      'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('shipping_address'),
                                  ])>{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-neutral-900">
                            Order notes <span class="font-normal text-neutral-400">(optional)</span>
                        </label>
                        <textarea name="notes" id="notes" rows="2"
                                  placeholder="Anything we should know about delivery"
                                  class="mt-2 block w-full rounded-lg border border-neutral-300 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="mt-10 lg:mt-0 lg:w-96 lg:shrink-0">
                <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-900">Your order</h2>

                    <ul class="mt-5 divide-y divide-neutral-200 border-y border-neutral-200">
                        @foreach ($lines as $line)
                            <li class="flex gap-3 py-4">
                                <img src="{{ $line->product->primaryImageUrl() }}" alt="{{ $line->product->name }}"
                                     class="size-14 shrink-0 rounded-lg object-cover">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-neutral-900">{{ $line->product->name }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-500">
                                        {{ $line->variant->label }} &middot; Qty {{ $line->quantity }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-medium text-neutral-900">{{ bdt($line->line_total) }}</p>
                            </li>
                        @endforeach
                    </ul>

                    {{-- data-subtotal drives the live recalculation in app.js; the
                         server recalculates the real charge on submit regardless. --}}
                    <dl class="mt-5 space-y-3 text-sm" data-order-summary data-subtotal="{{ $subtotal }}">
                        <div class="flex justify-between">
                            <dt class="text-neutral-600">Subtotal</dt>
                            <dd class="font-medium text-neutral-900">{{ bdt($subtotal) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-neutral-600">Delivery charge</dt>
                            <dd class="font-medium text-neutral-900" data-delivery-charge>
                                @if ($deliveryCharge !== null)
                                    {{ bdt($deliveryCharge) }}
                                @else
                                    <span class="font-normal text-neutral-500">Select a city</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between border-t border-neutral-300 pt-3 text-base font-semibold text-neutral-900">
                            <dt>Total</dt>
                            <dd data-order-total>
                                @if ($deliveryCharge !== null)
                                    {{ bdt($subtotal + $deliveryCharge) }}
                                @else
                                    {{ bdt($subtotal) }}+
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-5 rounded-lg border border-neutral-200 bg-white px-4 py-3">
                        <p class="text-sm font-medium text-neutral-900">Payment: Cash on Delivery</p>
                        <p class="mt-1 text-xs leading-relaxed text-neutral-600">
                            Pay the courier in cash when your parcel arrives. No advance payment needed.
                        </p>
                    </div>

                    <button type="submit"
                            class="mt-6 w-full rounded-full bg-brand-700 px-8 py-4 text-sm font-medium text-white transition hover:bg-brand-600">
                        Place Order
                    </button>

                    <p class="mt-3 text-center text-xs text-neutral-500">
                        By placing this order you agree to our
                        <a href="{{ route('pages.terms') }}" class="underline underline-offset-2">terms</a>.
                    </p>
                </div>
            </div>
        </form>
    </div>

</x-app-layout>
