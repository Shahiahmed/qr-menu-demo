<?php

namespace App\Filament\Resources\Collections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name_ru')
                    ->label('Подборка')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->name_kk),

                TextColumn::make('dishes_count')
                    ->label('Блюд')
                    ->counts('dishes')
                    ->badge(),

                IconColumn::make('is_visible')
                    ->label('Видна')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
