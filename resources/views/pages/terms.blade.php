<x-app-layout title="Terms &amp; Conditions">
    <x-page-body heading="Terms &amp; Conditions">

        <p class="text-sm text-neutral-500">Last updated: {{ date('F Y') }}</p>

        <p>
            These terms apply whenever you buy from {{ config('store.name') }}. Placing an order
            means you accept them.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Orders</h2>
        <p>
            Placing an order creates a request to purchase, not a completed sale. We confirm every
            order by phone before dispatch, so please give a number that can receive calls. If we
            cannot reach you after three attempts we may cancel the order.
        </p>
        <p>
            We reserve the right to decline an order — for example if an item is out of stock, the
            address is outside our courier's coverage, or a price was listed in error.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Prices and payment</h2>
        <p>
            All prices are in Bangladeshi Taka (৳) and include VAT where applicable. All orders are
            cash on delivery: you pay the courier the full amount shown on your order when the
            parcel arrives. We do not accept advance payment.
        </p>
        <p>
            Prices can change at any time, but the price shown when you placed your order is the
            price you pay.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Delivery charges</h2>
        <p>
            ৳{{ config('store.delivery.inside_dhaka') }} inside Dhaka and
            ৳{{ config('store.delivery.outside_dhaka') }} outside Dhaka, added to your order total.
            Orders usually arrive within 2–4 working days, though we cannot guarantee a specific
            date — delays caused by weather, strikes or courier backlogs are outside our control.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Stock</h2>
        <p>
            We try to keep stock accurate, but if an item sells out between your order and dispatch
            we will call you to arrange a replacement, a partial order, or a cancellation.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Exchanges and refunds</h2>
        <p>
            Unworn items with tags attached may be exchanged within 3 days of delivery. Full details
            are in our
            <a href="{{ route('pages.returns') }}" class="underline underline-offset-4">Return, Refund &amp; Cancellation Policy</a>.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Product descriptions</h2>
        <p>
            We photograph our products as accurately as we can, but screens vary and colours may
            look slightly different in person. Measurements are approximate and may vary by about
            half an inch between garments.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Using this site</h2>
        <p>
            All text, photographs and designs on this site belong to {{ config('store.name') }}.
            Please do not reuse them commercially without asking. Do not attempt to disrupt the
            site or place fraudulent orders.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Contact</h2>
        <p>
            Questions about these terms? Email {{ config('store.email') }} or call
            {{ config('store.phone') }}.
        </p>

        <p class="text-neutral-500">
            <em>Placeholder copy — have this reviewed before launch.</em>
        </p>
    </x-page-body>
</x-app-layout>
