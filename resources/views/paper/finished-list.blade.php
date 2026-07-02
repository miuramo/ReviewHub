<x-app-layout>
    <!-- paper.index -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('すべての投稿') }} （下スクロールまたはボタンで続きを表示）
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-2 px-6">
        <livewire:inf-list />
    </div>
    
    @push('localjs')
        <script src="/js/sortable.js"></script>
    @endpush

</x-app-layout>
