<?php

namespace App\Enums;

enum TransmissionType: string
{
    case Manual = 'manual';
    case Automatic = 'automatic';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'عادي',
            self::Automatic => 'أوتوماتيك',
        };
    }
}
