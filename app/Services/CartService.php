<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Session\Session;

/**
 * Session-backed shopping cart, keyed by product variant id.
 *
 * Only variant ids and quantities are persisted in the session — prices, names
 * and stock are always re-read from the database, so a stale session can never
 * sell at an old price or oversell a variant.
 */
class CartService
{
    public function __construct(private Session $session) {}

    private function key(): string
    {
        return config('store.cart.session_key', 'cart');
    }

    private function maxPerItem(): int
    {
        return (int) config('store.cart.max_quantity_per_item', 10);
    }

    /**
     * Raw session contents: [variant_id => quantity].
     *
     * @return array<int, int>
     */
    private function raw(): array
    {
        return $this->session->get($this->key(), []);
    }

    private function persist(array $items): void
    {
        $this->session->put($this->key(), $items);
    }

    /**
     * Add a quantity of a variant, clamped to what is actually in stock.
     *
     * @return array{added: int, clamped: bool}
     */
    public function add(ProductVariant $variant, int $quantity): array
    {
        $items = $this->raw();
        $current = $items[$variant->id] ?? 0;

        $ceiling = min($variant->stock_quantity, $this->maxPerItem());
        $desired = $current + $quantity;
        $final = max(0, min($desired, $ceiling));

        if ($final === 0) {
            unset($items[$variant->id]);
        } else {
            $items[$variant->id] = $final;
        }

        $this->persist($items);

        return [
            'added' => $final - $current,
            'clamped' => $desired > $ceiling,
        ];
    }

    /**
     * Set an exact quantity for a variant. Zero removes the line.
     *
     * @return array{quantity: int, clamped: bool}
     */
    public function update(ProductVariant $variant, int $quantity): array
    {
        $items = $this->raw();

        $ceiling = min($variant->stock_quantity, $this->maxPerItem());
        $final = max(0, min($quantity, $ceiling));

        if ($final === 0) {
            unset($items[$variant->id]);
        } else {
            $items[$variant->id] = $final;
        }

        $this->persist($items);

        return [
            'quantity' => $final,
            'clamped' => $quantity > $ceiling,
        ];
    }

    public function remove(int $variantId): void
    {
        $items = $this->raw();
        unset($items[$variantId]);
        $this->persist($items);
    }

    public function clear(): void
    {
        $this->session->forget($this->key());
    }

    /**
     * Hydrated cart lines. Variants that have been deleted since they were
     * added are dropped from the session on the way out.
     *
     * @return Collection<int, object>
     */
    public function items(): Collection
    {
        $items = $this->raw();

        if ($items === []) {
            return collect();
        }

        $variants = ProductVariant::with(['product.primaryImage'])
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        // Prune anything that no longer exists or lost its product.
        $missing = array_diff(array_keys($items), $variants->keys()->all());

        foreach ($variants as $variant) {
            if (! $variant->product) {
                $missing[] = $variant->id;
            }
        }

        if ($missing !== []) {
            $this->persist(array_diff_key($items, array_flip($missing)));
            $items = $this->raw();
        }

        return collect($items)
            ->map(function (int $quantity, int $variantId) use ($variants) {
                $variant = $variants->get($variantId);

                if (! $variant || ! $variant->product) {
                    return null;
                }

                $price = (float) $variant->product->price;

                return (object) [
                    'variant' => $variant,
                    'product' => $variant->product,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'line_total' => $price * $quantity,
                    'max_quantity' => min($variant->stock_quantity, $this->maxPerItem()),
                    'in_stock' => $variant->stock_quantity >= $quantity,
                    // A product withdrawn from sale after it was added stays
                    // visible in the cart, but cannot be checked out.
                    'available' => (bool) $variant->product->is_active,
                ];
            })
            ->filter()
            ->values();
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    /**
     * Total number of units in the cart (used for the header badge).
     */
    public function count(): int
    {
        return (int) array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    /**
     * True when any line now exceeds available stock — checkout blocks on this.
     */
    public function hasStockIssues(): bool
    {
        return $this->items()->contains(fn ($line) => ! $line->in_stock);
    }

    /**
     * True when any line is a product that has since been withdrawn from sale.
     */
    public function hasUnavailableItems(): bool
    {
        return $this->items()->contains(fn ($line) => ! $line->available);
    }

    /**
     * Drop withdrawn products — used when the customer chooses to clear them.
     */
    public function removeUnavailable(): int
    {
        $removed = 0;

        foreach ($this->items() as $line) {
            if (! $line->available) {
                $this->remove($line->variant->id);
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Total of the lines that can actually be bought right now.
     */
    public function payableSubtotal(): float
    {
        return (float) $this->items()
            ->filter(fn ($line) => $line->available)
            ->sum('line_total');
    }
}
