<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\Actions\BookCourierAction;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrdersTable
{
    /** Colour per order status, shared by the badge and the quick actions. */
    public const STATUS_COLORS = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'gray',
        'returned' => 'danger',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm')
                    ->weight('medium'),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (Order $record): string => $record->customer_phone),

                TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('total')
                    ->formatStateUsing(fn ($state): string => bdt($state))
                    ->alignEnd()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->toggleable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'paid' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('courier')
                    ->placeholder('—')
                    ->description(fn (Order $record): ?string => $record->tracking_id)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('j M Y, g:i A')
                    ->description(fn (Order $record): string => $record->created_at->diffForHumans())
                    ->sortable(),
            ])
            // Newest first: the order you most likely need is at the top.
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('needs_action')
                    ->label('Needs action')
                    ->query(fn (Builder $query) => $query->whereIn('status', ['pending', 'confirmed']))
                    ->toggle(),

                SelectFilter::make('status')
                    ->options(array_combine(
                        array_keys(self::STATUS_COLORS),
                        array_map('ucfirst', array_keys(self::STATUS_COLORS)),
                    ))
                    ->multiple(),

                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid']),
            ])
            ->recordActions([
                ViewAction::make(),

                BookCourierAction::make(),

                Action::make('print_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->url(fn (Order $record): string => route('orders.invoice', $record), shouldOpenInNewTab: true),

                // Quick transitions, without opening the order.
                ActionGroup::make(self::quickStatusActions())
                    ->label('Set status')
                    ->icon('heroicon-m-arrow-path')
                    ->button()
                    ->outlined(),

                EditAction::make(),
            ])
            ->toolbarActions([
                // One print job for a whole courier run.
                BulkAction::make('print_invoices')
                    ->label('Print invoices')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->deselectRecordsAfterCompletion()
                    ->url(fn (Collection $records): string => route('orders.invoice.batch', [
                        'ids' => implode(',', $records->modelKeys()),
                    ]), shouldOpenInNewTab: true),

                BulkActionGroup::make(self::bulkStatusActions()),
            ])
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders placed on the storefront appear here.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }

    /**
     * Per-row status transitions. Each is hidden when the order is already in
     * that state, so the menu only ever offers a real change.
     *
     * @return array<int, Action>
     */
    private static function quickStatusActions(): array
    {
        return array_map(
            fn (array $step) => Action::make('mark_'.$step[0])
                ->label('Mark '.$step[1])
                ->icon($step[2])
                ->color(self::STATUS_COLORS[$step[0]])
                ->requiresConfirmation()
                ->modalHeading(fn (Order $record): string => "Mark {$record->order_number} as {$step[1]}?")
                ->hidden(fn (Order $record): bool => $record->status === $step[0])
                ->action(function (Order $record) use ($step) {
                    $record->update(['status' => $step[0]]);

                    Notification::make()
                        ->title("{$record->order_number} marked {$step[1]}")
                        ->success()
                        ->send();
                }),
            self::steps(),
        );
    }

    /**
     * @return array<int, BulkAction>
     */
    private static function bulkStatusActions(): array
    {
        return array_map(
            fn (array $step) => BulkAction::make('bulk_'.$step[0])
                ->label('Mark '.$step[1])
                ->icon($step[2])
                ->color(self::STATUS_COLORS[$step[0]])
                ->requiresConfirmation()
                ->action(function (Collection $records) use ($step) {
                    // Saved one at a time on purpose: a mass update would skip
                    // Eloquent events, and OrderObserver sends the shipped SMS.
                    // Bulk selections here are small (a courier run, not a
                    // catalogue), so the extra queries are worth the correctness.
                    $records->each(fn (Order $order) => $order->update(['status' => $step[0]]));

                    Notification::make()
                        ->title($records->count().' orders marked '.$step[1])
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion(),
            self::steps(),
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string}>
     */
    private static function steps(): array
    {
        return [
            ['confirmed', 'confirmed', 'heroicon-m-check'],
            ['shipped', 'shipped', 'heroicon-m-truck'],
            ['delivered', 'delivered', 'heroicon-m-check-badge'],
            ['cancelled', 'cancelled', 'heroicon-m-x-mark'],
            ['returned', 'returned', 'heroicon-m-arrow-uturn-left'],
        ];
    }
}
