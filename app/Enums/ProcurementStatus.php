<?php

namespace App\Enums;

enum ProcurementStatus: string
{
    case DRAFT = 'draft';
    case PENDING_KABAG = 'pending_kabag';
    case PENDING_DIR = 'pending_dir';
    case PROCESSING = 'processing';
    case NEED_INFO = 'need_info';
    case WAITING_DELIVERY = 'waiting_delivery';
    case READY_TO_PICKUP = 'ready_to_pickup';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
}
