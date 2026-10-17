<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopFilterRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    /**
     * Paginated product grid, optionally filtered to one category.
     */
    public function index(ShopFilterRequest $request): View
    {
        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('id')
            ->get();

        $activeCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->string('category')->toString())
            : null;

        $search = trim((string) $request->input('q'));

        $products = Product::query()
            ->active()
            ->with(['category', 'primaryImage'])
            ->when($activeCategory, fn ($query) => $query->where('category_id', $activeCategory->id))
            ->when($search !== '', function ($query) use ($search) {
                // Escape the LIKE wildcards so a customer typing "50%" searches
                // for that text instead of matching everything.
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';

                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->when($request->input('sort') === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($request->input('sort') === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when(! $request->filled('sort'), fn ($query) => $query->latest('id'))
            ->paginate(12)
            ->withQueryString();

        return view('storefront.shop', compact('categories', 'activeCategory', 'products', 'search'));
    }
}
