<td class="p-1 text-center">
    @if ($is_editing)
        <select wire:model.live="target" wire:keydown.escape="$set('is_editing', false)" x-init="$el.focus()"
            class="border border-gray-300 rounded px-2 pr-8 py-0.5">
            @foreach ($selection as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
        <button wire:click="save" class="ml-1 px-2 py-1 bg-red-500 text-white rounded">save</button>
    @else
        <button wire:click="$set('is_editing', true)">{{ $selection[$review->target] ?? '未設定' }}</button>
    @endif
</td>
