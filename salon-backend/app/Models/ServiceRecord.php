<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'stylist_id',
        'client_id',
        'total_amount',
        'status',
        'payment_method',
        'performed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'status' => ServiceStatus::class,
            'payment_method' => PaymentMethod::class,
            'performed_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function stylist(): BelongsTo
    {
        return $this->belongsTo(Stylist::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(ServiceConsumption::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }
}
