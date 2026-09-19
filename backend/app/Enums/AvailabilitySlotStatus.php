<?php

namespace App\Enums;

enum AvailabilitySlotStatus: string
{
    case Proposed = 'proposed';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
