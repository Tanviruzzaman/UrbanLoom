<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\Actions\BookCourierAction;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_invoice')
                ->label('Print invoice')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->url(fn (Order $record): string => route('orders.invoice', $record), shouldOpenInNewTab: true),

            BookCourierAction::make(),
            EditAction::make(),
        ];
    }
}
