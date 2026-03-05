<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'sale_item_id',
        'item_id',
        'restocked_lot_id',
        'quantity_base',
        'unit_refund_base',
    ];

    protected function casts(): array
    {
        return [
            'quantity_base' => 'decimal:3',
            'unit_refund_base' => 'decimal:4',
        ];
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'return_id');
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function restockedLot(): BelongsTo
    {
        return $this->belongsTo(StockLot::class, 'restocked_lot_id');
    }
}
