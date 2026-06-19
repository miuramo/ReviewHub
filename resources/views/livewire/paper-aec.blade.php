<div>
    <x-element.h1c color="yellow">
        @if ($aec_id != null)
            担当幹事：
            <x-element.login_as :user="App\Models\User::find($aec_id)"></x-element.login_as>
        @else
            担当幹事：未設定
        @endif
        <span class="mx-4"></span>

        @if ($is_editing)
            <div class="mt-2">
                <select wire:model="aec_id" class="border-gray-300 rounded-md shadow-sm">
                    <option value="">担当幹事なし</option>
                    @foreach ($paper->managers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->affil }})</option>
                    @endforeach
                </select>
                <button wire:click="saveAec" class="ml-2 px-3 py-1 bg-green-500 text-white rounded">保存</button>
                <button wire:click="$set('is_editing', false)"
                    class="ml-2 px-3 py-1 bg-gray-500 text-white rounded">キャンセル</button>
            </div>
        @elseif (auth()->user()->can('manage_papermanager', $paper->id))
                <button wire:click="$set('is_editing', true)" class="ml-2 px-3 py-1 bg-cyan-500 text-white rounded text-sm">
                    担当幹事を変更する
                </button>
        @endif
    </x-element.h1c>
    {{-- The Master doesn't talk, he acts. --}}
</div>
