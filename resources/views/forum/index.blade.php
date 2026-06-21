<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('委員会フォーラム一覧') }}
        </h2>
    </x-slot>
    @section('title', '委員会フォーラム')

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-4 px-6">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('forum.create') }}" color="indigo" size="sm">
                + 新しいフォーラムを作成
            </x-element.linkbutton>
        </div>

        @livewire('forum-index')
    </div>
</x-app-layout>
