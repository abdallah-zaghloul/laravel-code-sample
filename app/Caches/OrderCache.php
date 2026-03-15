<?php
namespace App\Caches;

use App\Data\OrderSearchData;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Summary of OrderCache
 * @mixin Order
 */
trait OrderCache
{
    public static function getCached(OrderSearchData $data): Collection
    {
        return Cache::remember(
            'orders',
            60,
            fn() => static::query()
                ->search($data)
                ->get()
        );
    }

}
