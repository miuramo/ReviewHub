<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class BbMesPosted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $bbId;

    public function __construct(int $bbId)
    {
        $this->bbId = $bbId;
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('bb.' . $this->bbId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.posted';
    }
}
