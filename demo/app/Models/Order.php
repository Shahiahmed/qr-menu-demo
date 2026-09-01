<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A guest order placed from a table. Built and priced entirely on the server
 * (see {@see \App\Http\Controllers\OrderController}); the client only sends dish
 * ids and quantities. Staff move it through the workflow from the Filament panel.
 */
class Order extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_READY = 'ready';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    /** Русские подписи статусов для панели. */
    public const STATUSES = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_ACCEPTED => 'Принят',
        self::STATUS_READY => 'Готов',
        self::STATUS_DONE => 'Выдан',
        self::STATUS_CANCELLED => 'Отменён',
    ];

    /** Statuses that still need staff attention (default panel filter + badge). */
    public const ACTIVE_STATUSES = [self::STATUS_NEW, self::STATUS_ACCEPTED, self::STATUS_READY];

    protected $fillable = [
        'table_number',
        'total',
        'status',
        'comment',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'table_number' => 'integer',
            'total' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Total items ordered (sum of quantities). */
    public function itemsCount(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function formattedTotal(): string
    {
        return Money::format((int) $this->total, 'KZT');
    }
}
