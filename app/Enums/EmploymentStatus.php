<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case ACTIVE = 'ACTIVE';
    case RESIGNED = 'RESIGNED';
    case RETIRED = 'RETIRED';
    case TRANSFERRED = 'TRANSFERRED';
    case LEAVE = 'LEAVE';
}
