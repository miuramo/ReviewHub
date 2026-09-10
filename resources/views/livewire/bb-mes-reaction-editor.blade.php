<div class="relative flex flex-wrap items-center gap-1 mt-1" x-data="{ open: false }"
    @click.outside="open = false" @keydown.escape.window="open = false">
    @foreach ($reactions as $reaction)
        <button type="button" wire:key="reaction-{{ $bbMesId }}-{{ $reaction['emoji'] }}"
            wire:click="toggleReaction('{{ $reaction['emoji'] }}')" title="{{ $reaction['userNames'] }}"
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-sm border
                {{ $reaction['reacted'] ? 'bg-blue-100 border-blue-400' : 'bg-white border-gray-300 hover:bg-gray-100' }}">
            <span>{{ $reaction['emoji'] }}</span>
            <span class="text-xs text-gray-600">{{ $reaction['count'] }}</span>
        </button>
    @endforeach

    <button type="button" wire:key="picker-toggle-{{ $bbMesId }}" @click="open = !open"
        class="inline-flex items-center justify-center w-6 h-6 rounded-full border border-gray-300 bg-white hover:bg-gray-100 text-gray-500 text-sm">
        ＋
    </button>

    <div x-show="open" x-cloak @mouseleave="open = false" wire:key="picker-popover-{{ $bbMesId }}"
        class="absolute bottom-full left-0 z-10 mb-1 w-72 max-h-56 overflow-y-auto rounded-lg border border-gray-300 bg-white p-2 shadow-lg">
        @foreach ($emojiGroups as $groupIndex => $group)
            <div class="flex flex-wrap gap-1 mb-1" wire:key="picker-group-{{ $bbMesId }}-{{ $groupIndex }}">
                @foreach ($group as $emoji)
                    <button type="button" wire:key="picker-emoji-{{ $bbMesId }}-{{ $emoji }}" @click="open = false"
                        wire:click="toggleReaction('{{ $emoji }}')"
                        class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100 text-lg">
                        {{ $emoji }}
                    </button>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
