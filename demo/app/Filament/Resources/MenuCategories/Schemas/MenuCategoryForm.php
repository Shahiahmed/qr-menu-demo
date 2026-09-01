<?php

namespace App\Filament\Resources\MenuCategories\Schemas;

use App\Models\MenuCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // A category may sit under a top-level one (making it a
                // subcategory) or stand alone. Only top-level categories are
                // offered as parents, and never itself — that keeps the tree two
                // levels deep.
                Select::make('parent_id')
                    ->label('Родительская категория')
                    ->helperText('Пусто — категория верхнего уровня. Иначе — подкатегория выбранной.')
                    ->native(false)
                    ->searchable()
                    ->options(fn (?MenuCategory $record) => MenuCategory::query()
                        ->whereNull('parent_id')
                        ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                        ->orderBy('sort')
                        ->pluck('name_ru', 'id')),

                TextInput::make('name_ru')
                    ->label('Название (рус)')
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_kk')
                    ->label('Название (каз)')
                    ->maxLength(255)
                    ->helperText('Можно оставить пустым — покажем русское.'),

                TextInput::make('sort')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_visible')
                    ->label('Показывать гостям')
                    ->default(true),
            ]);
    }
}
