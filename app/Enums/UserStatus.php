<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Banned = 'banned';
    case Suspended = 'suspended';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
