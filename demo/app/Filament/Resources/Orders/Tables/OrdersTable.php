<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Staff keep this open on a screen in the kitchen — keep it fresh.
            ->poll('15s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label('Время')
                    ->since()
                    ->sortable(),

                TextColumn::make('table_number')
                    ->label('Стол')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('items_summary')
                    ->label('Состав')
                    ->state(fn (Order $record) => $record->itemsCount().' поз.')
                    ->description(fn (Order $record) => $record->items
                        ->map(fn ($i) => $i->name('ru').' ×'.$i->quantity)
                        ->implode(', ')),

                TextColumn::make('total')
                    ->label('Сумма')
                    ->formatStateUsing(fn (int $state) => \App\Support\Money::format($state, 'KZT'))
                    ->alignEnd()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        Order::STATUS_NEW => 'danger',
                        Order::STATUS_ACCEPTED => 'warning',
                        Order::STATUS_READY => 'info',
                        Order::STATUS_DONE => 'success',
                        Order::STATUS_CANCELLED => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Order::STATUSES)
                    ->default(null),
            ])
            ->recordActions([
                // Quick one-tap status transitions right from the list.
                Action::make('accept')
                    ->label('Принять')
                    ->icon('heroicon-o-check')
                    ->color('warning')
                    ->visible(fn (Order $record) => $record->status === Order::STATUS_NEW)
                    ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_ACCEPTED])),

                Action::make('ready')
                    ->label('Готов')
                    ->icon('heroicon-o-fire')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->status === Order::STATUS_ACCEPTED)
                    ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_READY])),

                Action::make('done')
                    ->label('Выдан')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => in_array($record->status, [Order::STATUS_ACCEPTED, Order::STATUS_READY], true))
                    ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_DONE])),

                Action::make('cancel')
                    ->label('Отменить')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => in_array($record->status, Order::ACTIVE_STATUSES, true))
                    ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_CANCELLED])),

                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
