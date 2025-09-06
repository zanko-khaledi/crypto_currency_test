<?php

namespace App\Enums;

trait UseValidation
{

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }

    public static function keys(): array
    {
        return array_column(self::cases(),'name');
    }

    public static function validate(): string
    {
        return 'in:'.implode(',',array_values(self::cases()));
    }
}
