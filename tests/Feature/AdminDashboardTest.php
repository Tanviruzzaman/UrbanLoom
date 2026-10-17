<?php

namespace Tests\Feature;

use App\Filament\Widgets\LowStockVariants;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\RecentOrders;
use App\Filament\Widgets\RepeatCustomers;
use App\Filament\Widgets\StoreStatsOverview;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function order(array $attributes = []): Order
    {
        return Order::create([
            'customer_name' => 'Test Customer',
            'customer_phone' => '01712345678',
            'shipping_address' => 'Somewhere',
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

    public function test_dashboard_loads_for_an_authenticated_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertSuccessful();
    }

    public function test_stats_widget_totals_todays_orders_and_revenue(): void
    {
        $this->actingAs($this->admin());

        $this->order(['total' => 560]);
        $this->order(['total' => 440]);

        Livewire::test(StoreStatsOverview::class)
            ->assertSuccessful()
            ->assertSee("Today's orders")
            ->assertSee('2')
            ->assertSee('৳1,000');
    }

    public function test_stats_widget_excludes_cancelled_orders_from_revenue(): void
    {
        $this->actingAs($this->admin());

        $this->order(['total' => 560]);
        $this->order(['total' => 5000, 'status' => 'cancelled']);

        Livewire::test(StoreStatsOverview::class)
            ->assertSuccessful()
            // Revenue is ৳560, not ৳5,560.
            ->assertSee('৳560')
            ->assertDontSee('৳5,560');
    }

    public function test_stats_widget_counts_pending_orders(): void
    {
        $this->actingAs($this->admin());

        $this->order();
        $this->order(['status' => 'delivered']);

        Livewire::test(StoreStatsOverview::class)
            ->assertSuccessful()
            ->assertSee('Pending orders')
            ->assertSee('Waiting to be confirmed');
    }

    public function test_recent_orders_widget_lists_latest_orders(): void
    {
        $this->actingAs($this->admin());

        $order = $this->order(['customer_name' => 'Nusrat Jahan']);

        Livewire::test(RecentOrders::class)
            ->assertSuccessful()
            ->assertSee('Recent orders')
            ->assertSee('Nusrat Jahan')
            ->assertSee($order->order_number)
            ->assertSee('৳560');
    }

    public function test_recent_orders_widget_is_capped_at_ten(): void
    {
        $this->actingAs($this->admin());

        foreach (range(1, 12) as $i) {
            $this->order(['customer_name' => "Customer {$i}"]);
        }

        Livewire::test(RecentOrders::class)
            ->assertSuccessful()
            ->assertSee('Customer 12')
            ->assertDontSee('Customer 1<');
    }

    public function test_recent_orders_widget_handles_an_empty_store(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(RecentOrders::class)
            ->assertSuccessful()
            ->assertSee('No orders yet');
    }

    private function variant(int $stock, string $productName = 'Test Tee'): ProductVariant
    {
        $product = Product::create([
            'name' => $productName,
            'price' => 560,
            'is_active' => true,
        ]);

        return $product->variants()->create(['size' => 'M', 'stock_quantity' => $stock]);
    }

    public function test_low_stock_widget_lists_only_variants_at_or_below_five(): void
    {
        $this->actingAs($this->admin());

        $low = $this->variant(3, 'Almost Gone Tee');
        $healthy = $this->variant(40, 'Well Stocked Tee');

        Livewire::test(LowStockVariants::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$low])
            ->assertCanNotSeeTableRecords([$healthy]);
    }

    public function test_low_stock_widget_includes_the_boundary_and_zero(): void
    {
        $this->actingAs($this->admin());

        $exactlyFive = $this->variant(5, 'Boundary Tee');
        $soldOut = $this->variant(0, 'Sold Out Tee');

        Livewire::test(LowStockVariants::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$exactlyFive, $soldOut])
            ->assertSee('Out of stock');
    }

    public function test_low_stock_widget_is_empty_when_stock_is_healthy(): void
    {
        $this->actingAs($this->admin());

        $this->variant(40);

        Livewire::test(LowStockVariants::class)
            ->assertSuccessful()
            ->assertSee('Stock levels are healthy');
    }

    /**
     * ChartWidget::getCachedData() is protected; read it without widening
     * the widget API just for tests.
     *
     * @return array<string, mixed>
     */
    private function chartData(OrdersChart $widget): array
    {
        $method = new \ReflectionMethod($widget, 'getCachedData');
        $method->setAccessible(true);

        return $method->invoke($widget);
    }

    public function test_chart_returns_fourteen_days_including_empty_ones(): void
    {
        $this->actingAs($this->admin());

        $this->order();

        $data = $this->chartData(
            Livewire::test(OrdersChart::class)->assertSuccessful()->instance()
        );

        $this->assertCount(14, $data['labels']);
        $this->assertCount(14, $data['datasets'][0]['data']);
        // Today is the last bucket and holds the single order.
        $this->assertSame(1.0, end($data['datasets'][0]['data']));
    }

    public function test_chart_revenue_filter_excludes_cancelled_orders(): void
    {
        $this->actingAs($this->admin());

        $this->order(['total' => 560]);
        $this->order(['total' => 9999, 'status' => 'cancelled']);

        $data = $this->chartData(
            Livewire::test(OrdersChart::class)->set('filter', 'revenue')->instance()
        );

        // Revenue counts 560 only; the order count still sees both.
        $this->assertSame(560.0, end($data['datasets'][0]['data']));
        $this->assertSame('Revenue (৳)', $data['datasets'][0]['label']);
    }

    /**
     * Regression: the grouped query behind this widget crashed on MySQL under
     * ONLY_FULL_GROUP_BY, because Filament appends `ORDER BY orders.id` for the
     * record key. SQLite does not enforce that rule, which is why the suite now
     * runs on MySQL.
     */
    public function test_repeat_customers_widget_renders_its_grouped_query(): void
    {
        $this->actingAs($this->admin());

        // Two orders from one phone, one from another.
        $this->order(['customer_name' => 'Repeat Buyer', 'customer_phone' => '01711111111', 'total' => 500]);
        $this->order(['customer_name' => 'Repeat Buyer', 'customer_phone' => '01711111111', 'total' => 700]);
        $this->order(['customer_name' => 'One Timer', 'customer_phone' => '01722222222']);

        Livewire::test(RepeatCustomers::class)
            ->assertSuccessful()
            ->assertSee('Repeat Buyer')
            ->assertDontSee('One Timer')
            // 500 + 700 lifetime value.
            ->assertSee('৳1,200');
    }

    public function test_repeat_customers_widget_can_be_sorted_without_crashing(): void
    {
        $this->actingAs($this->admin());

        $this->order(['customer_phone' => '01711111111']);
        $this->order(['customer_phone' => '01711111111']);

        // Sorting is what triggered the original ORDER BY failure.
        Livewire::test(RepeatCustomers::class)
            ->sortTable('order_count')
            ->assertSuccessful()
            ->sortTable('lifetime_value')
            ->assertSuccessful();
    }

    public function test_chart_counts_cancelled_orders_in_the_orders_filter(): void
    {
        $this->actingAs($this->admin());

        $this->order();
        $this->order(['status' => 'cancelled']);

        $data = $this->chartData(Livewire::test(OrdersChart::class)->instance());

        $this->assertSame(2.0, end($data['datasets'][0]['data']));
    }
}
