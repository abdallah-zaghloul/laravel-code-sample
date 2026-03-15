<?php

namespace App\Jobs;

use App\Data\OrderCreateData;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected OrderCreateData $data
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(OrderService $orderService): void
    {
        $orderService->create($this->data);
    }
}
