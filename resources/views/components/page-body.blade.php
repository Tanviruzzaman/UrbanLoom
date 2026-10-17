@props(['heading'])

<div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $heading }}</h1>

    <div class="mt-8 space-y-5 text-[15px] leading-relaxed text-neutral-700">
        {{ $slot }}
    </div>
</div>
