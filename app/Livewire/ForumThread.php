<?php

namespace App\Livewire;

use App\Models\ForumMes;
use Livewire\Attributes\On;
use Livewire\Component;

class ForumThread extends Component
{
    public int $forumId;
    public bool $isClose;

    public function mount(int $forumId, bool $isClose)
    {
        $this->forumId = $forumId;
        $this->isClose = $isClose;
    }

    #[On('echo:forum.{forumId},.message.posted')]
    public function refreshMessages()
    {
        // Echoからの通知を受けて再レンダリングするだけでよい
    }

    public function render()
    {
        $messages = ForumMes::where('forum_id', $this->forumId)
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->with(['user', 'replies.user', 'replies.replies.user', 'replies.replies.replies.user'])
            ->get();

        return view('livewire.forum-thread', [
            'messages' => $messages,
        ]);
    }
}
