<div class="m-0 inline-block">
    {{-- <input type="checkbox" wire:model="locked" id="paper_locked" name="paper_locked"> --}}
    {{-- In work, do what you enjoy. --}}
    {{-- {{$paper->locked ? '投稿はロックされています' : '投稿はロックされていません'}} --}}
    @if ($file->locked)
        <button title="click to unlock" wire:click="unlock"
            class="bg-cyan-200 text-cyan-800 border-2 border-cyan-500 px-1 py-0.5 rounded-lg inline-block">Locked
        </button>
    @else
        <button title="click to lock" wire:click="lock" class="bg-orange-300 text-orange-800 border-2 border-orange-500 px-1 py-0.5 rounded-lg">Unlocked
        </button>
    @endif
</div> {{-- Success is as dangerous as failure. --}}
