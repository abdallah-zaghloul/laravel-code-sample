<?php

namespace App\Casts\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class EnumArrayCast implements Cast
{
    public function __construct(
        protected string $iterableEnum
    ) {
    }

    public function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context
    ): mixed {
        return array_map(
            fn($v) => $this->iterableEnum::find($v),
            $value
        );
    }
}
