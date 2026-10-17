@props(['product'])

<a href="{{ route('product.show', $product) }}" class="group block">
    <div class="relative aspect-[4/5] overflow-hidden rounded-xl bg-neutral-100">
        <img src="{{ $product->primaryImageUrl() }}"
             alt="{{ $product->name }}"
             loading="lazy"
             class="size-full object-cover transition duration-500 group-hover:scale-105">

        @if ($product->on_sale)
            <span class="absolute left-3 top-3 rounded-full bg-brand-700 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-white">
                Sale
            </span>
        @endif
    </div>

    <div class="mt-3 space-y-1.5">
        @if ($product->category)
            <p class="text-[11px] uppercase tracking-wider text-neutral-400">{{ $product->category->name }}</p>
        @endif

        <h3 class="line-clamp-2 text-sm font-medium leading-snug text-neutral-900 transition group-hover:text-neutral-600">
            {{ $product->name }}
        </h3>

        <x-price :product="$product" size="sm" />
    </div>
</a>
