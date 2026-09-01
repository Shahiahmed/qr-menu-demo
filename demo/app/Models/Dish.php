<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Dish extends Model
{
    protected $fillable = [
        'menu_category_id',
        'slug',
        'name_ru',
        'name_kk',
        'description_ru',
        'description_kk',
        'price',
        'is_available',
        'is_visible',
        'image_path',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_available' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // A dish always needs a URL-safe, unique slug for its SEO page. Mint one
        // from the Russian name when the owner leaves it blank.
        static::saving(function (Dish $dish) {
            if (blank($dish->slug)) {
                $dish->slug = $dish->uniqueSlugFrom($dish->name_ru ?: 'dish');
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    public function name(string $locale): string
    {
        return $locale === 'kk'
            ? ($this->name_kk ?: $this->name_ru)
            : $this->name_ru;
    }

    public function description(string $locale): ?string
    {
        return $locale === 'kk'
            ? ($this->description_kk ?: $this->description_ru)
            : $this->description_ru;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    private function uniqueSlugFrom(string $source): string
    {
        $base = Str::slug($source) ?: 'dish';
        $slug = $base;
        $i = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
