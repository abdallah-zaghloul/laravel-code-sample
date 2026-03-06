<?php
namespace App\Enums;
use App\Traits\IterableEnum;

enum FlagEnum
{
    use IterableEnum;
    case Tax;
    case Amount;
    case Count;
}
