<?php

namespace App\Filament\Resources\MenuCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name_ru')
                    ->label('Раздел')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('parent.name_ru')
                    ->label('Входит в')
                    ->placeholder('— верхний уровень —')
                    ->color('gray'),

                TextColumn::make('name_kk')
                    ->label('Каз')
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('dishes_count')
                    ->label('Блюд')
                    ->counts('dishes')
                    ->badge(),

                IconColumn::make('is_visible')
                    ->label('Виден')
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
