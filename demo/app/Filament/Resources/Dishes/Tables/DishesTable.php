<?php

namespace App\Filament\Resources\Dishes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DishesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Фото')
                    ->disk('public')
                    ->square()
                    ->size(48),

                TextColumn::make('name_ru')
                    ->label('Блюдо')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->name_kk),

                TextColumn::make('category.name_ru')
                    ->label('Раздел')
                    ->badge()
                    ->color('gray'),

                // Stored in тиын; render as tenge.
                TextColumn::make('price')
                    ->label('Цена')
                    ->formatStateUsing(fn (int $state) => \App\Support\Money::format($state, 'KZT'))
                    ->alignEnd(),

                IconColumn::make('is_available')
                    ->label('В наличии')
                    ->boolean(),

                IconColumn::make('is_visible')
                    ->label('Виден')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('menu_category_id')
                    ->label('Раздел')
                    ->relationship('category', 'name_ru'),
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
