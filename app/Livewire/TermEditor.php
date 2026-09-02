<?php

namespace App\Livewire;

use Livewire\Component;

class TermEditor extends Component
{
    public $is_editing = false;
    public $term;
    public $post_id;
    public $selection = [];
    public function mount($term)
    {
        $this->term = $term;
        $this->post_id = $term->post->id;
        $this->selection = \App\Models\Post::pluck('name', 'id')->toArray();
    }
    public function render()
    {
        return view('livewire.term-editor');
    }
    public function save()
    {
        $this->term->post_id = $this->post_id;
        $this->term->save();
        $this->is_editing = false;
    }
}
