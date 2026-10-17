@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
])

@php
    $brand = config('store.name');
    $cartCount = app(\App\Services\CartService::class)->count();
    $navCategories = \App\Models\Category::query()->active()->orderBy('id')->get();

    $pageTitle = $title ? $title . ' — ' . $brand : $brand . ' — ' . config('store.tagline');
    $pageDescription = $description
        ?: config('store.tagline') . '. Cash on delivery all over Bangladesh, with delivery in 2–4 days.';
    $socialImage = $image ?: asset(config('store.social_image'));
    $logo = config('store.logo');
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph: what Facebook shows when someone shares a link. --}}
    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $socialImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="en_GB">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $socialImage }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#334181">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-neutral-900 antialiased">

    {{-- Announcement bar --}}
    <div class="bg-brand-950 px-4 py-2 text-center text-xs tracking-wide text-neutral-100 sm:text-sm">
        Cash on Delivery all over Bangladesh &nbsp;·&nbsp; Delivery ৳60 inside Dhaka, ৳120 outside
    </div>

    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-neutral-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

            {{-- Mobile menu (pure CSS, no JS dependency) --}}
            <details class="group relative md:hidden">
                <summary class="flex cursor-pointer list-none items-center rounded-md p-2 text-neutral-700 hover:bg-neutral-100" aria-label="Menu">
                    <svg class="size-6 group-open:hidden" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                    <svg class="hidden size-6 group-open:block" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </summary>
                <nav class="absolute left-0 top-full mt-2 w-56 rounded-xl border border-neutral-200 bg-white p-2 shadow-lg">
                    <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-neutral-100">Home</a>
                    <a href="{{ route('shop.index') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-neutral-100">Shop All</a>
                    @foreach ($navCategories as $category)
                        <a href="{{ route('shop.index', ['category' => $category->slug]) }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-neutral-100">{{ $category->name }}</a>
                    @endforeach
                </nav>
            </details>

            {{-- Logo: an image if one is configured, otherwise the name as text --}}
            <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ $brand }} home">
                @if ($logo)
                    <img src="{{ asset($logo) }}" alt="{{ $brand }}" class="h-8 w-auto md:h-9">
                @else
                    <span class="text-xl font-semibold tracking-tight md:text-2xl">
                        {{ $brand }}<span class="text-brand-600">.</span>
                    </span>
                @endif
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden items-center gap-7 text-sm font-medium text-neutral-600 md:flex">
                <a href="{{ route('shop.index') }}" class="transition hover:text-neutral-900">Shop All</a>
                @foreach ($navCategories as $category)
                    <a href="{{ route('shop.index', ['category' => $category->slug]) }}" class="transition hover:text-neutral-900">{{ $category->name }}</a>
                @endforeach
            </nav>

            {{-- Cart --}}
            <a href="{{ route('cart.index') }}" class="relative flex items-center gap-2 rounded-full border border-neutral-200 px-3 py-2 text-sm font-medium transition hover:border-neutral-900">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                <span class="hidden sm:inline">Cart</span>
                @if ($cartCount > 0)
                    <span class="absolute -right-1.5 -top-1.5 flex size-5 items-center justify-center rounded-full bg-brand-700 text-[11px] font-semibold text-white">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </header>

    {{-- Flash messages --}}
    @if (session('success') || session('error'))
        <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div @class([
                'rounded-lg border px-4 py-3 text-sm',
                'border-green-200 bg-green-50 text-green-800' => session('success'),
                'border-red-200 bg-red-50 text-red-800' => session('error'),
            ])>
                {{ session('success') ?? session('error') }}
            </div>
        </div>
    @endif

    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="mt-20 border-t border-neutral-200 bg-neutral-50">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-4 lg:px-8">
            <div class="md:col-span-2">
                <p class="text-lg font-semibold tracking-tight">{{ $brand }}<span class="text-brand-600">.</span></p>
                <p class="mt-3 max-w-sm text-sm leading-relaxed text-neutral-600">
                    {{ config('store.tagline') }}. Everyday clothing and jewellery for men and women,
                    delivered to your door with cash on delivery.
                </p>
            </div>

            <div>
                <h2 class="text-sm font-semibold tracking-wide text-neutral-900">Company</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-neutral-600">
                    <li><a href="{{ route('pages.about') }}" class="transition hover:text-neutral-900">About Us</a></li>
                    <li><a href="{{ route('pages.contact') }}" class="transition hover:text-neutral-900">Contact Us</a></li>
                    <li><a href="{{ route('shop.index') }}" class="transition hover:text-neutral-900">Shop All</a></li>
                    <li><a href="{{ route('track.form') }}" class="transition hover:text-neutral-900">Track Your Order</a></li>
                </ul>

                <h2 class="mt-7 text-sm font-semibold tracking-wide text-neutral-900">Policies</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-neutral-600">
                    <li><a href="{{ route('pages.returns') }}" class="transition hover:text-neutral-900">Returns &amp; Refunds</a></li>
                    <li><a href="{{ route('pages.privacy') }}" class="transition hover:text-neutral-900">Privacy Policy</a></li>
                    <li><a href="{{ route('pages.terms') }}" class="transition hover:text-neutral-900">Terms &amp; Conditions</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold tracking-wide text-neutral-900">Get in touch</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-neutral-600">
                    <li><a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}" class="transition hover:text-neutral-900">{{ config('store.phone') }}</a></li>
                    <li><a href="mailto:{{ config('store.email') }}" class="transition hover:text-neutral-900">{{ config('store.email') }}</a></li>
                    <li class="leading-relaxed">{{ config('store.address') }}</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-neutral-200">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-neutral-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>&copy; {{ date('Y') }} {{ $brand }}. All rights reserved.</p>
                <p>Cash on Delivery · Nationwide shipping</p>
            </div>
        </div>
    </footer>
</body>
</html>
