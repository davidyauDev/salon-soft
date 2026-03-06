<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'duration_min',
        'base_price',
        'requires_deposit',
        'deposit_amount',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'requires_deposit' => 'boolean',
            'duration_min' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_service');
    }

    public function stylists(): BelongsToMany
    {
        return $this->belongsToMany(Stylist::class, 'service_stylist');
    }
}
