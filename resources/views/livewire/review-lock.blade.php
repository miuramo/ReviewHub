<span class="my-1 inline-block">
    @if ($review->locked)
        <button title="click to unlock" wire:click="unlock"
            class="bg-cyan-200 text-cyan-800 border-2 border-cyan-500 px-1 py-0.5 rounded-lg inline-block 
            @if(!$can_manage) cursor-not-allowed opacity-50 @endif">Locked
        </button>
    @else
        <button title="click to lock" wire:click="lock" class="bg-orange-300 text-orange-800 border-2 border-orange-500 px-1 py-0.5 rounded-lg @if(!$can_manage) cursor-not-allowed opacity-50 @endif">Unlocked
        </button>
    @endif
</span> 
