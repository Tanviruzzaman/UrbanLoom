<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

/**
 * Creates one realistic COD order so the confirmation email, the Filament
 * Orders resource and the courier action have something to work against
 * until the storefront checkout is built.
 */
class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::with('product')->inRandomOrder()->take(3)->get();

        if ($variants->isEmpty()) {
            $this->command?->error('No product variants found. Run: php artisan db:seed --class=StoreSeeder');

            return;
        }

        $order = new Order([
            'customer_name' => 'Rahim Uddin',
            'customer_phone' => '01712345678',
            'customer_email' => 'rahim@example.com',
            'shipping_address' => 'House 42, Road 7, Block C, Banani',
            'city' => 'Dhaka',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'notes' => 'Please call before delivery.',
        ]);

        $order->delivery_charge = config('store.delivery.inside_dhaka');
        $order->subtotal = 0;
        $order->total = 0;
        $order->save();

        $subtotal = 0;

        foreach ($variants as $index => $variant) {
            $quantity = $index + 1;
            $price = (float) $variant->product->price;
            $lineTotal = $price * $quantity;
            $subtotal += $lineTotal;

            $order->items()->create([
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name,
                'variant_label' => $variant->label,
                'price' => $price,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ]);
        }

        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal + (float) $order->delivery_charge,
        ]);

        $this->command?->info("Demo order created: {$order->order_number} (total ".$order->total.' BDT)');
    }
}
