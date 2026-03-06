<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'item_id',
        'stock_lot_id',
        'quantity_base',
        'unit_price_base',
        'unit_cost_base',
    ];

    protected function casts(): array
    {
        return [
            'quantity_base' => 'decimal:3',
            'unit_price_base' => 'decimal:4',
            'unit_cost_base' => 'decimal:4',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function stockLot(): BelongsTo
    {
        return $this->belongsTo(StockLot::class);
    }

    public function saleReturnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }
}
