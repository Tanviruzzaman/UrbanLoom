<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogueGuardsTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::create([
            'name' => 'Drop Shoulder Tee',
            'description' => 'Heavyweight cotton, relaxed fit.',
            'price' => 560,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function variant(Product $product, int $stock = 10): ProductVariant
    {
        return $product->variants()->create(['size' => 'M', 'stock_quantity' => $stock]);
    }

    /*
    |--------------------------------------------------------------------------
    | Withdrawn products
    |--------------------------------------------------------------------------
    */

    public function test_a_withdrawn_product_is_flagged_unavailable_in_the_cart(): void
    {
        $product = $this->product();
        $variant = $this->variant($product);

        $cart = app(CartService::class);
        $cart->add($variant, 1);
        $this->assertTrue($cart->items()->first()->available);

        $product->update(['is_active' => false]);

        $this->assertFalse($cart->items()->first()->available);
        $this->assertTrue($cart->hasUnavailableItems());
    }

    public function test_checkout_page_redirects_when_an_item_is_withdrawn(): void
    {
        $product = $this->product();
        $variant = $this->variant($product);

        app(CartService::class)->add($variant, 1);
        $product->update(['is_active' => false]);

        $this->get('/checkout')
            ->assertRedirect('/cart')
            ->assertSessionHas('error');
    }

    public function test_an_order_cannot_be_placed_for_a_withdrawn_product(): void
    {
        $product = $this->product();
        $variant = $this->variant($product);

        app(CartService::class)->add($variant, 1);
        $product->update(['is_active' => false]);

        $this->post('/checkout', [
            'customer_name' => 'Rahim Uddin',
            'customer_phone' => '01712345678',
            'city' => 'Dhaka',
            'shipping_address' => 'House 42',
        ])->assertRedirect('/cart');

        $this->assertSame(0, \App\Models\Order::count());
        // Stock must not have moved either.
        $this->assertSame(10, $variant->refresh()->stock_quantity);
    }

    public function test_payable_subtotal_excludes_withdrawn_items(): void
    {
        $good = $this->product(['name' => 'Available Tee']);
        $gone = $this->product(['name' => 'Withdrawn Tee', 'price' => 1000]);

        $cart = app(CartService::class);
        $cart->add($this->variant($good), 1);
        $cart->add($this->variant($gone), 1);

        $gone->update(['is_active' => false]);

        $this->assertSame(1560.0, $cart->subtotal());
        $this->assertSame(560.0, $cart->payableSubtotal());
    }

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_a_product_name(): void
    {
        $category = Category::create(['name' => 'Tops', 'is_active' => true]);
        $this->product(['name' => 'Pearl Drop Earrings', 'category_id' => $category->id]);
        $this->product(['name' => 'Drop Shoulder Tee', 'category_id' => $category->id]);

        $this->get('/shop?q=Pearl')
            ->assertSuccessful()
            ->assertSee('Pearl Drop Earrings')
            ->assertDontSee('Drop Shoulder Tee');
    }

    public function test_search_matches_the_description(): void
    {
        $this->product(['name' => 'Solid Tee', 'description' => 'Made from breathable linen.']);
        $this->product(['name' => 'Other Tee', 'description' => 'Heavy cotton.']);

        $this->get('/shop?q=linen')
            ->assertSuccessful()
            ->assertSee('Solid Tee')
            ->assertDontSee('Other Tee');
    }

    public function test_search_does_not_find_withdrawn_products(): void
    {
        $this->product(['name' => 'Hidden Tee', 'is_active' => false]);

        $this->get('/shop?q=Hidden')
            ->assertSuccessful()
            ->assertDontSee('Hidden Tee');
    }

    public function test_a_search_with_no_results_says_so(): void
    {
        $this->product();

        $this->get('/shop?q=zzzznothing')
            ->assertSuccessful()
            ->assertSee('Nothing matched');
    }

    public function test_like_wildcards_are_treated_as_literal_text(): void
    {
        $this->product(['name' => 'Plain Tee']);

        // A bare % must not match everything.
        $this->get('/shop?q=%')
            ->assertSuccessful()
            ->assertDontSee('Plain Tee');
    }

    public function test_search_combines_with_a_category_filter(): void
    {
        $tops = Category::create(['name' => 'Tops', 'is_active' => true]);
        $jewellery = Category::create(['name' => 'Jewellery', 'is_active' => true]);

        $this->product(['name' => 'Cotton Top', 'category_id' => $tops->id]);
        $this->product(['name' => 'Cotton Bracelet', 'category_id' => $jewellery->id]);

        $this->get('/shop?q=Cotton&category=jewellery')
            ->assertSuccessful()
            ->assertSee('Cotton Bracelet')
            ->assertDontSee('Cotton Top');
    }
}
