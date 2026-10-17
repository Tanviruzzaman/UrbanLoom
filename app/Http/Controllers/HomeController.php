<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Storefront landing page: featured categories and featured products.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('id')
            ->get();

        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->with(['category', 'primaryImage'])
            ->latest('id')
            ->take(8)
            ->get();

        return view('storefront.home', compact('categories', 'featuredProducts'));
    }
}
