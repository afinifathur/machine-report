<?php

namespace App\Enums;

enum ProcurementUrgency: string
{
    case NORMAL = 'normal';
    case URGENT = 'urgent';
    case EMERGENCY = 'emergency';
}
