<?php

namespace App\Enums;

enum CourseApplicationStatus: string
{
    case Pending = 'pending';
    case Reviewed = 'reviewed';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
