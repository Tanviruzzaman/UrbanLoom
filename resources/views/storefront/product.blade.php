<x-app-layout
    :title="$product->name"
    :description="Str::limit(strip_tags($product->description ?: $product->name), 155)"
    :image="$product->primaryImageUrl()"
    type="product">

    @php
        $images = $product->images;
        $inStockVariants = $product->variants->where('stock_quantity', '>', 0);
        $isSoldOut = $inStockVariants->isEmpty();
        $maxPerItem = config('store.cart.max_quantity_per_item', 10);

        // Unsized products (jewellery, accessories) have a single variant with
        // no size — there is nothing for the customer to choose, so the picker
        // is replaced by a hidden input.
        $hasChoice = $product->variants->count() > 1
            || $product->variants->contains(fn ($variant) => filled($variant->size));
        $soleVariant = $hasChoice ? null : $product->variants->first();
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 md:py-12 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="flex flex-wrap items-center gap-2 text-sm text-neutral-500">
            <a href="{{ route('home') }}" class="transition hover:text-neutral-900">Home</a>
            <span>/</span>
            <a href="{{ route('shop.index') }}" class="transition hover:text-neutral-900">Shop</a>
            @if ($product->category)
                <span>/</span>
                <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="transition hover:text-neutral-900">
                    {{ $product->category->name }}
                </a>
            @endif
        </nav>

        <div class="mt-8 gap-10 lg:flex lg:gap-14">

            {{-- Gallery --}}
            <div class="lg:w-1/2">
                <div class="overflow-hidden rounded-xl bg-neutral-100">
                    <img id="gallery-main"
                         src="{{ $product->primaryImageUrl() }}"
                         alt="{{ $product->name }}"
                         class="aspect-[4/5] w-full object-cover">
                </div>

                @if ($images->count() > 1)
                    <div class="mt-3 grid grid-cols-5 gap-3" data-gallery-thumbs>
                        @foreach ($images as $image)
                            <button type="button"
                                    data-full="{{ $image->url }}"
                                    class="overflow-hidden rounded-lg border-2 border-transparent bg-neutral-100 transition hover:border-neutral-400 focus:border-neutral-900 focus:outline-none">
                                <img src="{{ $image->url }}" alt="{{ $product->name }} thumbnail" loading="lazy"
                                     class="aspect-square w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div class="mt-8 lg:mt-0 lg:w-1/2">
                @if ($product->category)
                    <p class="text-xs uppercase tracking-wider text-neutral-500">{{ $product->category->name }}</p>
                @endif

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-neutral-900 sm:text-3xl">
                    {{ $product->name }}
                </h1>

                <x-price :product="$product" size="lg" class="mt-4" />

                @if ($product->description)
                    <p class="mt-5 text-[15px] leading-relaxed text-neutral-600">{{ $product->description }}</p>
                @endif

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($isSoldOut)
                    <div class="mt-7 rounded-xl border border-neutral-300 bg-neutral-50 p-5 text-center">
                        <p class="text-sm font-medium text-neutral-900">Out of stock</p>
                        <p class="mt-1 text-sm text-neutral-600">This product is sold out right now. Check back soon.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('cart.store') }}" class="mt-7">
                        @csrf

                        {{-- Size / variant selector --}}
                        @if (! $hasChoice)
                            <input type="hidden" name="product_variant_id" value="{{ $soleVariant->id }}">
                            <p class="text-sm text-neutral-600">
                                <span class="font-medium text-neutral-900">{{ $product->totalStock() }}</span> in stock
                            </p>
                        @else
                        <fieldset>
                            <legend class="text-sm font-medium text-neutral-900">
                                Select size
                                <span class="text-neutral-400">·</span>
                                <span class="font-normal text-neutral-500">{{ $product->totalStock() }} in stock</span>
                            </legend>

                            <div class="mt-3 flex flex-wrap gap-2.5">
                                @foreach ($product->variants as $variant)
                                    @php $available = $variant->stock_quantity > 0; @endphp

                                    <div>
                                        <input type="radio"
                                               name="product_variant_id"
                                               id="variant-{{ $variant->id }}"
                                               value="{{ $variant->id }}"
                                               class="peer sr-only"
                                               data-stock="{{ $variant->stock_quantity }}"
                                               @disabled(! $available)
                                               @checked(old('product_variant_id') == $variant->id)
                                               required>

                                        <label for="variant-{{ $variant->id }}"
                                               @class([
                                                   'flex min-w-[3.25rem] cursor-pointer items-center justify-center rounded-lg border px-4 py-2.5 text-sm font-medium transition',
                                                   'border-neutral-300 text-neutral-900 hover:border-brand-600 peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white' => $available,
                                                   'cursor-not-allowed border-neutral-200 bg-neutral-50 text-neutral-300 line-through' => ! $available,
                                               ])
                                               @if (! $available) title="Out of stock" @endif>
                                            {{ $variant->size }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            @if ($product->variants->first()?->color)
                                <p class="mt-3 text-sm text-neutral-600">
                                    Colour: <span class="font-medium text-neutral-900">{{ $product->variants->first()->color }}</span>
                                </p>
                            @endif
                        </fieldset>
                        @endif

                        {{-- Quantity --}}
                        <div class="mt-6">
                            <label for="quantity" class="text-sm font-medium text-neutral-900">Quantity</label>
                            <input type="number"
                                   name="quantity"
                                   id="quantity"
                                   value="{{ old('quantity', 1) }}"
                                   min="1"
                                   max="{{ $maxPerItem }}"
                                   inputmode="numeric"
                                   class="mt-2 block w-28 rounded-lg border border-neutral-300 px-3 py-2.5 text-sm focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900">
                        </div>

                        <button type="submit"
                                class="mt-7 w-full rounded-full bg-brand-700 px-8 py-4 text-sm font-medium text-white transition hover:bg-brand-600">
                            Add to Cart
                        </button>
                    </form>
                @endif

                {{-- Reassurance --}}
                <dl class="mt-8 space-y-3 border-t border-neutral-200 pt-6 text-sm">
                    <div class="flex gap-3">
                        <dt class="font-medium text-neutral-900">Payment</dt>
                        <dd class="text-neutral-600">Cash on delivery — pay when it arrives</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="font-medium text-neutral-900">Delivery</dt>
                        <dd class="text-neutral-600">
                            ৳{{ config('store.delivery.inside_dhaka') }} inside Dhaka ·
                            ৳{{ config('store.delivery.outside_dhaka') }} outside
                        </dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="font-medium text-neutral-900">Exchange</dt>
                        <dd class="text-neutral-600">Within 3 days of delivery</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Related --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="mt-20">
                <h2 class="text-xl font-semibold tracking-tight sm:text-2xl">You may also like</h2>
                <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

</x-app-layout>
