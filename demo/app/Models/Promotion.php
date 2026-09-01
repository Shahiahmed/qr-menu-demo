<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Promotion extends Model
{
    protected $fillable = [
        'title_ru',
        'title_kk',
        'subtitle_ru',
        'subtitle_kk',
        'image_path',
        'sort',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function title(string $locale): string
    {
        return $locale === 'kk'
            ? ($this->title_kk ?: $this->title_ru)
            : $this->title_ru;
    }

    public function subtitle(string $locale): ?string
    {
        return $locale === 'kk'
            ? ($this->subtitle_kk ?: $this->subtitle_ru)
            : $this->subtitle_ru;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
