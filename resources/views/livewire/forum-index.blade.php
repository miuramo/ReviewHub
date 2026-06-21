<div>
    {{-- キーワード検索 --}}
    <div class="mb-5 flex items-center gap-3">
        <div class="relative">
            <input type="text"
                   wire:model.live.debounce.400ms="keyword"
                   class="border border-gray-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 rounded-md pl-9 pr-3 py-1.5 text-sm w-72 focus:outline-none focus:ring focus:border-indigo-400"
                   placeholder="メッセージをキーワード検索...">
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </span>
        </div>
        @if ($keyword !== '')
            <button wire:click="$set('keyword', '')"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200 underline">
                クリア
            </button>
        @endif
        <div wire:loading wire:target="keyword" class="text-xs text-indigo-500">検索中...</div>
    </div>

    @if ($accessiblePosts->isEmpty())
        <p class="text-gray-500">アクセス可能なフォーラムはありません。（有効な任期がない可能性があります）</p>
    @else
        @foreach ($accessiblePosts as $post)
            @php
                $paginator = $paginatedByPost[$post->id];
                $forums    = $paginator->items();
            @endphp
            <div class="mb-6">
                <h3 class="text-base font-semibold text-gray-700 dark:text-slate-300 border-b border-gray-300 dark:border-slate-600 pb-1 mb-2">
                    {{ $post->name }} のフォーラム
                    @if ($paginator->total() > 0)
                        <span class="ml-1 text-xs font-normal text-gray-400 dark:text-slate-500">（{{ $paginator->total() }}件）</span>
                    @endif
                </h3>

                @if ($paginator->total() === 0)
                    <p class="text-sm text-gray-400 pl-2">
                        @if ($keyword !== '')
                            「{{ $keyword }}」に一致するフォーラムはありません。
                        @else
                            現在、{{ $post->name }}用のフォーラムはありません。
                        @endif
                    </p>
                @else
                    <div class="space-y-2">
                        @foreach ($forums as $forum)
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

                    {{-- ページネーション --}}
                    @if ($paginator->lastPage() > 1)
                        @php
                            $current  = $paginator->currentPage();
                            $last     = $paginator->lastPage();
                            $postId   = $post->id;
                            // 表示するページ番号の範囲（現在 ±2、先頭・末尾は常に表示）
                            $range    = range(max(1, $current - 2), min($last, $current + 2));
                        @endphp
                        <div class="mt-3 flex items-center flex-wrap gap-1 text-sm select-none">
                            {{-- 前ページ --}}
                            <button wire:click="gotoPage({{ $postId }}, {{ $current - 1 }})"
                                    @disabled($current <= 1)
                                    class="px-2 py-1 border rounded text-gray-600 dark:text-slate-300 dark:border-slate-500 hover:bg-gray-100 dark:hover:bg-slate-600 disabled:opacity-40 disabled:cursor-not-allowed">
                                &laquo;
                            </button>

                            {{-- 先頭ページ --}}
                            @if (!in_array(1, $range))
                                <button wire:click="gotoPage({{ $postId }}, 1)"
                                        class="px-2 py-1 border rounded text-gray-600 dark:text-slate-300 dark:border-slate-500 hover:bg-gray-100 dark:hover:bg-slate-600">
                                    1
                                </button>
                                @if ($range[0] > 2)
                                    <span class="px-1 text-gray-400">…</span>
                                @endif
                            @endif

                            {{-- 中間ページ群 --}}
                            @foreach ($range as $p)
                                <button wire:click="gotoPage({{ $postId }}, {{ $p }})"
                                        class="px-2 py-1 border rounded {{ $p === $current ? 'bg-indigo-600 text-white border-indigo-600' : 'text-gray-600 dark:text-slate-300 dark:border-slate-500 hover:bg-gray-100 dark:hover:bg-slate-600' }}">
                                    {{ $p }}
                                </button>
                            @endforeach

                            {{-- 末尾ページ --}}
                            @if (!in_array($last, $range))
                                @if ($range[array_key_last($range)] < $last - 1)
                                    <span class="px-1 text-gray-400">…</span>
                                @endif
                                <button wire:click="gotoPage({{ $postId }}, {{ $last }})"
                                        class="px-2 py-1 border rounded text-gray-600 dark:text-slate-300 dark:border-slate-500 hover:bg-gray-100 dark:hover:bg-slate-600">
                                    {{ $last }}
                                </button>
                            @endif

                            {{-- 次ページ --}}
                            <button wire:click="gotoPage({{ $postId }}, {{ $current + 1 }})"
                                    @disabled(!$paginator->hasMorePages())
                                    class="px-2 py-1 border rounded text-gray-600 dark:text-slate-300 dark:border-slate-500 hover:bg-gray-100 dark:hover:bg-slate-600 disabled:opacity-40 disabled:cursor-not-allowed">
                                &raquo;
                            </button>

                            <span class="ml-2 text-xs text-gray-500 dark:text-slate-400">
                                {{ $current }} / {{ $last }} ページ
                            </span>
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    @endif
</div>
