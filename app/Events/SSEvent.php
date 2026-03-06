<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SSEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets, Dispatchable, SerializesModels;

    private string $topic;
    public function __construct(
        public string $path,
        public int|string $key,
        public mixed $data
    ) {
        $this->topic = "$this->path.$this->key";
    }

    public function broadcastOn()
    {
        return new Channel($this->topic);
    }

    public function broadcastAs()
    {
        return $this->topic;
    }

    public function broadcastWith(): array
    {
        return collect($this->data)->toArray();
    }
}
