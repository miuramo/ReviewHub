<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('フォーラム作成') }}
        </h2>
    </x-slot>
    @section('title', 'フォーラム作成')

    <div class="py-4 px-6 max-w-xl">
        @if ($errors->any())
            <x-alert.error>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </x-alert.error>
        @endif

        <form method="POST" action="{{ route('forum.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">タイトル</label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:border-indigo-400"
                    required maxlength="255" placeholder="フォーラムのタイトルを入力">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">役職（対象委員会）</label>
                <select name="post_id"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:border-indigo-400"
                    required>
                    <option value="">選択してください</option>
                    @foreach ($posts as $post)
                        <option value="{{ $post->id }}" {{ old('post_id') == $post->id ? 'selected' : '' }}>
                            {{ $post->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <x-element.submitbutton color="indigo">作成する</x-element.submitbutton>
                <x-element.linkbutton href="{{ route('forum.index') }}" color="gray" size="sm">キャンセル</x-element.linkbutton>
            </div>
        </form>
    </div>
</x-app-layout>
