<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Supervisor = 'supervisor';
    case Instructor = 'instructor';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'أدمن',
            self::Supervisor => 'مشرف',
            self::Instructor => 'مدرب',
            self::Student => 'طالب',
        };
    }
}
