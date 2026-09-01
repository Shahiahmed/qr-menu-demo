<?php

namespace App\Filament\Pages;

use App\Models\VenueSetting;
use App\Support\ImageOptimizer;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Edits the single venue row (id = 1). No create/delete — there is only ever
 * one venue on this site.
 */
class VenueSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Заведение';

    protected static ?string $title = 'Настройки заведения';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.venue-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill(VenueSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->tabs([
                    Tab::make('Основное')->schema([
                        TextInput::make('name')
                            ->label('Название заведения')
                            ->required()
                            ->maxLength(255),

                        Select::make('default_locale')
                            ->label('Язык по умолчанию')
                            ->options(['ru' => 'Русский', 'kk' => 'Қазақша'])
                            ->native(false)
                            ->required(),

                        Select::make('currency')
                            ->label('Валюта')
                            ->options(['KZT' => '₸ Тенге', 'RUB' => '₽ Рубль', 'USD' => '$ Доллар'])
                            ->native(false)
                            ->required(),

                        Textarea::make('description_ru')->label('Описание (рус)')->rows(2),
                        Textarea::make('description_kk')->label('Описание (каз)')->rows(2),
                    ])->columns(2),

                    Tab::make('Оформление')->schema([
                        Select::make('theme')
                            ->label('Цветовая тема')
                            ->options(collect(config('menu.themes'))->map(fn ($t) => $t['ru'])->all())
                            ->native(false)
                            ->required(),

                        Select::make('layout')
                            ->label('Раскладка меню')
                            ->options(collect(config('menu.layouts'))->map(fn ($l) => $l['ru'])->all())
                            ->native(false)
                            ->required(),

                        Toggle::make('show_logo')
                            ->label('Показывать логотип на обложке')
                            ->columnSpanFull(),

                        // Both are downscaled and re-encoded to WebP before disk —
                        // a cover spans the screen (cap 1600), a logo is a small
                        // badge (cap 512). Keeps the guest page light.
                        FileUpload::make('cover_path')
                            ->label('Обложка')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->maxSize(8192)
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => ImageOptimizer::store(
                                $file, 'venue', ImageOptimizer::MODE_CONTAIN, 1600, 82,
                            )),

                        FileUpload::make('logo_path')
                            ->label('Логотип')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->maxSize(4096)
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => ImageOptimizer::store(
                                $file, 'venue', ImageOptimizer::MODE_CONTAIN, 512, 82,
                            )),
                    ])->columns(2),

                    Tab::make('Контакты')->schema([
                        TextInput::make('address')->label('Адрес')->maxLength(255),
                        TextInput::make('phone')->label('Телефон')->maxLength(255),
                        TextInput::make('wifi_ssid')->label('Wi-Fi сеть')->maxLength(255),
                        TextInput::make('wifi_password')->label('Wi-Fi пароль')->maxLength(255),
                        TextInput::make('instagram_url')->label('Instagram')->maxLength(255),
                        TextInput::make('facebook_url')->label('Facebook')->maxLength(255),
                        TextInput::make('tiktok_url')->label('TikTok')->maxLength(255),
                    ])->columns(2),

                    Tab::make('Заказы')->schema([
                        Toggle::make('ordering_enabled')
                            ->label('Приём заказов со столов')
                            ->helperText('Когда выключено, гости видят меню без кнопок «В заказ».')
                            ->columnSpanFull(),

                        TextInput::make('tables_count')
                            ->label('Количество столов')
                            ->helperText('Гость выберет стол из списка 1…N. Пусто — гость вводит номер вручную.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(9999),
                    ])->columns(2),

                    Tab::make('SEO')->schema([
                        Section::make('Русский')->schema([
                            TextInput::make('seo_title_ru')
                                ->label('Title (рус)')
                                ->helperText('Заголовок вкладки и в поиске. Пусто — берётся название заведения. До ~60 символов.')
                                ->maxLength(255),
                            Textarea::make('seo_description_ru')
                                ->label('Description (рус)')
                                ->helperText('Текст под ссылкой в поиске и в превью при отправке ссылки. Пусто — берётся описание заведения. До ~160 символов.')
                                ->rows(2),
                            TextInput::make('seo_keywords_ru')
                                ->label('Ключевые слова (рус)')
                                ->helperText('Через запятую: меню, ресторан Алматы, казахская кухня…')
                                ->maxLength(255),
                        ]),
                        Section::make('Қазақша')->schema([
                            TextInput::make('seo_title_kk')
                                ->label('Title (каз)')
                                ->helperText('Пусто — берётся русский Title или название заведения.')
                                ->maxLength(255),
                            Textarea::make('seo_description_kk')
                                ->label('Description (каз)')
                                ->helperText('Пусто — берётся русское описание.')
                                ->rows(2),
                            TextInput::make('seo_keywords_kk')
                                ->label('Ключевые слова (каз)')
                                ->helperText('Через запятую.')
                                ->maxLength(255),
                        ]),
                        Section::make('Картинка для соцсетей (OG)')->schema([
                            // Downscaled + re-encoded to WebP like every other upload.
                            // ~1200px long edge suits the 1.91:1 social-share card.
                            FileUpload::make('seo_og_path')
                                ->label('OG-изображение')
                                ->helperText('Показывается при отправке ссылки в WhatsApp, Telegram, соцсети. Рекомендуется 1200×630. Пусто — используется обложка меню.')
                                ->image()
                                ->imageEditor()
                                ->imageCropAspectRatio('1.91:1')
                                ->disk('public')
                                ->maxSize(8192)
                                ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => ImageOptimizer::store(
                                    $file, 'venue', ImageOptimizer::MODE_CONTAIN, 1200, 82,
                                )),
                        ]),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        VenueSetting::current()->update($data);

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Сохранено')
            ->send();
    }
}
