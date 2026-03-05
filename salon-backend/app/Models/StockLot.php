<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class StockLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity_remaining',
        'cost_per_base',
        'purchased_at',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_remaining' => 'decimal:3',
            'cost_per_base' => 'decimal:4',
            'purchased_at' => 'datetime',
            'expires_at' => 'date',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('quantity_remaining', '>', 0);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function serviceConsumptions(): HasMany
    {
        return $this->hasMany(ServiceConsumption::class);
    }
}
