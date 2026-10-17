<x-app-layout
    :title="$activeCategory?->name ?? 'Shop All'"
    :description="$activeCategory ? 'Shop ' . $activeCategory->name . ' at ' . config('store.name') . '. Cash on delivery all over Bangladesh.' : null">

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 md:py-14 lg:px-8">

        {{-- Heading --}}
        <div class="gap-6 border-b border-neutral-200 pb-6 sm:flex sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    @if ($search !== '')
                        Search results
                    @else
                        {{ $activeCategory?->name ?? 'Shop All' }}
                    @endif
                </h1>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ $products->total() }} {{ Str::plural('product', $products->total()) }}
                    @if ($search !== '')
                        for &ldquo;{{ $search }}&rdquo;
                    @elseif ($activeCategory)
                        in {{ $activeCategory->name }}
                    @endif
                </p>
            </div>

            {{-- Search --}}
            <form method="GET" action="{{ route('shop.index') }}" class="mt-5 sm:mt-0 sm:w-72">
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
                @endif
                <label for="q" class="sr-only">Search products</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-neutral-400"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" />
                    </svg>
                    <input type="search" name="q" id="q" value="{{ $search }}"
                           placeholder="Search kamiz, tees, jewellery…"
                           class="w-full rounded-full border border-neutral-300 py-2.5 pl-10 pr-4 text-sm focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
                </div>
            </form>
        </div>

        @if ($search !== '' && $products->isEmpty())
            <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
                <p class="text-sm font-medium text-amber-900">Nothing matched &ldquo;{{ $search }}&rdquo;</p>
                <p class="mt-1.5 text-sm text-amber-800">
                    Try a shorter word, or
                    <a href="{{ route('shop.index') }}" class="font-medium underline underline-offset-2">browse everything</a>.
                </p>
            </div>
        @endif

        <div class="mt-8 gap-10 md:flex">

            {{-- Category filter --}}
            <aside class="md:w-56 md:shrink-0">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Categories</h2>

                <ul class="mt-4 flex flex-wrap gap-2 md:block md:space-y-1">
                    <li>
                        <a href="{{ route('shop.index') }}"
                           @class([
                               'block rounded-full px-4 py-2 text-sm transition md:rounded-lg',
                               'bg-brand-700 text-white' => ! $activeCategory,
                               'border border-neutral-200 text-neutral-700 hover:border-neutral-900 md:border-0 md:hover:bg-neutral-100' => (bool) $activeCategory,
                           ])>
                            All products
                        </a>
                    </li>

                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('shop.index', array_filter(['category' => $category->slug, 'q' => $search])) }}"
                               @class([
                                   'flex items-center justify-between gap-2 rounded-full px-4 py-2 text-sm transition md:rounded-lg',
                                   'bg-brand-700 text-white' => $activeCategory?->id === $category->id,
                                   'border border-neutral-200 text-neutral-700 hover:border-neutral-900 md:border-0 md:hover:bg-neutral-100' => $activeCategory?->id !== $category->id,
                               ])>
                                <span>{{ $category->name }}</span>
                                <span @class([
                                    'text-xs',
                                    'text-neutral-300' => $activeCategory?->id === $category->id,
                                    'text-neutral-400' => $activeCategory?->id !== $category->id,
                                ])>{{ $category->products_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Sort --}}
                <h2 class="mt-8 text-xs font-semibold uppercase tracking-wider text-neutral-500">Sort by</h2>
                <form method="GET" action="{{ route('shop.index') }}" class="mt-3">
                    @if ($activeCategory)
                        <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
                    @endif
                    @if ($search !== '')
                        <input type="hidden" name="q" value="{{ $search }}">
                    @endif
                    <select name="sort" onchange="this.form.submit()"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900">
                        <option value="latest" @selected(request('sort') === 'latest' || ! request('sort'))>Newest</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
                    </select>
                    <noscript><button type="submit" class="mt-2 w-full rounded-lg bg-brand-700 px-3 py-2 text-sm text-white">Apply</button></noscript>
                </form>
            </aside>

            {{-- Product grid --}}
            <div class="mt-8 flex-1 md:mt-0">
                @if ($products->isEmpty())
                    <div class="rounded-xl border border-dashed border-neutral-300 p-16 text-center">
                        <p class="text-sm text-neutral-600">No products found here yet.</p>
                        <a href="{{ route('shop.index') }}" class="mt-4 inline-block text-sm font-medium underline underline-offset-4">
                            View all products
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-x-4 gap-y-9 lg:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="mt-12">
                            {{ $products->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

</x-app-layout>
