<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // No create action: orders originate from the storefront checkout.
    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Status tabs across the top, each with a live count.
     */
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All'),
            'needs_action' => Tab::make('Needs action')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['pending', 'confirmed']))
                ->badge(OrderResource::getModel()::whereIn('status', ['pending', 'confirmed'])->count())
                ->badgeColor('warning'),
        ];

        foreach (array_keys(OrdersTable::STATUS_COLORS) as $status) {
            $tabs[$status] = Tab::make(ucfirst($status))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status))
                ->badge(OrderResource::getModel()::where('status', $status)->count())
                ->badgeColor(OrdersTable::STATUS_COLORS[$status]);
        }

        return $tabs;
    }
}
