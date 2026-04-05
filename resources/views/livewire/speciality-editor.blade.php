<div>
    @if ($is_editing)
        <div class="mb-4">
            <div>
                <x-setcolor-button wire:click="save" color="cyan">{{ __('Save') }}</x-setcolor-button>
            </div>
            @foreach ($this->specialities as $index => $speciality)
                <div class="flex items-center mb-2">
                    <span class="mr-2">{{ $index + 1 }}.</span>
                    <x-text-input type="text" wire:model="specialities.{{ $index }}.name" wire:blur="userUpdatedSpecialities"
                        class="border rounded px-2 py-1" size=60 placeholder="{{ __('Specialities') }}"/>
                    <x-setcolor-button wire:click="removeSpeciality({{ $index }})" color="red" class="ml-2">{{ __('Remove') }}</x-setcolor-button>
                </div>
            @endforeach
            <div class="mt-4">
                <x-text-input type="text" wire:model="keyword" class="mt-1 block w-full rounded" size=50
                    placeholder="{{ __('New Speciality') }}"/>
                <x-setcolor-button wire:click="addSpeciality" color="blue">{{ __('Add Speciality') }}</x-setcolor-button>
            </div>
        </div>
    @else
        <div class="mb-4">
            <x-setcolor-button wire:click="$set('is_editing', true)" color="green" class="px-4 py-2 rounded">{{ __('Edit Specialities') }}</x-setcolor-button>
            <ul class="mt-4">
                @foreach ($this->specialities as $index => $speciality)
                    <li class="mb-1">{{ $index + 1 }}. {{ $speciality['name'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
