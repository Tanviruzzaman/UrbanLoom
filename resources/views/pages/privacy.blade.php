<x-app-layout title="Privacy Policy">
    <x-page-body heading="Privacy Policy">

        <p class="text-sm text-neutral-500">Last updated: {{ date('F Y') }}</p>

        <p>
            This policy explains what {{ config('store.name') }} collects when you shop with us,
            why we collect it, and what we do with it.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">What we collect</h2>
        <p>
            When you place an order we collect your name, mobile number, delivery address and city.
            Your email address is optional — we ask for it only so we can send your order
            confirmation. If you contact us through the website we also keep the message you send.
        </p>
        <p>
            Your browser sends us ordinary technical information too, such as your IP address and
            the pages you visit, which we use to keep the site working and secure.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Why we collect it</h2>
        <ul class="list-inside list-disc space-y-1.5">
            <li>To pack and deliver your order</li>
            <li>To call you and confirm the order before dispatch</li>
            <li>To send your order confirmation, if you gave us an email address</li>
            <li>To handle exchanges, refunds and customer support</li>
            <li>To keep basic business records of what we sold</li>
        </ul>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Who we share it with</h2>
        <p>
            We share your name, phone number and address with our courier partner, purely so your
            parcel can reach you. That is the only routine sharing we do.
        </p>
        <p>
            We do not sell, rent or trade your information to anyone. We would only disclose it
            otherwise if the law required it.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Payment information</h2>
        <p>
            Every order is cash on delivery. We never ask for, never see, and never store card
            numbers or banking details. If we add online payment later, it will be handled by a
            licensed payment gateway and this policy will be updated first.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Cookies</h2>
        <p>
            We use a single session cookie to remember what is in your cart while you browse. It is
            not used for advertising and it disappears when your session ends.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Your choices</h2>
        <p>
            You can ask us what we hold about you, ask us to correct it, or ask us to delete it —
            email {{ config('store.email') }} or call {{ config('store.phone') }}. We may need to
            keep basic order records for accounting even after a deletion request.
        </p>

        <p class="text-neutral-500">
            <em>Placeholder copy — have this reviewed before launch.</em>
        </p>
    </x-page-body>
</x-app-layout>
