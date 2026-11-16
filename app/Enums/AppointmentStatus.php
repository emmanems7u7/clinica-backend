<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled  = 'scheduled';
    case Confirmed  = 'confirmed';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case NoShow     = 'no_show';

    /** Estados que liberan el horario del odontólogo */
    public static function freeing(): array
    {
        return [self::Cancelled->value, self::NoShow->value];
    }

    /** Estados finales: ya no admiten cambios de estado */
    public static function final(): array
    {
        return [self::Completed->value, self::Cancelled->value, self::NoShow->value];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
