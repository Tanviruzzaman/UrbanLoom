<?php

namespace App\Filament\Resources\Orders\Actions;

use App\Models\Order;
use App\Services\SteadfastService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * "Book Courier" — creates a Steadfast parcel for an order, records the
 * consignment ID, and moves the order to shipped.
 */
class BookCourierAction
{
    public static function make(string $name = 'book_courier'): Action
    {
        return Action::make($name)
            ->label('Book Courier')
            ->icon('heroicon-m-truck')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(fn (Order $record): string => "Book courier for {$record->order_number}?")
            ->modalDescription(fn (Order $record): string => sprintf(
                'A parcel will be created for %s (%s) with %s to collect on delivery. The order will be marked shipped.',
                $record->customer_name,
                $record->customer_phone,
                bdt($record->payment_status === 'paid' ? 0 : $record->total),
            ))
            ->modalSubmitActionLabel('Book parcel')
            // Nothing to book for an order that is cancelled or already sent.
            ->hidden(fn (Order $record): bool => in_array($record->status, ['cancelled', 'delivered'], true)
                || filled($record->tracking_id))
            ->action(function (Order $record, SteadfastService $steadfast) {
                $result = $steadfast->bookAndRecord($record);

                $notification = Notification::make()->body($result['message']);

                $result['success']
                    ? $notification->title('Courier booked')->success()
                    : $notification->title('Could not book courier')->danger()->persistent();

                $notification->send();
            });
    }
}
