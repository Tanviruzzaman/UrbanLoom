<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    /**
     * Sizes every t-shirt is stocked in.
     */
    private const SIZES = ['S', 'M', 'L', 'XL'];

    /**
     * Seed demo categories, products, variants and images for the storefront.
     *
     * Everything is matched on its slug first, so running the seeder twice
     * tops the data up instead of duplicating it.
     */
    public function run(): void
    {
        $categories = $this->seedCategories();

        foreach ($this->products() as $data) {
            $product = $this->seedProduct($data, $categories[$data['category']]);

            $this->seedVariants($product, $data['color'] ?? null);
            $this->seedImage($product);
        }

        $this->command?->info('Seeded '.Category::count().' categories and '.Product::count().' products.');
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $names = ['New', 'Best Selling', 'Solid', 'Kids'];

        return collect($names)
            ->mapWithKeys(fn (string $name) => [
                $name => Category::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name, 'is_active' => true],
                ),
            ])
            ->all();
    }

    private function seedProduct(array $data, Category $category): Product
    {
        return Product::firstOrCreate(
            ['slug' => Str::slug($data['name'])],
            [
                'category_id' => $category->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'compare_at_price' => $data['compare_at_price'],
                'is_active' => true,
                'is_featured' => $data['featured'] ?? false,
            ],
        );
    }

    /**
     * Give the product one variant per size. Solid tees carry a colour too.
     */
    private function seedVariants(Product $product, ?string $color): void
    {
        if ($product->variants()->exists()) {
            return;
        }

        $variants = collect(self::SIZES)->map(fn (string $size) => [
            'size' => $size,
            'color' => $color,
            'sku' => $this->sku($product, $size, $color),
            'stock_quantity' => random_int(10, 50),
        ])->all();

        $product->variants()->createMany($variants);
    }

    private function seedImage(Product $product): void
    {
        if ($product->images()->exists()) {
            return;
        }

        $product->images()->create([
            'image_path' => 'products/placeholder.png',
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Build a stable, unique SKU such as "DROP-SHOULDER-TEE-BLACK-BLK-M".
     */
    private function sku(Product $product, string $size, ?string $color): string
    {
        return Str::upper(collect([
            rtrim(Str::limit(Str::slug($product->name), 28, ''), '-'),
            $color ? Str::substr($color, 0, 3) : null,
            $size,
        ])->filter()->implode('-'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(): array
    {
        $dropShoulder = ['price' => 560, 'compare_at_price' => 590];
        $solid = ['price' => 360, 'compare_at_price' => 390];

        return [
            [
                ...$dropShoulder,
                'name' => 'Drop Shoulder Tee — Midnight Black',
                'category' => 'New',
                'featured' => true,
                'description' => 'Heavyweight 100% cotton drop-shoulder tee with a relaxed fit and ribbed neckline. Pre-shrunk and built for Dhaka heat.',
            ],
            [
                ...$dropShoulder,
                'name' => 'Drop Shoulder Tee — Off White',
                'category' => 'New',
                'featured' => true,
                'description' => 'Clean off-white drop-shoulder tee in soft combed cotton. Boxy cut with a slightly dropped sleeve.',
            ],
            [
                ...$dropShoulder,
                'name' => 'Oversized Drop Shoulder Tee — Charcoal',
                'category' => 'Best Selling',
                'featured' => true,
                'description' => 'Our best selling oversized fit in charcoal grey. Thick 180 GSM cotton that holds its shape wash after wash.',
            ],
            [
                ...$dropShoulder,
                'name' => 'Drop Shoulder Tee — Sand Beige',
                'category' => 'Best Selling',
                'description' => 'Warm sand beige drop-shoulder tee. Breathable single jersey knit, perfect for everyday wear.',
            ],
            [
                ...$solid,
                'name' => 'Solid Cotton Tee — Black',
                'category' => 'Solid',
                'color' => 'Black',
                'featured' => true,
                'description' => 'The everyday black tee. Regular fit, 100% cotton, colour-locked so it stays deep black.',
            ],
            [
                ...$solid,
                'name' => 'Solid Cotton Tee — Maroon',
                'category' => 'Solid',
                'color' => 'Maroon',
                'description' => 'Rich maroon solid tee in a comfortable regular fit. Reinforced shoulder seams for durability.',
            ],
            [
                ...$solid,
                'name' => 'Solid Cotton Tee — Olive',
                'category' => 'Solid',
                'color' => 'Olive',
                'description' => 'Muted olive green solid tee. Soft hand-feel cotton with a classic crew neck.',
            ],
            [
                'name' => 'Kids Cotton Tee — Sky Blue',
                'category' => 'Kids',
                'price' => 320,
                'compare_at_price' => 350,
                'description' => 'Lightweight sky blue tee for kids. Extra-soft cotton with a tagless neck label so it never itches.',
            ],
        ];
    }
}
