<?php

namespace App\Livewire;

use Livewire\Component;

class ReviewLock extends Component
{
    public $review;
    public function render()
    {
        return view('livewire.review-lock');
    }
    public function lock()
    {
        $this->review->locked = true;
        $this->review->save();
    }
    public function unlock()
    {
        $this->review->locked = false;
        $this->review->save();
    }
}
