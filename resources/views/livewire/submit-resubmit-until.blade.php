<span>
    @if ($is_editing)
        現在の再提出期限：{{ $submit->resubmit_until ?? '未設定' }}<br>
        <input type="date" wire:model.live="resubmit_until" wire:keydown.escape="$set('is_editing', false)"
            x-init="$el.focus()" class="border border-gray-300 rounded px-1 py-0.5">
        <button wire:click="save" class="ml-1 px-2 py-1 bg-red-500 text-white rounded">save</button>
        @error('resubmit_until')
            <span class="text-blue-600 text-xs">{{ $message }}</span>
        @enderror
    @else
        <button wire:click="$set('is_editing', true)">{{ $submit->resubmit_until ?? '---' }}</button>
    @endif
</span>
