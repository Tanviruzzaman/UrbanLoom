<x-app-layout title="Page not found">

    <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6 md:py-28 lg:px-8">

        <p class="font-mono text-sm tracking-[0.2em] text-brand-600">404</p>

        <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
            We couldn't find that page
        </h1>

        <p class="mx-auto mt-4 max-w-md text-[15px] leading-relaxed text-neutral-600">
            The link may be old, or the product may have sold out and been removed.
            Everything we have in stock is in the shop.
        </p>

        <div class="mt-9 flex flex-wrap justify-center gap-3">
            <a href="{{ route('shop.index') }}"
               class="rounded-full bg-brand-700 px-7 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                Browse the shop
            </a>
            <a href="{{ route('home') }}"
               class="rounded-full border border-neutral-300 px-7 py-3 text-sm font-medium text-neutral-900 transition hover:border-neutral-900">
                Back to home
            </a>
        </div>

        <p class="mt-10 text-sm text-neutral-500">
            Looking for an order you placed? Call us on
            <a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}"
               class="font-medium text-brand-600 underline-offset-4 hover:underline">{{ config('store.phone') }}</a>
            with your order number.
        </p>
    </div>

</x-app-layout>
