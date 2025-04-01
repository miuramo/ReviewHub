<?php

namespace App\Livewire;

use Livewire\Component;

class PaperLock extends Component
{
    public $paper;

    public function render()
    {
        return view('livewire.paper-lock');
    }

    public function lock()
    {
        $this->paper->locked = true;
        $this->paper->save();
        
    }
    public function unlock()
    {
        $this->paper->locked = false;
        $this->paper->save();
    }
}
