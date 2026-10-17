<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Printable invoice / packing slip for one order.
     */
    public function show(Order $order): View
    {
        return view('invoices.print', [
            'orders' => collect([$order->load('items')]),
        ]);
    }

    /**
     * Several invoices on one page, separated by page breaks — one print job
     * for a whole courier run.
     */
    public function batch(Request $request): View
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->take(100);

        $orders = Order::with('items')
            ->whereKey($ids)
            ->orderBy('id')
            ->get();

        abort_if($orders->isEmpty(), 404);

        return view('invoices.print', ['orders' => $orders]);
    }
}
