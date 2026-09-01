<?php

namespace App\Filament\Resources\Dishes\Schemas;

use App\Models\MenuCategory;
use App\Support\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DishForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Блюдо')
                    ->columns(2)
                    ->schema([
                        Select::make('menu_category_id')
                            ->label('Раздел')
                            // Show the tree ("Кухня → Салаты") so a dish lands in
                            // the right leaf; a dish may also sit on a top-level
                            // category directly.
                            ->options(fn () => MenuCategory::query()
                                ->with('parent')
                                ->orderBy('sort')
                                ->get()
                                ->mapWithKeys(fn (MenuCategory $c) => [
                                    $c->id => $c->parent ? $c->parent->name_ru.' → '.$c->name_ru : $c->name_ru,
                                ]))
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->columnSpanFull(),

                        TextInput::make('name_ru')
                            ->label('Название (рус)')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_kk')
                            ->label('Название (каз)')
                            ->maxLength(255),

                        Textarea::make('description_ru')
                            ->label('Описание (рус)')
                            ->rows(2),

                        Textarea::make('description_kk')
                            ->label('Описание (каз)')
                            ->rows(2),

                        // Owner types tenge; we store тиын. Convert on the boundary
                        // — never keep money as a float.
                        TextInput::make('price')
                            ->label('Цена, ₸')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->formatStateUsing(fn (?int $state) => $state === null ? null : intdiv($state, 100))
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),

                        TextInput::make('slug')
                            ->label('URL блюда (slug)')
                            ->helperText('Оставьте пустым — создастся автоматически из названия.')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Показ и фото')
                    ->columns(2)
                    ->schema([
                        // Centre-cropped to a square and re-encoded to WebP before
                        // it touches disk — a menu shows many photos at once, so a
                        // raw phone photo would lag the guest page. ~800px / ~40 KB.
                        FileUpload::make('image_path')
                            ->label('Фото блюда')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->disk('public')
                            ->maxSize(8192)
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => ImageOptimizer::store(
                                $file, 'dishes', ImageOptimizer::MODE_SQUARE, 800, 80,
                            ))
                            ->columnSpanFull(),

                        Toggle::make('is_available')
                            ->label('В наличии')
                            ->helperText('Выключите — блюдо покажется с меткой «Закончилось».')
                            ->default(true),

                        Toggle::make('is_visible')
                            ->label('Показывать гостям')
                            ->default(true),

                        TextInput::make('sort')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
