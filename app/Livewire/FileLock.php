<?php

namespace App\Livewire;

use Livewire\Component;

class FileLock extends Component
{
    public $file;
    
    public function render()
    {
        return view('livewire.file-lock');
    }

    public function lock()
    {
        $this->file->locked = true;
        $this->file->save();
    }
    public function unlock()
    {
        $this->file->locked = false;
        $this->file->save();
    }
}
