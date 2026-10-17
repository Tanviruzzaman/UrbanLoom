@php $brand = config('store.name'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $orders->count() === 1 ? $orders->first()->order_number : '('.$orders->count().' orders)' }} — {{ $brand }}</title>
    <style>
        /* Self-contained print styles — this page never loads the site CSS. */
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #f3f4f6;
            font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #111827;
        }
        .toolbar {
            max-width: 780px;
            margin: 0 auto 18px;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }
        .toolbar p { margin: 0; color: #6b7280; font-size: 13px; }
        .btn {
            appearance: none;
            border: 0;
            background: #334181;
            color: #fff;
            font: inherit;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 999px;
            cursor: pointer;
        }
        .btn.secondary { background: #fff; color: #111827; border: 1px solid #d1d5db; }

        .sheet {
            max-width: 780px;
            margin: 0 auto 20px;
            background: #fff;
            padding: 32px 36px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }

        .head { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #111827; padding-bottom: 16px; }
        .head h1 { margin: 0 0 4px; font-size: 20px; letter-spacing: -.01em; }
        .head .muted { color: #6b7280; font-size: 12px; line-height: 1.6; }
        .head .right { text-align: right; white-space: nowrap; }
        .head .label { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: #6b7280; margin: 0; }
        .head .ordno { margin: 2px 0 0; font-family: ui-monospace, Menlo, monospace; font-size: 16px; font-weight: 700; }

        .parties { display: flex; gap: 32px; margin-top: 20px; }
        .parties > div { flex: 1; }
        .parties h2 { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: #6b7280; margin: 0 0 6px; }
        .parties p { margin: 0; line-height: 1.65; }
        .parties .name { font-weight: 600; }

        table { width: 100%; border-collapse: collapse; margin-top: 22px; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; padding: 8px 0; border-bottom: 1px solid #d1d5db; }
        td { padding: 10px 0; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        th.r, td.r { text-align: right; }
        .variant { color: #6b7280; font-size: 12px; }

        .totals { margin-left: auto; margin-top: 14px; width: 260px; }
        .totals div { display: flex; justify-content: space-between; padding: 5px 0; }
        .totals .grand { border-top: 2px solid #111827; margin-top: 6px; padding-top: 10px; font-size: 16px; font-weight: 700; }

        .collect { margin-top: 18px; border: 2px solid #111827; border-radius: 6px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; }
        .collect .k { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; }
        .collect .v { font-size: 22px; font-weight: 700; font-family: ui-monospace, Menlo, monospace; }
        .collect.paid { border-color: #15803d; color: #15803d; }

        .notes { margin-top: 16px; padding: 10px 14px; background: #f9fafb; border-radius: 6px; font-size: 12px; }
        .foot { margin-top: 22px; padding-top: 14px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 11px; line-height: 1.7; }

        @media print {
            body { background: #fff; padding: 0; font-size: 12px; }
            .toolbar { display: none; }
            .sheet { max-width: none; margin: 0; padding: 0; border-radius: 0; box-shadow: none; }
            .sheet + .sheet { page-break-before: always; }
            @page { margin: 14mm; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <p>
        {{ $orders->count() }} {{ Str::plural('invoice', $orders->count()) }} ready.
        Put one inside each parcel.
    </p>
    <div>
        <button type="button" class="btn secondary" onclick="window.close()">Close</button>
        <button type="button" class="btn" onclick="window.print()">Print</button>
    </div>
</div>

@foreach ($orders as $order)
    <div class="sheet">

        <div class="head">
            <div>
                <h1>{{ $brand }}</h1>
                <p class="muted">
                    {{ config('store.address') }}<br>
                    {{ config('store.phone') }} · {{ config('store.email') }}
                </p>
            </div>
            <div class="right">
                <p class="label">Invoice</p>
                <p class="ordno">{{ $order->order_number }}</p>
                <p class="muted" style="margin-top:6px;">{{ $order->created_at->format('j M Y') }}</p>
            </div>
        </div>

        <div class="parties">
            <div>
                <h2>Deliver to</h2>
                <p>
                    <span class="name">{{ $order->customer_name }}</span><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->city }}<br>
                    <strong>{{ $order->customer_phone }}</strong>
                </p>
            </div>
            <div>
                <h2>Delivery</h2>
                <p>
                    Method: Cash on Delivery<br>
                    Courier: {{ $order->courier ?: 'Not booked' }}<br>
                    @if ($order->tracking_id)
                        Tracking: {{ $order->tracking_id }}<br>
                    @endif
                    Status: {{ ucfirst($order->status) }}
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="r">Price</th>
                    <th class="r">Qty</th>
                    <th class="r">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->product_name }}
                            @if ($item->variant_label)
                                <br><span class="variant">{{ $item->variant_label }}</span>
                            @endif
                        </td>
                        <td class="r">{{ bdt($item->price) }}</td>
                        <td class="r">{{ $item->quantity }}</td>
                        <td class="r">{{ bdt($item->line_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal</span><span>{{ bdt($order->subtotal) }}</span></div>
            <div><span>Delivery charge</span><span>{{ bdt($order->delivery_charge) }}</span></div>
            <div class="grand"><span>Total</span><span>{{ bdt($order->total) }}</span></div>
        </div>

        <div class="collect {{ $order->payment_status === 'paid' ? 'paid' : '' }}">
            <span class="k">
                {{ $order->payment_status === 'paid' ? 'Already paid — collect nothing' : 'Collect from customer' }}
            </span>
            <span class="v">{{ bdt($order->payment_status === 'paid' ? 0 : $order->total) }}</span>
        </div>

        @if ($order->notes)
            <div class="notes"><strong>Note:</strong> {{ $order->notes }}</div>
        @endif

        <div class="foot">
            Thank you for shopping with {{ $brand }}. Please check your parcel before paying the courier.
            Exchanges within 3 days on unworn items with tags attached — call {{ config('store.phone') }}
            and quote {{ $order->order_number }}.
        </div>
    </div>
@endforeach

</body>
</html>
