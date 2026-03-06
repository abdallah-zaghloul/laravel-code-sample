<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WSEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets, Dispatchable, SerializesModels;

    public function __construct(
        public string $path,
        public int|string $key,
        public mixed $data
    ) {
    }

    public function broadcastOn()
    {
        return new Channel($this->path);
    }

    public function broadcastAs()
    {
        return (string) $this->key;
    }

    public function broadcastWith(): array
    {
        return collect($this->data)->toArray();
    }
}
