<?php

namespace App\Livewire;

use Livewire\Component;

class SubmitResubmitUntil extends Component
{
    public $submit;
    public $is_editing = false;
    public $resubmit_until;

    public function mount($submit)
    {
        $this->submit = $submit;
        $this->resubmit_until = $submit->resubmit_until;
    }

    public function render()
    {
        return view('livewire.submit-resubmit-until');
    }

    public function save()
    {
        $this->validate(['resubmit_until' => 'nullable|date']);
        $this->is_editing = false;
        $this->submit->resubmit_until = $this->resubmit_until ?: null;
        $this->submit->save();
    }
}
