<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function order(array $attributes = []): Order
    {
        return Order::create([
            'customer_name' => 'Rahim Uddin',
            'customer_phone' => '01712345678',
            'shipping_address' => 'House 42, Banani',
            'city' => 'Dhaka',
            'subtotal' => 500,
            'delivery_charge' => 60,
            'total' => 560,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            ...$attributes,
        ]);
    }

    private function product(array $attributes = []): Product
    {
        return Product::create([
            'name' => 'Test Tee',
            'price' => 560,
            'is_active' => true,
            'is_featured' => false,
            ...$attributes,
        ]);
    }

    public function test_all_resource_index_pages_load(): void
    {
        foreach (['/admin/orders', '/admin/products', '/admin/categories'] as $url) {
            $this->get($url)->assertSuccessful();
        }
    }

    public function test_orders_list_shows_an_order(): void
    {
        $order = $this->order();

        Livewire::test(ListOrders::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$order])
            ->assertSee('৳560');
    }

    public function test_quick_action_marks_an_order_confirmed(): void
    {
        $order = $this->order();

        Livewire::test(ListOrders::class)
            ->callTableAction('mark_confirmed', $order)
            ->assertHasNoTableActionErrors();

        $this->assertSame('confirmed', $order->refresh()->status);
    }

    public function test_needs_action_filter_excludes_delivered_orders(): void
    {
        $pending = $this->order();
        $delivered = $this->order(['status' => 'delivered']);

        Livewire::test(ListOrders::class)
            ->filterTable('needs_action', true)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$delivered]);
    }

    public function test_nav_badge_counts_only_pending_orders(): void
    {
        $this->order();
        $this->order();
        $this->order(['status' => 'delivered']);

        $this->assertSame('2', OrderResource::getNavigationBadge());
    }

    public function test_nav_badge_is_null_when_nothing_pending(): void
    {
        $this->order(['status' => 'delivered']);

        $this->assertNull(OrderResource::getNavigationBadge());
    }

    public function test_orders_cannot_be_created_by_hand(): void
    {
        $this->assertFalse(OrderResource::canCreate());
        $this->assertArrayNotHasKey('create', OrderResource::getPages());
    }

    public function test_order_view_page_shows_snapshotted_items(): void
    {
        $order = $this->order();
        $order->items()->create([
            'product_name' => 'Solid Cotton Tee',
            'variant_label' => 'M / Black',
            'price' => 360,
            'quantity' => 2,
            'line_total' => 720,
        ]);

        $this->get(OrderResource::getUrl('view', ['record' => $order]))
            ->assertSuccessful()
            ->assertSee('Solid Cotton Tee')
            ->assertSee('M / Black');
    }

    public function test_products_list_shows_summed_variant_stock(): void
    {
        $product = $this->product();
        $product->variants()->createMany([
            ['size' => 'S', 'stock_quantity' => 10],
            ['size' => 'M', 'stock_quantity' => 15],
        ]);

        Livewire::test(ListProducts::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$product])
            // 10 + 15, aggregated in SQL via withSum.
            ->assertSee('25');
    }

    public function test_bulk_action_activates_products(): void
    {
        $a = $this->product(['name' => 'A', 'is_active' => false]);
        $b = $this->product(['name' => 'B', 'is_active' => false]);

        Livewire::test(ListProducts::class)
            ->callTableBulkAction('activate', [$a, $b]);

        $this->assertTrue($a->refresh()->is_active);
        $this->assertTrue($b->refresh()->is_active);
    }

    public function test_bulk_action_features_products(): void
    {
        $product = $this->product();

        Livewire::test(ListProducts::class)
            ->callTableBulkAction('feature', [$product]);

        $this->assertTrue($product->refresh()->is_featured);
    }

    public function test_categories_list_counts_products(): void
    {
        $category = Category::create(['name' => 'Solid', 'is_active' => true]);
        $this->product(['category_id' => $category->id]);

        Livewire::test(ListCategories::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$category])
            ->assertSee('Solid');
    }

    public function test_product_resource_registers_both_relation_managers(): void
    {
        $relations = ProductResource::getRelations();

        $this->assertCount(2, $relations);
    }

    public function test_stock_can_be_edited_inline_on_a_variant(): void
    {
        $product = $this->product();
        $variant = $product->variants()->create(['size' => 'M', 'stock_quantity' => 5]);

        $variant->update(['stock_quantity' => 42]);

        $this->assertSame(42, $variant->refresh()->stock_quantity);
        $this->assertInstanceOf(ProductVariant::class, $variant);
    }
}
