<?php

namespace App\Livewire;

use App\Models\Bb;
use Livewire\Attributes\On;
use Livewire\Component;

class BbThread extends Component
{
    public int $bbId;

    public function mount(int $bbId)
    {
        $this->bbId = $bbId;
    }

    #[On('echo:bb.{bbId},.message.posted')]
    public function refreshMessages()
    {
        // Echoからの通知を受けて再レンダリングするだけでよい
    }

    #[On('echo:bb.{bbId},.read.updated')]
    public function refreshReadStatus()
    {
        // Echoからの通知を受けて再レンダリングするだけでよい
    }

    public function render()
    {
        $bb = Bb::with(['messages.user', 'messages.files', 'messages.reads'])->findOrFail($this->bbId);
        $bb->markMessagesAsRead(auth()->id());

        return view('livewire.bb-thread', [
            'bb' => $bb,
            'readStatuses' => $bb->readStatuses(),
            'readStatusUrl' => route('bb.read_status', ['bb' => $bb->id, 'key' => $bb->key]),
        ]);
    }
}
