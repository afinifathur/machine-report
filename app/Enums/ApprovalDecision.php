<?php

namespace App\Enums;

enum ApprovalDecision: string
{
    case APPROVED = 'approved';
    case RETURNED_FOR_INFO = 'returned_for_info';
    case REJECTED = 'rejected';
}
