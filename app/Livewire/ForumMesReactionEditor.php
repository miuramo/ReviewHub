<?php

namespace App\Livewire;

use App\Events\ForumMesReactionUpdated;
use Livewire\Attributes\On;
use Livewire\Component;

class ForumMesReactionEditor extends Component
{
    public int $forumMesId;
    public int $userId;

    // 選択可能な絵文字（3グループ）
    public $emojiGroups = [
        ['🆗', '✅', '✔', '🙆', '🙆‍♂️', '🙆‍♀️', '👍', '👏', '🙌', '👌', '🙇', '🙇‍♂️', '🙇‍♀️', '🙏', '🫡', '🥳', '💯', '🆒', '🎉', '🎊', '🍻', '🥂'],
        ['😀', '😃', '😄', '😁', '😅', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '🎄', '🏆', '🐥', '🎃', '🈴', '🈵', '🉑'],
        ['🤔', '👀', '🙀', '😮‍💨', '😔', '😴', '😷', '🥵', '😵', '🧐', '🥺', '😨', '😰', '😢', '🫟', '🆘', '⭕', '❌', '❎', '👎'],
    ];

    public function mount(int $forumMesId, int $userId)
    {
        $this->forumMesId = $forumMesId;
        $this->userId = $userId;
    }

    #[On('echo:forum-mes.{forumMesId},.reaction.updated')]
    public function refreshReactions()
    {
        // Echoからの通知を受けて再レンダリングするだけでよい
    }

    public function render()
    {
        $reactions = \App\Models\ForumMesReaction::where('forum_mes_id', $this->forumMesId)
            ->orderBy('id')
            ->get()
            ->groupBy('emoji')
            ->map(function ($group) {
                return [
                    'emoji' => $group->first()->emoji,
                    'count' => $group->count(),
                    'reacted' => $group->contains('user_id', $this->userId),
                    'userNames' => $group->pluck('user.name')->filter()->implode(', '),
                ];
            })
            ->values();

        return view('livewire.forum-mes-reaction-editor', [
            'reactions' => $reactions,
        ]);
    }

    public function toggleReaction($emoji)
    {
        // MySQLの絵文字の照合順序は別の絵文字を同一視することがあるため、DB側では絞り込まずPHPで厳密比較する
        $existing = \App\Models\ForumMesReaction::where('forum_mes_id', $this->forumMesId)
            ->where('user_id', $this->userId)
            ->get()
            ->first(fn($reaction) => $reaction->emoji === $emoji);

        if ($existing) {
            $existing->delete();
        } else {
            \App\Models\ForumMesReaction::create([
                'forum_mes_id' => $this->forumMesId,
                'user_id' => $this->userId,
                'emoji' => $emoji,
            ]);
        }

        broadcast(new ForumMesReactionUpdated($this->forumMesId));
    }
}
