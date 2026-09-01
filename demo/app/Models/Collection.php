<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    protected $fillable = [
        'name_ru',
        'name_kk',
        'sort',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class)
            ->withPivot('sort')
            ->orderBy('collection_dish.sort')
            ->orderBy('dishes.id');
    }

    /** Localised name, falling back to Russian when the Kazakh field is empty. */
    public function name(string $locale): string
    {
        return $locale === 'kk'
            ? ($this->name_kk ?: $this->name_ru)
            : $this->name_ru;
    }
}
