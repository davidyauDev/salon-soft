<?php

declare(strict_types=1);

namespace App\Enums;

enum ItemType: string
{
    case Mixed = 'mixed';
    case SellOnly = 'sell_only';
    case ConsumeOnly = 'consume_only';
}
