<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'unit',
        'factor_to_base',
        'is_base',
    ];

    protected function casts(): array
    {
        return [
            'factor_to_base' => 'decimal:6',
            'is_base' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
