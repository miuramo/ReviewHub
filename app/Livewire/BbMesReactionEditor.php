<?php

namespace App\Livewire;

use App\Events\BbMesReactionUpdated;
use Livewire\Attributes\On;
use Livewire\Component;

class BbMesReactionEditor extends Component
{
    public int $bbMesId;
    public int $userId;

    // 選択可能な絵文字（3グループ）
    public $emojiGroups = [
        ['🆗', '✅', '✔', '🙆', '🙆‍♂️', '🙆‍♀️', '👍', '👏', '🙌', '👌', '🙇', '🙇‍♂️', '🙇‍♀️', '🙏', '🫡', '🥳', '💯', '🆒', '🎉', '🎊', '🍻', '🥂'],
        ['😀', '😃', '😄', '😁', '😅', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '🎄', '🏆', '🐥', '🎃', '🈴', '🈵', '🉑'],
        ['🤔', '👀', '🙀', '😮‍💨', '😔', '😴', '😷', '🥵', '😵', '🧐', '🥺', '😨', '😰', '😢', '🫟', '🆘', '⭕', '❌', '❎', '👎'],
    ];

    public function mount(int $bbMesId, int $userId)
    {
        $this->bbMesId = $bbMesId;
        $this->userId = $userId;
    }

    #[On('echo:bb-mes.{bbMesId},.reaction.updated')]
    public function refreshReactions()
    {
        // Echoからの通知を受けて再レンダリングするだけでよい
    }

    public function render()
    {
        $reactions = \App\Models\BbMesReaction::where('bb_mes_id', $this->bbMesId)
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

        return view('livewire.bb-mes-reaction-editor', [
            'reactions' => $reactions,
        ]);
    }

    public function toggleReaction($emoji)
    {
        // MySQLの絵文字の照合順序は別の絵文字を同一視することがあるため、DB側では絞り込まずPHPで厳密比較する
        $existing = \App\Models\BbMesReaction::where('bb_mes_id', $this->bbMesId)
            ->where('user_id', $this->userId)
            ->get()
            ->first(fn($reaction) => $reaction->emoji === $emoji);

        if ($existing) {
            $existing->delete();
        } else {
            \App\Models\BbMesReaction::create([
                'bb_mes_id' => $this->bbMesId,
                'user_id' => $this->userId,
                'emoji' => $emoji,
            ]);
        }

        broadcast(new BbMesReactionUpdated($this->bbMesId));
    }
}
