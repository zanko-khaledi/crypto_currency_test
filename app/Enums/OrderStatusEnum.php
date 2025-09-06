<?php

namespace App\Enums;

enum OrderStatusEnum:string
{
    use UseValidation;
    case OPEN = 'open';
    case MATCHED = 'matched';
    case CANCELED = 'canceled';
}
