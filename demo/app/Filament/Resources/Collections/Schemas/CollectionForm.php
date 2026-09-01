<?php

namespace App\Filament\Resources\Collections\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Подборка')
                    ->columns(2)
                    ->schema([
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

                        // Filament writes the collection_dish pivot for us. A dish
                        // can sit in several collections regardless of its category.
                        Select::make('dishes')
                            ->label('Блюда в подборке')
                            ->relationship('dishes', 'name_ru')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
