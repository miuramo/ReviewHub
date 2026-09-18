<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class BbMesReactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $bbMesId;

    public function __construct(int $bbMesId)
    {
        $this->bbMesId = $bbMesId;
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('bb-mes.' . $this->bbMesId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reaction.updated';
    }
}
