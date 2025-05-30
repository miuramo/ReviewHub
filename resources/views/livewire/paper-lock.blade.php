<div class="mx-2 my-1">
    {{-- <input type="checkbox" wire:model="locked" id="paper_locked" name="paper_locked"> --}}
    {{-- In work, do what you enjoy. --}}
    {{-- {{$paper->locked ? '投稿はロックされています' : '投稿はロックされていません'}} --}}
    @if($paper->locked)
        <span class="bg-cyan-200 text-cyan-800 border-2 border-cyan-500 px-2 py-1 rounded-lg">現在、投稿はロックされています</span>
        <button wire:click="unlock" class="bg-orange-200 hover:bg-orange-400 rounded-lg p-1 px-2 text-sm dark:bg-orange-500">ロックを解除する
        </button>
    @else
    <span class="bg-orange-300 text-orange-800 border-2 border-orange-500 px-2 py-1 rounded-lg">現在、投稿はロックされていません</span>
    <button wire:click="lock" class="bg-cyan-200 hover:bg-cyan-300 rounded-lg p-1 px-2 text-sm">ロックする
        </button>
    @endif
</div>
