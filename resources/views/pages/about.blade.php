<x-app-layout title="About Us">
    <x-page-body heading="About Us">

        <p class="text-lg leading-relaxed text-neutral-800">
            {{ config('store.name') }} is a Bangladeshi clothing label built around one idea:
            a good t-shirt should not be expensive.
        </p>

        <p>
            We started because buying a decent everyday tee here usually meant choosing between
            cheap fabric that lost its shape after three washes, or an imported brand priced far
            beyond what it was worth. We thought there was room for something in between — made
            locally, honestly priced, and built to survive Dhaka heat and Dhaka laundry.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">What we make</h2>
        <p>
            Everything we sell is cut and stitched in Bangladesh from 100% cotton single jersey.
            Our drop-shoulder tees are heavyweight at around 180 GSM, pre-shrunk so they keep their
            fit, with ribbed necklines and reinforced shoulder seams. Our solid tees are a lighter,
            regular cut for everyday wear.
        </p>
        <p>
            We keep the range deliberately small. Fewer designs means we can buy better fabric,
            check every batch properly, and keep sizes in stock rather than running out a week
            after launch.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">How we sell</h2>
        <p>
            We sell direct to you — no middlemen, no retail markup. That is the whole reason the
            price works. Every order ships cash on delivery anywhere in Bangladesh, so you pay only
            once the parcel is in your hands and you have checked it.
        </p>
        <p>
            Delivery is ৳{{ config('store.delivery.inside_dhaka') }} inside Dhaka and
            ৳{{ config('store.delivery.outside_dhaka') }} everywhere else, and orders typically
            arrive within 2–4 days. If a size does not fit, you can
            <a href="{{ route('pages.returns') }}" class="underline underline-offset-4">exchange it within 3 days</a>.
        </p>

        <h2 class="pt-2 text-lg font-semibold text-neutral-900">Talk to us</h2>
        <p>
            We are a small team and we answer our own phone. Call {{ config('store.phone') }}
            between 10:00 and 20:00, or
            <a href="{{ route('pages.contact') }}" class="underline underline-offset-4">send us a message</a>.
            If something goes wrong with an order, tell us — we would much rather fix it than lose you.
        </p>

        <p class="text-neutral-500">
            <em>Placeholder copy — replace with your own story before launch.</em>
        </p>
    </x-page-body>
</x-app-layout>
