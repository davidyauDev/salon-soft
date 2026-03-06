<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'base_unit',
        'sale_price',
        'stock_minimum',
        'category_id',
        'brand_id',
        'sku',
        'barcode',
        'reorder_point',
        'reorder_qty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ItemType::class,
            'sale_price' => 'decimal:2',
            'stock_minimum' => 'decimal:3',
            'reorder_point' => 'decimal:3',
            'reorder_qty' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ItemUnit::class);
    }

    public function stockLots(): HasMany
    {
        return $this->hasMany(StockLot::class);
    }

    public function inventoryMoves(): HasMany
    {
        return $this->hasMany(InventoryMove::class);
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
