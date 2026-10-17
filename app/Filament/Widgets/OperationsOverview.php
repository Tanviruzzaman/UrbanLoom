<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * The three numbers that decide whether a cash-on-delivery shop stays solvent:
 * what you must dispatch, what the courier still owes you, and how much of what
 * you ship comes back.
 */
class OperationsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Confirmed but not yet shipped — today's packing list.
        $toPack = Order::query()->where('status', 'confirmed')->count();

        // Shipped and still unpaid: cash the courier is holding for you.
        $withCourier = Order::query()
            ->where('status', 'shipped')
            ->where('payment_status', 'unpaid')
            ->sum('total');

        $withCourierCount = Order::query()
            ->where('status', 'shipped')
            ->where('payment_status', 'unpaid')
            ->count();

        /*
         * Return rate over the last 30 days.
         *
         * Only dispatched parcels count: an order cancelled before it was
         * handed to a courier cost nothing, so including it would understate
         * the damage that real returns do to the margin.
         */
        $since = now()->subDays(30);

        $dispatched = Order::query()
            ->where('created_at', '>=', $since)
            ->whereIn('status', ['shipped', 'delivered', 'returned'])
            ->count();

        $returned = Order::query()
            ->where('created_at', '>=', $since)
            ->where('status', 'returned')
            ->count();

        $rate = $dispatched > 0 ? ($returned / $dispatched) * 100 : 0;

        return [
            Stat::make('To pack today', number_format($toPack))
                ->description($toPack > 0 ? 'Confirmed, waiting for the courier' : 'Nothing waiting to go out')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color($toPack > 0 ? 'info' : 'gray'),

            Stat::make('Cash with courier', bdt($withCourier))
                ->description($withCourierCount.' '.str('parcel')->plural($withCourierCount).' shipped, not yet remitted')
                ->descriptionIcon('heroicon-m-truck')
                ->color($withCourier > 0 ? 'warning' : 'gray'),

            Stat::make('Return rate (30 days)', $dispatched > 0 ? round($rate).'%' : '—')
                ->description($dispatched > 0
                    ? $returned.' of '.$dispatched.' dispatched orders came back'
                    : 'No dispatched orders yet')
                ->descriptionIcon($rate >= 25 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-check-circle')
                // Above 25% the margin is in real trouble; above 15% watch it.
                ->color(match (true) {
                    $dispatched === 0 => 'gray',
                    $rate >= 25 => 'danger',
                    $rate >= 15 => 'warning',
                    default => 'success',
                }),
        ];
    }
}
