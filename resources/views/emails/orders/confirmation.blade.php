@php
    use App\Support\Money;

    $brand = config('store.name');
    // Email clients ignore <style> blocks and modern CSS, so everything here is
    // table-based with inline styles.
    $wrap = 'font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;';
    $muted = 'color:#6b7280;';
    $cell = 'padding:12px 0; border-bottom:1px solid #e5e7eb; vertical-align:top;';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $order->order_number }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; {{ $wrap }}">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 12px;">
    <tr>
        <td align="center">

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden;">

                {{-- Header --}}
                <tr>
                    <td style="background-color:#111827; padding:28px 32px;">
                        <p style="margin:0; font-size:20px; font-weight:600; color:#ffffff;">{{ $brand }}</p>
                        <p style="margin:6px 0 0; font-size:13px; color:#9ca3af;">{{ config('store.tagline') }}</p>
                    </td>
                </tr>

                {{-- Intro --}}
                <tr>
                    <td style="padding:32px 32px 8px;">
                        <h1 style="margin:0 0 12px; font-size:22px; font-weight:600; color:#111827;">
                            Thank you for your order!
                        </h1>
                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#374151;">
                            Hi {{ $order->customer_name }}, we have received your order and will call you shortly
                            to confirm it before dispatch.
                        </p>

                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f9fafb; border-radius:8px; margin:8px 0 4px;">
                            <tr>
                                <td style="padding:16px 20px;">
                                    <p style="margin:0; font-size:12px; text-transform:uppercase; letter-spacing:0.05em; {{ $muted }}">Order number</p>
                                    <p style="margin:4px 0 0; font-size:18px; font-weight:600; color:#111827; font-family:ui-monospace, SFMono-Regular, Menlo, monospace;">
                                        {{ $order->order_number }}
                                    </p>
                                    <p style="margin:10px 0 0; font-size:13px; {{ $muted }}">
                                        Placed on {{ $order->created_at->format('d M Y, g:i A') }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Items --}}
                <tr>
                    <td style="padding:20px 32px 0;">
                        <h2 style="margin:0 0 4px; font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#111827;">
                            Order summary
                        </h2>

                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="font-size:14px;">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td style="{{ $cell }}">
                                        <span style="color:#111827; font-weight:500;">{{ $item->product_name }}</span>
                                        @if ($item->variant_label)
                                            <br><span style="font-size:13px; {{ $muted }}">{{ $item->variant_label }}</span>
                                        @endif
                                        <br><span style="font-size:13px; {{ $muted }}">
                                            {{ Money::bdt($item->price) }} &times; {{ $item->quantity }}
                                        </span>
                                    </td>
                                    <td align="right" style="{{ $cell }} color:#111827; font-weight:500; white-space:nowrap;">
                                        {{ Money::bdt($item->line_total) }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr>
                                <td style="padding:14px 0 4px; {{ $muted }}">Subtotal</td>
                                <td align="right" style="padding:14px 0 4px; color:#111827;">{{ Money::bdt($order->subtotal) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0; {{ $muted }}">Delivery charge</td>
                                <td align="right" style="padding:4px 0; color:#111827;">{{ Money::bdt($order->delivery_charge) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 0 0; border-top:2px solid #111827; font-size:16px; font-weight:600; color:#111827;">
                                    Total
                                </td>
                                <td align="right" style="padding:14px 0 0; border-top:2px solid #111827; font-size:16px; font-weight:600; color:#111827;">
                                    {{ Money::bdt($order->total) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Payment method --}}
                <tr>
                    <td style="padding:24px 32px 0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#ecfdf5; border-radius:8px;">
                            <tr>
                                <td style="padding:16px 20px;">
                                    <p style="margin:0; font-size:14px; font-weight:600; color:#065f46;">
                                        Payment method: Cash on Delivery
                                    </p>
                                    <p style="margin:6px 0 0; font-size:13px; line-height:1.6; color:#047857;">
                                        Please keep {{ Money::bdt($order->total) }} ready to pay the courier when your parcel arrives.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Delivery address --}}
                <tr>
                    <td style="padding:24px 32px 0;">
                        <h2 style="margin:0 0 10px; font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#111827;">
                            Delivery address
                        </h2>
                        <p style="margin:0; font-size:14px; line-height:1.7; color:#374151;">
                            {{ $order->customer_name }}<br>
                            {{ $order->shipping_address }}<br>
                            {{ $order->city }}<br>
                            {{ $order->customer_phone }}
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:32px;">
                        <p style="margin:0 0 6px; font-size:13px; line-height:1.7; {{ $muted }}">
                            Questions about this order? Reply to this email or call us at {{ config('store.phone') }},
                            quoting your order number.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background-color:#f9fafb; padding:20px 32px; border-top:1px solid #e5e7eb;">
                        <p style="margin:0; font-size:12px; {{ $muted }}">
                            &copy; {{ date('Y') }} {{ $brand }} &middot; {{ config('store.address') }}
                        </p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>
