<?php

namespace App\Http\Controllers;

use App\Data\OrderCreateData;
use App\Data\OrderSearchData;
use App\Data\OrderUpdateFlagsData;
use App\Data\ProductData;
use App\Jobs\OrderCreateJob;
use App\Services\OrderService;
use App\Utils\Response;

class OrderController extends Controller
{
    use Response;

    public function __construct(
        protected OrderService $orderService
    ) {
    }


    public function create(OrderCreateData $data)
    {
        $order = $this->orderService->create($data);
        return $this->data('order', $order);
    }


    public function createInBackground(OrderCreateData $data)
    {
        OrderCreateJob::dispatchAfterResponse(data: $data);
        return $this->success('creating order in background ...');
    }


    public function search(OrderSearchData $data)
    {
        $orders = $this->orderService->search($data);
        return $this->data('orders', $orders);
    }


    public function updateFlags(OrderUpdateFlagsData $data)
    {
        $order = $this->orderService->updateFlags($data);
        return $this->data('order', $order);
    }
}
