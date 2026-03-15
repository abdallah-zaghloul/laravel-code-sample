<?php

namespace App\Data;

use App\Enums\FlagEnum;

class OrderShowData extends BaseData
{
    public function __construct(
        public string $name,
        // public FlagEnum $flag
    ) {
    }

}
