<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StoreStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /**
     * Cancelled orders are excluded from every revenue figure — they were
     * never collected, so counting them would overstate takings.
     */
    private function earning(): Builder
    {
        return Order::query()->whereNotIn('status', ['cancelled', 'returned']);
    }

    protected function getStats(): array
    {
        $today = today();

        // One aggregate query per figure; no models are hydrated.
        $todayOrders = Order::query()->whereDate('created_at', $today)->count();
        $todayRevenue = (float) $this->earning()->whereDate('created_at', $today)->sum('total');
        $pendingOrders = Order::query()->where('status', 'pending')->count();
        $monthRevenue = (float) $this->earning()
            ->whereBetween('created_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])
            ->sum('total');

        $monthOrders = Order::query()
            ->whereBetween('created_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])
            ->count();

        return [
            Stat::make("Today's orders", number_format($todayOrders))
                ->description($today->format('j F Y'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color($todayOrders > 0 ? 'success' : 'gray'),

            Stat::make("Today's revenue", bdt($todayRevenue))
                ->description('Excludes cancelled orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($todayRevenue > 0 ? 'success' : 'gray'),

            Stat::make('Pending orders', number_format($pendingOrders))
                ->description($pendingOrders > 0 ? 'Waiting to be confirmed' : 'Nothing waiting')
                ->descriptionIcon($pendingOrders > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make("This month's revenue", bdt($monthRevenue))
                ->description($monthOrders.' '.str('order')->plural($monthOrders).' in '.$today->format('F'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }
}
