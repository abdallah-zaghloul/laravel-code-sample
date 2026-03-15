<?php

namespace App\Data;

class ProductData extends BaseData
{
    public function __construct(
        public string $name,
        public float $price,
    ) {
    }
}
