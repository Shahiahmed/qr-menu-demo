<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * The single venue this site belongs to. Always id = 1 — see {@see current()}.
 */
class VenueSetting extends Model
{
    /**
     * Fallback cover shown until the owner uploads their own — the same warm
     * restaurant crop the main project's /m/demo uses, so a fresh install never
     * shows a bare placeholder.
     */
    public const DEFAULT_COVER_URL =
        'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&h=640&q=75&auto=format&fit=crop';

    protected $fillable = [
        'name',
        'currency',
        'default_locale',
        'description_ru',
        'description_kk',
        'address',
        'phone',
        'wifi_ssid',
        'wifi_password',
        'instagram_url',
        'facebook_url',
        'tiktok_url',
        'theme',
        'layout',
        'cover_path',
        'logo_path',
        'show_logo',
        'ordering_enabled',
        'tables_count',
        'seo_title_ru',
        'seo_title_kk',
        'seo_description_ru',
        'seo_description_kk',
    ];

    protected function casts(): array
    {
        return [
            'show_logo' => 'boolean',
            'ordering_enabled' => 'boolean',
            'tables_count' => 'integer',
        ];
    }

    /**
     * The one and only settings row, created on first access with sane defaults.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : self::DEFAULT_COVER_URL;
    }

    /** True only when the owner uploaded their own cover (not the fallback). */
    public function hasOwnCover(): bool
    {
        return (bool) $this->cover_path;
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}
