<?php

namespace App\Data;

use App\Casts\Data\EnumCollectionCast;
use App\Enums\FlagEnum;
use App\Models\Order;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\WithCast;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Rule as RuleAtt;
use Spatie\LaravelData\Support\Validation\ValidationContext;


class OrderUpdateFlagsData extends BaseData
{
     #[Computed]
    public Collection $flagsUpdate;
    public function __construct(
        #[FromRouteParameter('id')]
        public int|string $id,

            /** @var Collection<FlagEnum> */
        #[WithCast(EnumCollectionCast::class, iterableEnum: FlagEnum::class)]
        public Collection $flagsCollection,

        public bool $is_append = true
    ) {
        $this->flagsUpdate = $this->flagsCollection->merge($this->only('is_append'));
    }

    public static function rules(?ValidationContext $context = null, Order $orderModel = null): array
    {
        return [
            'id' => [Rule::exists($orderModel->getTable(), $orderModel->getKeyName())],
            'flagsCollection.*' => [Rule::in(FlagEnum::values())],
        ];
    }
}
