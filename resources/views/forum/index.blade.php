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

        @if ($accessiblePosts->isEmpty())
            <p class="text-gray-500">アクセス可能なフォーラムはありません。（有効な任期がない可能性があります）</p>
        @else
            @foreach ($accessiblePosts as $post)
                @php
                    $forumsForPost = $groupedForums->get($post->id, collect());
                @endphp
                <div class="mb-6">
                    <h3 class="text-base font-semibold text-gray-700 dark:text-slate-300 border-b border-gray-300 dark:border-slate-600 pb-1 mb-2">
                        {{ $post->name }} のフォーラム
                    </h3>

                    @if ($forumsForPost->isEmpty())
                        <p class="text-sm text-gray-400 pl-2">現在、{{ $post->name }}用のフォーラムはありません。</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($forumsForPost as $forum)
                                @php
                                    $fy = $forum->fiscal_year();
                                @endphp
                                <div class="flex items-center gap-3 bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-md p-3 hover:bg-indigo-50 dark:hover:bg-slate-600 transition-colors">
                                    <div class="flex-1">
                                        <a href="{{ route('forum.show', ['forum' => $forum->id]) }}"
                                           class="font-semibold text-indigo-700 dark:text-indigo-300 hover:underline">
                                            {{ $forum->title }}
                                        </a>
                                        <div class="text-xs text-gray-500 mt-1">
                                            作成: {{ $forum->created_at->format('Y-m-d') }}（{{ $fy }}年度）／
                                            作成者: {{ $forum->user->name ?? '(不明)' }}
                                            ／ メッセージ数: {{ $forum->messages->count() }}
                                            @if ($forum->isclose)
                                                <span class="ml-2 px-1 py-0.5 bg-red-200 text-red-700 rounded text-xs">締め切り済み</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <x-element.linkbutton href="{{ route('forum.show', ['forum' => $forum->id]) }}" color="indigo" size="sm">
                                            開く
                                        </x-element.linkbutton>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</x-app-layout>
