<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Last 14 days';

    protected ?string $description = 'Orders placed and revenue taken, by day.';

    public ?string $filter = 'orders';

    /**
     * Toggle between counting orders and summing revenue.
     */
    protected function getFilters(): ?array
    {
        return [
            'orders' => 'Orders per day',
            'revenue' => 'Revenue per day',
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $showRevenue = $this->filter === 'revenue';

        $start = today()->subDays(13);

        /**
         * One grouped query for the whole window. Cancelled orders are dropped
         * from revenue but still counted as orders placed.
         */
        $rows = Order::query()
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw("SUM(CASE WHEN status IN ('cancelled','returned') THEN 0 ELSE total END) as revenue")
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck($showRevenue ? 'revenue' : 'orders', 'day');

        $labels = [];
        $values = [];

        // Walk every day in the window so gaps render as zero, not as a break.
        foreach (range(0, 13) as $offset) {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();

            $labels[] = $date->format('j M');
            $values[] = (float) ($rows[$key] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => $showRevenue ? 'Revenue (৳)' : 'Orders',
                'data' => $values,
                'backgroundColor' => $showRevenue ? 'rgba(16, 185, 129, 0.75)' : 'rgba(59, 130, 246, 0.75)',
                'borderColor' => $showRevenue ? 'rgb(16, 185, 129)' : 'rgb(59, 130, 246)',
                'borderWidth' => 1,
                'borderRadius' => 4,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        $prefix = $this->filter === 'revenue' ? "'৳'" : "''";

        // RawJs so tooltips and the y-axis carry the ৳ symbol.
        return RawJs::make(<<<JS
            {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => {$prefix} + context.parsed.y.toLocaleString('en-US'),
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            callback: (value) => {$prefix} + value.toLocaleString('en-US'),
                        },
                    },
                },
            }
        JS);
    }
}
