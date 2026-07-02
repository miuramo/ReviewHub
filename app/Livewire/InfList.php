<?php

namespace App\Livewire;

use App\Models\Paper;
use Livewire\Component;

class InfList extends Component
{
    public $perPage = 10;
    public int $increment = 10;
    public bool $isLoading = false;
    public int $total = 0;
    public $heads = ['id', '種別', 'status', 'title / author', '投稿日時', '投稿者', '査-状況'];



    protected $listeners = [
        'load-more' => 'loadMore',
    ];

    public function mount(): void
    {
        // 全件数を取得（キャッシュや別の方法でも可）
        $this->total = Paper::count();
    }

    public function loadMore()
    {
        // 多重呼び出し防止
        if ($this->isLoading) {
            return;
        }

        // すでに全件取得済みなら何もしない
        if ($this->perPage >= $this->total) {
            return;
        }

        $this->isLoading = true;

        // 件数を増やす（ここでは単純に perPage を増やす方式）
        $this->perPage = min($this->perPage + $this->increment, $this->total);

        // 必要ならここで追加の処理（ログ、Analytics等）

        $this->isLoading = false;
    }
    public function render()
    {
        return view('livewire.inf-list', [
            'papers' => Paper::latest()->take($this->perPage)->get(),
        ]);
    }
}
