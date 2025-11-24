<?php

namespace App\Livewire;

use Livewire\Component;

class TaskDuedate extends Component
{
    public $task;
    public $is_editing = false;
    public $due_date;
    public function mount($task)
    {
        $this->task = $task;
        $this->due_date = $task->due_date;
    }
    public function render()
    {
        return view('livewire.task-duedate');
    }
    public function save()
    {
        $this->is_editing = false;
        $old_due_date = $this->task->due_date;
        $this->task->due_date = $this->due_date;
        $this->task->save();
        $this->task->logappend('due_date old=' . $old_due_date . ' new=' . $this->due_date, auth()->id());
    }
}
