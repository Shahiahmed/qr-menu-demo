<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of an order. Name and price are snapshots taken at order time (see
 * the migration) — never read the live dish for the bill.
 */
class OrderItem extends Model
{
    protected $fillable = [
        'dish_id',
        'name_ru',
        'name_kk',
        'price',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function name(string $locale): string
    {
        return $locale === 'kk' ? ($this->name_kk ?: $this->name_ru) : $this->name_ru;
    }

    /** Line subtotal (price × quantity) as a human string. */
    public function formattedLineTotal(): string
    {
        return Money::format((int) $this->price * (int) $this->quantity, 'KZT');
    }
}
