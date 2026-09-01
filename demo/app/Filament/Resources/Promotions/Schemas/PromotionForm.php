<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Support\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Баннер')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title_ru')
                            ->label('Заголовок (рус)')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('title_kk')
                            ->label('Заголовок (каз)')
                            ->maxLength(255)
                            ->helperText('Можно оставить пустым — покажем русский.'),

                        TextInput::make('subtitle_ru')
                            ->label('Подпись (рус)')
                            ->maxLength(255),

                        TextInput::make('subtitle_kk')
                            ->label('Подпись (каз)')
                            ->maxLength(255),

                        // Wide banner card — downscaled and re-encoded to WebP
                        // before disk so the carousel stays light on a phone.
                        FileUpload::make('image_path')
                            ->label('Картинка')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->maxSize(8192)
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => ImageOptimizer::store(
                                $file, 'promotions', ImageOptimizer::MODE_CONTAIN, 1200, 80,
                            ))
                            ->columnSpanFull(),
                    ]),

                Section::make('Показ')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sort')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_visible')
                            ->label('Показывать гостям')
                            ->default(true),
                    ]),
            ]);
    }
}
