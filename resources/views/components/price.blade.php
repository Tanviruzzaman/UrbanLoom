@props(['product', 'size' => 'base'])

@php
    use App\Support\Money;

    $currentClasses = match ($size) {
        'lg' => 'text-2xl font-semibold',
        'sm' => 'text-sm font-semibold',
        default => 'text-base font-semibold',
    };

    $compareClasses = match ($size) {
        'lg' => 'text-lg',
        'sm' => 'text-xs',
        default => 'text-sm',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-baseline gap-2']) }}>
    <span class="{{ $currentClasses }} text-neutral-900">{{ Money::bdt($product->price) }}</span>

    @if ($product->on_sale)
        <span class="{{ $compareClasses }} text-neutral-400 line-through">{{ Money::bdt($product->compare_at_price) }}</span>
        <span class="rounded bg-red-50 px-1.5 py-0.5 text-[11px] font-semibold text-red-600">
            -{{ $product->discount_percentage }}%
        </span>
    @endif
</div>
