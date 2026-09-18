<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ForumMesReactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $forumMesId;

    public function __construct(int $forumMesId)
    {
        $this->forumMesId = $forumMesId;
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('forum-mes.' . $this->forumMesId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reaction.updated';
    }
}
