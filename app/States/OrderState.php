<?php
namespace App\States;

use App\Data\OrderUpdateFlagsData;
use App\Enums\FlagEnum;
use App\Models\Order;

/**
 * @mixin Order
 */
trait OrderState
{
    public function updateFlags(OrderUpdateFlagsData $data): static
    {
        return tap(
            $this,
            fn(Order $order) => $order->update([
                'flags_json' => $data->flagsUpdate,
            ])
        );
    }

}
