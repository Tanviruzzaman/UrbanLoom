<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Support\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        return view('storefront.cart', [
            'lines' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $variant = ProductVariant::with('product')->findOrFail($request->integer('product_variant_id'));

        if ($variant->stock_quantity < 1) {
            return back()->with('error', 'Sorry, that size just went out of stock.');
        }

        $result = $this->cart->add($variant, $request->integer('quantity'));

        if ($result['added'] <= 0) {
            return back()->with('error', 'You already have all available stock of that size in your cart.');
        }

        $message = $result['clamped']
            ? "Only {$result['added']} left in that size — added what we could."
            : 'Added to your cart.';

        return redirect()->route('cart.index')->with('success', $message);
    }

    public function update(UpdateCartRequest $request, ProductVariant $variant): RedirectResponse
    {
        $result = $this->cart->update($variant, $request->integer('quantity'));

        if ($result['quantity'] === 0) {
            return redirect()->route('cart.index')->with('success', 'Item removed from your cart.');
        }

        return redirect()->route('cart.index')->with(
            $result['clamped'] ? 'error' : 'success',
            $result['clamped']
                ? "Only {$result['quantity']} left in that size — quantity adjusted."
                : 'Cart updated.',
        );
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $this->cart->remove($variant->id);

        return redirect()->route('cart.index')->with('success', 'Item removed from your cart.');
    }
}
