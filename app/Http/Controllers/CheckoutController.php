<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderNotificationService;
use App\Support\Delivery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

use function Illuminate\Support\defer;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    /**
     * Checkout form with the order summary.
     */
    public function create(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        if ($this->cart->hasUnavailableItems()) {
            return redirect()->route('cart.index')
                ->with('error', 'One or more items in your cart are no longer available. Please remove them to continue.');
        }

        return view('storefront.checkout', [
            'lines' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
            'cities' => Delivery::cities(),
        ]);
    }

    /**
     * Place a Cash on Delivery order.
     */
    public function store(StoreOrderRequest $request, OrderNotificationService $notifier): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $data = $request->validated();

        try {
            $order = DB::transaction(function () use ($data) {
                $lines = $this->cart->items();

                if ($lines->isEmpty()) {
                    throw new \RuntimeException('Your cart is empty.');
                }

                // Lock the variants for the duration of the transaction so two
                // shoppers cannot claim the last item at the same time.
                $variants = ProductVariant::query()
                    ->whereIn('id', $lines->pluck('variant.id'))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($lines as $line) {
                    $variant = $variants->get($line->variant->id);

                    if (! $variant || $variant->stock_quantity < $line->quantity) {
                        throw new \RuntimeException(
                            "Sorry, \"{$line->product->name}\" ({$line->variant->label}) no longer has enough stock."
                        );
                    }

                    // Re-checked here, not just in the cart: the product could
                    // have been withdrawn between loading the page and submitting.
                    if (! $line->available) {
                        throw new \RuntimeException(
                            "Sorry, \"{$line->product->name}\" is no longer available. Please remove it from your cart."
                        );
                    }
                }

                $subtotal = (float) $lines->sum('line_total');
                $deliveryCharge = Delivery::chargeFor($data['city']);

                $order = Order::create([
                    ...$data,
                    'subtotal' => $subtotal,
                    'delivery_charge' => $deliveryCharge,
                    'total' => $subtotal + $deliveryCharge,
                    'payment_method' => 'cod',
                    'payment_status' => 'unpaid',
                    'status' => 'pending',
                ]);

                foreach ($lines as $line) {
                    // Snapshot name and price: the order must not change if the
                    // product is renamed or repriced later.
                    $order->items()->create([
                        'product_id' => $line->product->id,
                        'product_variant_id' => $line->variant->id,
                        'product_name' => $line->product->name,
                        'variant_label' => $line->variant->label,
                        'price' => $line->unit_price,
                        'quantity' => $line->quantity,
                        'line_total' => $line->line_total,
                    ]);

                    $variants->get($line->variant->id)->decrement('stock_quantity', $line->quantity);
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $this->cart->clear();

        /*
         * Email + SMS are sent *after* the response reaches the browser, so a
         * slow SMTP or SMS gateway never makes the customer wait on the
         * thank-you page. defer() needs no queue worker, which matters on the
         * shared hosting this will run on.
         *
         * The order is already committed and the service swallows its own
         * failures, so nothing here can lose a sale.
         */
        defer(fn () => $notifier->orderPlaced($order));

        return redirect()->route('checkout.thankYou', $order);
    }

    /**
     * Order confirmation page.
     */
    public function thankYou(Order $order): View
    {
        return view('storefront.thank-you', [
            'order' => $order->load('items'),
        ]);
    }
}
