<div>

    <x-paper.psummarytable2 :all="$papers" :heads="$heads" size="sm" />

    {{-- スクロール検知用 --}}
    {{-- <div x-data x-intersect="$wire.loadMore()" class="h-10"></div> --}}

    <div wire:loading wire:target="loadMore" class="text-center p-4">
        読み込み中...
    </div>

    {{-- 「続きを表示」ボタン（スクロールで反応しない場合や明示的に押したいユーザー用） --}}
    @if ($perPage < $total)
        <div class="text-center my-4">
            <button
                type="button"
                @click="$wire.loadMore().then(() => sortables_init())"
                wire:loading.attr="disabled"
                wire:target="loadMore"
                @disabled($isLoading)
                class="px-4 py-2 border rounded hover:bg-gray-50"
            >
                {{-- wire:loading 内のコンテンツは読み込み時に自動で表示できます --}}
                <span wire:loading.remove wire:target="loadMore">つづきを表示</span>
                <span wire:loading wire:target="loadMore">読み込み中…</span>
            </button>
        </div>
    @else
        <div class="text-center my-4 text-sm text-gray-500">
            これより前の投稿はありません
        </div>
    @endif
</div>
