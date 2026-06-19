<?php

namespace App\Livewire;

use Livewire\Component;

class PaperAec extends Component
{
    public $paper;
    public $aec_id;
    public $is_editing = false;

    public function mount($paper)
    {
        $this->paper = $paper;
        $this->aec_id = $paper->aec_id;
    }
    public function render()
    {
        return view('livewire.paper-aec');
    }
    public function saveAec()
    {
        $this->paper->aec_id = $this->aec_id;
        $this->paper->save();
        $this->is_editing = false;
    }
}
