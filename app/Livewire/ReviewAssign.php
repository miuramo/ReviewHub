<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;

class ReviewAssign extends Component
{
    public $submit_id;
    public $users = [];
    public $revuids = [];
    public $search = '';
    public $paper_id;

    public function mount()
    {
        $this->revuids = Role::findByIdOrName('rev')->uids();
        // $this->users = User::whereIn('id', $this->revuids)->limit(5)->get();
    }

    public function render()
    {
        $this->updatedSearch();
        return view('livewire.review-assign');
    }
    public function updatedSearch()
    {
        $this->search = trim($this->search);
        if ($this->search == '') {
            $this->users = collect();//User::whereIn('id', $this->revuids)->orderByDesc('id')->limit(5)->get();
        } else {
            $search = $this->search;
            $this->users = User::whereIn('id', $this->revuids)->where(function($qr) use ($search){
                $qr->orWhere('name', 'like', "%{$search}%");
                $qr->orWhere('email', 'like', "%{$search}%");
                $qr->orWhere('affil', 'like', "%{$search}%");
            })->limit(30)->get();
        }
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->users = User::whereIn('id', $this->revuids)->orderByDesc('id')->limit(5)->get();
        $this->render();
    }
}
