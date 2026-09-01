<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    protected $fillable = [
        'parent_id',
        'name_ru',
        'name_kk',
        'icon',
        'sort',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class)->orderBy('sort')->orderBy('id');
    }

    /** The top-level category this subcategory sits under (null when top-level). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Subcategories under this top-level category, in display order. */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort')->orderBy('id');
    }

    /** Localised name, falling back to Russian when the Kazakh field is empty. */
    public function name(string $locale): string
    {
        return $locale === 'kk'
            ? ($this->name_kk ?: $this->name_ru)
            : $this->name_ru;
    }
}
