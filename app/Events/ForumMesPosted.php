<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ForumMesPosted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $forumId;

    public function __construct(int $forumId)
    {
        $this->forumId = $forumId;
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('forum.' . $this->forumId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.posted';
    }
}
