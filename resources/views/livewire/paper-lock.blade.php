<div class="mx-2 my-1">
    {{-- <input type="checkbox" wire:model="locked" id="paper_locked" name="paper_locked"> --}}
    {{-- In work, do what you enjoy. --}}
    {{-- {{$paper->locked ? '投稿はロックされています' : '投稿はロックされていません'}} --}}
    @if ($paper->locked)
        <button title="クリックするとロックを解除する" wire:click="unlock"
            class="bg-cyan-200 text-cyan-800 border-2 border-cyan-500 px-2 py-1 rounded-lg">
            現在、投稿はロックされています
        </button>
    @else
        <button title="クリックするとロックする" wire:click="lock" class="bg-orange-300 text-orange-800 border-2 border-orange-500 px-2 py-1 rounded-lg">
            現在、投稿はロックされていません
        </button>
    @endif
    <span class="text-sm">← クリックでロックの切り替えができます</span>
</div>
