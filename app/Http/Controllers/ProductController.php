<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    /**
     * Product detail page: gallery, variant selector and add-to-cart form.
     */
    public function show(Product $product): View
    {
        if (! $product->is_active) {
            throw new NotFoundHttpException;
        }

        $product->load(['category', 'images', 'variants']);

        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['category', 'primaryImage'])
            ->take(4)
            ->get();

        return view('storefront.product', compact('product', 'relatedProducts'));
    }
}
