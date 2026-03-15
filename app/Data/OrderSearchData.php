<?php

namespace App\Data;

use App\Casts\Data\EnumCollectionCast;
use App\Enums\FlagEnum;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Support\Validation\ValidationContext;


class OrderSearchData extends BaseData
{
    public function __construct(
        public ?string $name,

            /** @var Collection<FlagEnum> */
        #[WithCast(EnumCollectionCast::class, iterableEnum: FlagEnum::class)]
        public ?Collection $flagsCollection,
    ) {
    }

    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'flagsCollection.*' => [Rule::in(FlagEnum::values())],
        ];
    }
}
