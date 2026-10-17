<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Customers who have ordered more than once.
 *
 * A repeat order carries no advertising cost, so it is worth several times a
 * first order. These are the people worth phoning when new stock lands.
 */
class RepeatCustomers extends TableWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Repeat customers')
            ->description('Grouped by phone number. These orders cost you nothing in advertising.')
            ->query(function () {
                /*
                 * Aggregated in SQL — no order rows are hydrated to count them.
                 *
                 * The grouping runs inside a subquery aliased back to `orders`.
                 * Filament appends `ORDER BY orders.id` for the record key, and
                 * under MySQL's only_full_group_by that is illegal against a
                 * GROUP BY query — but perfectly valid against a derived table
                 * where `id` is just a column.
                 */
                $grouped = Order::query()
                    ->selectRaw('MIN(id) as id')
                    ->selectRaw('customer_phone')
                    ->selectRaw('MAX(customer_name) as customer_name')
                    ->selectRaw('COUNT(*) as order_count')
                    ->selectRaw("SUM(CASE WHEN status IN ('cancelled','returned') THEN 0 ELSE total END) as lifetime_value")
                    ->selectRaw('MAX(created_at) as last_order_at')
                    ->groupBy('customer_phone')
                    ->havingRaw('COUNT(*) > 1');

                return Order::query()->fromSub($grouped, 'orders');
            })
            ->defaultSort('order_count', 'desc')
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->weight('medium')
                    ->description(fn ($record): string => $record->customer_phone),

                TextColumn::make('order_count')
                    ->label('Orders')
                    ->badge()
                    ->color('success')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('lifetime_value')
                    ->label('Total spent')
                    ->formatStateUsing(fn ($state): string => bdt($state))
                    ->alignEnd()
                    ->weight('medium')
                    ->sortable(),

                TextColumn::make('last_order_at')
                    ->label('Last order')
                    ->dateTime('j M Y')
                    ->description(fn ($record): string => \Illuminate\Support\Carbon::parse($record->last_order_at)->diffForHumans())
                    ->sortable(),
            ])
            ->recordUrl(null)
            ->emptyStateHeading('No repeat customers yet')
            ->emptyStateDescription('Nobody has ordered twice so far. They will appear here as soon as they do.')
            ->emptyStateIcon('heroicon-o-heart');
    }
}
