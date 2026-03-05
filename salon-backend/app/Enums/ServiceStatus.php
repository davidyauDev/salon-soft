<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceStatus: string
{
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
