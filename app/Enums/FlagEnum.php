<?php
namespace App\Enums;
use App\Utils\IterableEnum;

enum FlagEnum: string
{
    use IterableEnum;
    case Tax = 'Tax';
    case Amount = 'Amount';
    case Count = 'Count';
}
