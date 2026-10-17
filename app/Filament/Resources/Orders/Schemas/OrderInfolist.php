<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Order number')
                            ->fontFamily('mono')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('created_at')
                            ->label('Placed')
                            ->dateTime('j M Y, g:i A'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => OrdersTable::STATUS_COLORS[$state] ?? 'gray')
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                        TextEntry::make('payment_status')
                            ->label('Payment')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'paid' ? 'success' : 'gray')
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                        TextEntry::make('payment_method')
                            ->label('Method')
                            ->formatStateUsing(fn (string $state): string => $state === 'cod' ? 'Cash on Delivery' : strtoupper($state)),

                        TextEntry::make('courier')
                            ->placeholder('Not booked')
                            ->helperText(fn (Order $record): ?string => $record->tracking_id
                                ? 'Tracking: '.$record->tracking_id
                                : null),
                    ])
                    ->columns(3),

                Section::make('Customer & delivery')
                    ->schema([
                        TextEntry::make('customer_name')->label('Name'),
                        TextEntry::make('customer_phone')->label('Phone')->copyable(),
                        TextEntry::make('customer_email')->label('Email')->placeholder('Not given')->copyable(),
                        TextEntry::make('city'),
                        TextEntry::make('shipping_address')->label('Address')->columnSpanFull(),
                        TextEntry::make('notes')->label('Order notes')->placeholder('None')->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Items')
                    ->description('A snapshot taken when the order was placed — these never change if a product is later renamed or repriced.')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('product_name')->label('Product')->weight('medium'),
                                TextEntry::make('variant_label')->label('Variant')->placeholder('—'),
                                TextEntry::make('price')
                                    ->label('Unit price')
                                    ->formatStateUsing(fn ($state): string => bdt($state)),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('line_total')
                                    ->label('Line total')
                                    ->formatStateUsing(fn ($state): string => bdt($state))
                                    ->weight('medium'),
                            ])
                            ->columns(5)
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->formatStateUsing(fn ($state): string => bdt($state)),
                                TextEntry::make('delivery_charge')
                                    ->label('Delivery charge')
                                    ->formatStateUsing(fn ($state): string => bdt($state)),
                                TextEntry::make('total')
                                    ->label('Total payable')
                                    ->formatStateUsing(fn ($state): string => bdt($state))
                                    ->weight('bold')
                                    ->size('lg'),
                            ]),
                    ]),
            ]);
    }
}
