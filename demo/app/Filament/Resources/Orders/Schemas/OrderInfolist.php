<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Заказ')
                ->columns(2)
                ->schema([
                    TextEntry::make('id')->label('Номер'),

                    TextEntry::make('status')
                        ->label('Статус')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? $state),

                    TextEntry::make('table_number')
                        ->label('Стол')
                        ->placeholder('—'),

                    TextEntry::make('created_at')
                        ->label('Время')
                        ->dateTime('d.m.Y H:i'),

                    TextEntry::make('comment')
                        ->label('Комментарий')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),

            Section::make('Позиции')
                ->schema([
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('name_ru')
                                ->hiddenLabel()
                                ->weight('bold'),

                            TextEntry::make('quantity')
                                ->label('Кол-во'),

                            TextEntry::make('price')
                                ->label('Цена')
                                ->formatStateUsing(fn (int $state) => Money::format($state, 'KZT')),

                            TextEntry::make('line_total')
                                ->label('Сумма')
                                ->state(fn ($record) => $record->formattedLineTotal()),
                        ])
                        ->columns(4),

                    TextEntry::make('total')
                        ->label('Итого')
                        ->weight('bold')
                        ->formatStateUsing(fn (int $state) => Money::format($state, 'KZT')),
                ]),
        ]);
    }
}
