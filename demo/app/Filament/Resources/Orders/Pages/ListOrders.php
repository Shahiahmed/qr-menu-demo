<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /** Quick tabs so staff default to the orders that still need work. */
    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Активные')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', Order::ACTIVE_STATUSES)),

            'all' => Tab::make('Все'),

            'done' => Tab::make('Выданные')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_DONE)),
        ];
    }
}
