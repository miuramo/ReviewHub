<span>
    @if ($is_editing)
        現在の予定締切日：{{ $task->due_date }}<br>
        <input type="date" wire:model.live="due_date" wire:keydown.escape="$set('is_editing', false)"
            x-init="$el.focus()" class="border border-gray-300 rounded px-1 py-0.5">
        <button wire:click="save" class="ml-1 px-2 py-1 bg-red-500 text-white rounded">save</button>
    @else
        <button wire:click="$set('is_editing', true)">予定締切日: {{ $task->due_date ?? '未設定' }}</button>
    @endif
    {{-- Care about people's approval and you will be their prisoner. --}}
</span>
