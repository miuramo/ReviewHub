<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TruncateTable extends Component
{
    public $table;
    public $count;
    public function render()
    {
        return view('livewire.truncate-table');
    }
    public function truncate()
    {
        info("TRUNCATE TABLE " .$this->table);
        DB::statement("TRUNCATE TABLE {$this->table}");
        $this->count = 0;
    }
}
