<?php

namespace App\Livewire;

use Livewire\Component;

class ChangeReviewTarget extends Component
{
    public $review = null;
    public $target = 0;
    public $is_editing = false;
    public $selection = [];
    public function mount($review, $selection = [])
    {
        $this->review = $review;
        $this->target = $review->target;
        $this->selection = $selection;
    }

    public function render()
    {
        return view('livewire.change-review-target');
    }
    public function save()
    {
        $this->is_editing = false;
        $this->review->target = $this->target;
        $this->review->save();
    }
}
