<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fulfilment')
                    ->description('The only fields you normally change after an order is placed.')
                    ->schema([
                        Select::make('status')
                            ->required()
                            ->native(false)
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled (before dispatch)',
                                'returned' => 'Returned (refused at the door)',
                            ]),

                        Select::make('payment_status')
                            ->label('Payment status')
                            ->required()
                            ->native(false)
                            ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid'])
                            ->helperText('Mark paid once the courier remits the cash.'),

                        TextInput::make('courier')
                            ->maxLength(255)
                            ->placeholder('Steadfast, Pathao, RedX…')
                            ->datalist(['Steadfast', 'Pathao', 'RedX', 'Sundarban', 'SA Paribahan']),

                        TextInput::make('tracking_id')
                            ->label('Tracking / consignment ID')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Customer & delivery')
                    ->description('Correct a mis-typed phone number or address before dispatch.')
                    ->schema([
                        TextInput::make('customer_name')->required()->maxLength(255),

                        TextInput::make('customer_phone')
                            ->required()
                            ->tel()
                            ->maxLength(255)
                            ->rule('regex:/^(?:\+?880|0)1[3-9]\d{8}$/')
                            ->validationMessages([
                                'regex' => 'Enter a valid Bangladeshi mobile number, e.g. 01712345678.',
                            ]),

                        TextInput::make('customer_email')->email()->maxLength(255),

                        TextInput::make('city')->required()->maxLength(100),

                        Textarea::make('shipping_address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Order notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Totals')
                    ->description('Snapshotted when the order was placed. Edit only to correct a genuine mistake.')
                    ->collapsed()
                    ->schema([
                        TextInput::make('subtotal')->numeric()->prefix('৳')->required(),
                        TextInput::make('delivery_charge')->numeric()->prefix('৳')->required(),
                        TextInput::make('total')->numeric()->prefix('৳')->required(),
                    ])
                    ->columns(3),
            ]);
    }
}
