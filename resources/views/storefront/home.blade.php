<x-app-layout>

    {{-- Hero --}}
    <section class="border-b border-neutral-200 bg-neutral-50">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-24 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-neutral-500">
                    {{ config('store.name') }} — Est. Dhaka
                </p>
                <h1 class="mt-4 text-4xl font-semibold leading-[1.1] tracking-tight text-neutral-900 sm:text-5xl md:text-6xl">
                    Everyday clothing,<br>made for Bangladesh.
                </h1>
                <p class="mt-5 max-w-lg text-base leading-relaxed text-neutral-600">
                    Kamiz, tops, tees and jewellery for men and women — honest prices, and cash on
                    delivery anywhere in the country. No advance payment: pay when it reaches your door.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('shop.index') }}"
                       class="rounded-full bg-brand-700 px-7 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                        Shop the collection
                    </a>
                    <a href="{{ route('shop.index', ['sort' => 'latest']) }}"
                       class="rounded-full border border-neutral-300 px-7 py-3 text-sm font-medium text-neutral-900 transition hover:border-neutral-900">
                        New arrivals
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured categories --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Shop by category</h2>
                    <p class="mt-2 text-sm text-neutral-600">Find your fit across our collections.</p>
                </div>
                <a href="{{ route('shop.index') }}" class="hidden shrink-0 text-sm font-medium text-neutral-600 underline-offset-4 transition hover:text-neutral-900 hover:underline sm:block">
                    View all
                </a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                       class="group relative flex aspect-square flex-col justify-end overflow-hidden rounded-xl bg-brand-900 p-5 transition hover:bg-brand-800">
                        <span class="absolute inset-0 bg-gradient-to-tr from-brand-950 via-brand-900 to-brand-700 opacity-90 transition group-hover:opacity-100"></span>
                        <span class="relative">
                            <span class="block text-lg font-semibold text-white">{{ $category->name }}</span>
                            <span class="mt-1 block text-xs text-neutral-300">
                                {{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Featured products --}}
    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 md:pb-24 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Featured</h2>
                <p class="mt-2 text-sm text-neutral-600">Hand-picked favourites from the collection.</p>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden shrink-0 text-sm font-medium text-neutral-600 underline-offset-4 transition hover:text-neutral-900 hover:underline sm:block">
                View all
            </a>
        </div>

        @if ($featuredProducts->isEmpty())
            <p class="mt-8 rounded-xl border border-dashed border-neutral-300 p-10 text-center text-sm text-neutral-500">
                No featured products yet. Mark a few products as featured to see them here.
            </p>
        @else
            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif

        <div class="mt-10 text-center sm:hidden">
            <a href="{{ route('shop.index') }}" class="inline-block rounded-full border border-neutral-300 px-7 py-3 text-sm font-medium transition hover:border-neutral-900">
                View all products
            </a>
        </div>
    </section>

    {{-- Trust strip --}}
    <section class="border-y border-neutral-200 bg-neutral-50">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8">
            @foreach ([
                ['Cash on Delivery', 'Pay only when your parcel arrives. No advance needed.'],
                ['Fast nationwide shipping', '৳60 inside Dhaka, ৳120 outside — delivered in 2–4 days.'],
                ['Easy exchange', 'Wrong size? Exchange within 3 days of delivery.'],
            ] as [$heading, $body])
                <div>
                    <h3 class="text-sm font-semibold tracking-wide text-neutral-900">{{ $heading }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-neutral-600">{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>

</x-app-layout>
