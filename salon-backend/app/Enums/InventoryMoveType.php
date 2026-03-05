<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryMoveType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case ServiceConsumption = 'service_consumption';
    case ReturnIn = 'return_in';
    case Waste = 'waste';
    case Adjustment = 'adjustment';
}
