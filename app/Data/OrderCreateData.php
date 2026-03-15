<?php

namespace App\Data;

use App\Casts\Data\ArrayOf;
use App\Casts\Data\ArrayOfCast;
use App\Casts\Data\EnumArrayCast;
use App\Casts\Data\EnumCollectionCast;
use App\Enums\FlagEnum;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Casts\EnumCast;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Rule as RuleAtt;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Validation\ValidationContext;


class OrderCreateData extends BaseData
{
    public function __construct(
        public string $name,

        #[WithCast(EnumCast::class, type: FlagEnum::class)]
        public FlagEnum $flag,

            /** @var Collection<FlagEnum> */
        #[WithCast(EnumCollectionCast::class, iterableEnum: FlagEnum::class)]
        public Collection $flagsCollection,

            /** @var array<FlagEnum> */
        #[WithCast(EnumArrayCast::class, iterableEnum: FlagEnum::class)]
        public array $flagsArray,

            /** @var array<ProductData> */
        #[WithCast(ArrayOfCast::class, class: ProductData::class)]
        public array $products,

            /** @var Collection<ProductData> */
        #[DataCollectionOf(ProductData::class)]
        public DataCollection $productsCollection
    ) {
    }

    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'flag' => [Rule::in(FlagEnum::values())],
            'flagsCollection.*' => [Rule::in(FlagEnum::values())],
            'flagsArray.*' => [Rule::in(FlagEnum::values())],
            'products.*.name' => ['distinct'],
        ];
    }
}
