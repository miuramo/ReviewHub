<?php

namespace App\Livewire;

use Livewire\Component;

class ReviewLock extends Component
{
    public $review;
    public $can_manage;
    public function mount($review)
    {
        $this->review = $review;
        $this->can_manage = auth()->user()->can('manage_review', $this->review->paper_id);
    }
    public function render()
    {
        return view('livewire.review-lock');
    }
    public function lock()
    {
        //権限があれば、ロックする。権限がなければ、ロックできない。
        if ($this->can_manage) {
            $this->review->locked = true;
            $this->review->save();
        }
    }
    public function unlock()
    {
        if ($this->can_manage) {
            $this->review->locked = false;
            $this->review->save();
        }
    }
}
