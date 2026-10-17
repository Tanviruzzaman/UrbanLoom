<x-app-layout title="Return, Refund &amp; Cancellation Policy">
    <x-page-body heading="Return, Refund &amp; Cancellation Policy">

        <p class="rounded-lg border border-neutral-200 bg-neutral-50 px-5 py-4 text-sm">
            <span class="font-medium text-neutral-900">In short:</span>
            check your parcel before paying the courier. If something is wrong, call us within
            3 days on {{ config('store.phone') }} and we will make it right.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Cancelling an order</h2>
        <p>
            You can cancel any order free of charge before it is handed to the courier — just call
            or message us with your order number. Once a parcel has been dispatched it cannot be
            cancelled, but you may still refuse it at the door.
        </p>
        <p>
            We may cancel an order ourselves if we cannot reach you by phone after three attempts,
            if the delivery address is outside our courier's coverage, or if an item sells out
            before dispatch. We will always call you first.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Checking your parcel</h2>
        <p>
            Our courier allows you to open and check the parcel before paying. Please do — confirm
            the size, colour and condition while the delivery person is still with you. It is much
            easier to resolve a problem at that moment than afterwards.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Exchanges</h2>
        <p>
            We exchange any unworn, unwashed item with its tags still attached within
            <span class="font-medium text-neutral-900">3 days of delivery</span>, subject to stock.
            Size swaps are the most common request and we keep stock aside for them wherever we can.
        </p>
        <p>
            Return delivery charges are paid by the customer, unless the fault is ours — a wrong
            item, a wrong size sent, or a manufacturing defect. In those cases we cover shipping
            both ways.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Refunds</h2>
        <p>
            Because every order is cash on delivery, the simplest refund is to refuse the parcel at
            the door — you pay nothing at all.
        </p>
        <p>
            If you have already paid and we agree a refund, we send it by bKash or Nagad to the
            number you ordered from, within 7 working days of receiving the returned item. The
            original delivery charge is refunded only when the fault was ours.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">What we cannot accept</h2>
        <ul class="list-inside list-disc space-y-1.5">
            <li>Items that have been worn, washed, ironed or altered</li>
            <li>Items returned without their original tags and packaging</li>
            <li>Items reported more than 3 days after delivery</li>
            <li>Damage caused by normal wear, incorrect washing, or accidents</li>
        </ul>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">How to start a return</h2>
        <ol class="list-inside list-decimal space-y-1.5">
            <li>Call or WhatsApp us on {{ config('store.phone') }}, or use our
                <a href="{{ route('pages.contact') }}" class="underline underline-offset-4">contact form</a>.</li>
            <li>Have your order number ready — it is in your confirmation email.</li>
            <li>Tell us what is wrong and send a photo if the item is damaged.</li>
            <li>We confirm the exchange or refund and arrange courier pickup.</li>
        </ol>

        <p class="text-neutral-500">
            <em>Placeholder copy — have this reviewed against your courier's terms and current
            consumer rules before launch.</em>
        </p>
    </x-page-body>
</x-app-layout>
