<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrders extends TableWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent orders')
            ->description('The last 10 orders placed')
            ->query(
                // select() keeps the query narrow — no unused columns hydrated.
                Order::query()
                    ->select(['id', 'order_number', 'customer_name', 'customer_phone', 'total', 'status', 'payment_status', 'created_at'])
                    ->latest('id')
                    ->limit(10)
            )
            ->paginated(false)
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (Order $record): string => $record->customer_phone)
                    ->searchable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => bdt($state))
                    ->alignEnd()
                    ->weight('medium'),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'paid' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('j M, g:i A')
                    ->description(fn (Order $record): string => $record->created_at->diffForHumans())
                    ->sortable(),
            ])
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders placed on the storefront will appear here.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}
