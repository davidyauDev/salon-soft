<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_record_id',
        'item_id',
        'stock_lot_id',
        'quantity_base',
        'unit_cost_base',
    ];

    protected function casts(): array
    {
        return [
            'quantity_base' => 'decimal:3',
            'unit_cost_base' => 'decimal:4',
        ];
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class, 'service_record_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function stockLot(): BelongsTo
    {
        return $this->belongsTo(StockLot::class);
    }
}
