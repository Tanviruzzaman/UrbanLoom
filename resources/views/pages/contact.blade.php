<x-app-layout title="Contact Us">

    <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">

        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Contact Us</h1>
        <p class="mt-3 max-w-2xl text-[15px] leading-relaxed text-neutral-600">
            Questions about an order, a size, or an exchange? Send us a message and we'll reply
            within one working day — or call us directly, we're on the phone from 10:00 to 20:00 every day.
        </p>

        <div class="mt-12 grid gap-12 lg:grid-cols-[minmax(0,1fr)_20rem]">

            {{-- Form --}}
            <div class="min-w-0">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-900">Send a message</h2>

                @if ($errors->any())
                    <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-medium">Please fix the following:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('pages.contact.send') }}" class="mt-6 space-y-5">
                    @csrf

                    {{-- Honeypot: hidden from people, tempting to bots. --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-neutral-900">
                                Your name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="name"
                                   @class([
                                       'mt-2 block w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                       'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('name'),
                                       'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('name'),
                                   ])>
                            @error('name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-neutral-900">
                                Email <span class="text-red-600">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
                                   @class([
                                       'mt-2 block w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                       'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('email'),
                                       'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('email'),
                                   ])>
                            @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-neutral-900">
                                Phone <span class="font-normal text-neutral-400">(optional)</span>
                            </label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="01712345678" inputmode="tel"
                                   @class([
                                       'mt-2 block w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                       'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('phone'),
                                       'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('phone'),
                                   ])>
                            @error('phone')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="order_number" class="block text-sm font-medium text-neutral-900">
                                Order number <span class="font-normal text-neutral-400">(optional)</span>
                            </label>
                            <input type="text" name="order_number" id="order_number" value="{{ old('order_number') }}"
                                   placeholder="ORD-20260914-A1B2C"
                                   class="mt-2 block w-full rounded-lg border border-neutral-300 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900">
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-neutral-900">
                            Subject <span class="text-red-600">*</span>
                        </label>
                        <select name="subject" id="subject" required
                                @class([
                                    'mt-2 block w-full rounded-lg border bg-white px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                    'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('subject'),
                                    'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('subject'),
                                ])>
                            @foreach ([
                                'Question about an order',
                                'Exchange or return',
                                'Product or sizing question',
                                'Delivery problem',
                                'Wholesale enquiry',
                                'Something else',
                            ] as $option)
                                <option value="{{ $option }}" @selected(old('subject') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('subject')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-neutral-900">
                            Message <span class="text-red-600">*</span>
                        </label>
                        <textarea name="message" id="message" rows="5" required
                                  placeholder="Tell us how we can help"
                                  @class([
                                      'mt-2 block w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-1',
                                      'border-red-400 focus:border-red-500 focus:ring-red-500' => $errors->has('message'),
                                      'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900' => ! $errors->has('message'),
                                  ])>{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit"
                            class="rounded-full bg-brand-700 px-8 py-3.5 text-sm font-medium text-white transition hover:bg-brand-600">
                        Send message
                    </button>
                </form>
            </div>

            {{-- Details --}}
            <aside>
                <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-900">Reach us directly</h2>

                    <dl class="mt-5 space-y-4 text-sm">
                        <div>
                            <dt class="text-neutral-500">Phone</dt>
                            <dd class="mt-1">
                                <a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}"
                                   class="font-medium text-neutral-900 underline-offset-4 hover:underline">
                                    {{ config('store.phone') }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-neutral-500">Email</dt>
                            <dd class="mt-1">
                                <a href="mailto:{{ config('store.email') }}"
                                   class="font-medium text-neutral-900 underline-offset-4 hover:underline">
                                    {{ config('store.email') }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-neutral-500">Address</dt>
                            <dd class="mt-1 leading-relaxed text-neutral-700">{{ config('store.address') }}</dd>
                        </div>
                        <div>
                            <dt class="text-neutral-500">Hours</dt>
                            <dd class="mt-1 text-neutral-700">Every day, 10:00 – 20:00</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-5 rounded-xl border border-neutral-200 p-6">
                    <h2 class="text-sm font-semibold text-neutral-900">Chasing an order?</h2>
                    <p class="mt-2 text-sm leading-relaxed text-neutral-600">
                        Include your order number (like
                        <span class="font-mono text-xs">ORD-20260914-A1B2C</span>)
                        from your confirmation email and we'll find it straight away.
                    </p>
                </div>
            </aside>
        </div>
    </div>

</x-app-layout>
