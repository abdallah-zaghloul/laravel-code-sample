<?php

namespace App\Services;

use App\Data\OrderCreateData;
use App\Data\OrderSearchData;
use App\Data\OrderShowData;
use App\Data\OrderUpdateFlagsData;
use App\Models\Order;

class OrderService
{
    public function create(OrderCreateData $data)
    {
        $order = Order::create($data->toArray());
        return OrderShowData::from($order);
    }

    public function search(OrderSearchData $data)
    {
        $orders = Order::query()->byFlags($data?->flagsCollection)->get();
        return OrderShowData::collect($orders);
    }

    public function updateFlags(OrderUpdateFlagsData $data)
    {
        $order = Order::findOrFail($data->id);
        $order->updateFlags($data);
        return OrderShowData::from($order);
    }
}
