<?php

namespace App\Enums;

enum OrderTypeEnum:string
{
    use UseValidation;
    case BUY = 'buy';
    case SELL = 'sell';
}
