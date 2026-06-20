<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ $forum->title }}
            @if ($forum->isclose)
                <span class="ml-3 text-sm font-normal px-2 py-0.5 bg-red-200 text-red-700 rounded">締め切り済み</span>
            @endif
        </h2>
        <div class="text-sm text-gray-500 mt-1">
            {{ $forum->post->name ?? '' }} ／
            作成: {{ $forum->created_at->format('Y-m-d') }}（{{ $forum->fiscal_year() }}年度）／
            作成者: {{ $forum->user->name ?? '(不明)' }}
        </div>
    </x-slot>
    @section('title', $forum->title . ' フォーラム')

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-2 px-6">
        <div class="mb-3">
            <x-element.linkbutton href="{{ route('forum.index') }}" color="gray" size="sm">
                ← フォーラム一覧へ戻る
            </x-element.linkbutton>

            @if (auth()->user()->can('role_any', 'admin|manager'))
                <span class="mx-2"></span>
                <form method="POST" action="{{ route('forum.destroy', ['forum' => $forum->id]) }}"
                    class="inline" onsubmit="return confirm('このフォーラムを削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <x-element.submitbutton color="pink" size="sm">削除（管理者）</x-element.submitbutton>
                </form>
            @endif
        </div>

        {{-- メッセージ一覧 --}}
        @foreach ($forum->messages as $mes)
            <x-forum.mes :mes="$mes"></x-forum.mes>
        @endforeach

        {{-- 書き込みフォーム --}}
        @unless ($forum->isclose)
            <div class="mt-6 text-right">
                <form action="{{ route('forum.mes.store', ['forum' => $forum->id]) }}" method="POST" id="post_forummes">
                    @csrf
                    <div class="inline-block w-3/4 bg-indigo-200 p-3 rounded-md hover:bg-indigo-300 transition-colors duration-300">
                        <div class="px-2 text-left text-sm mb-1">送信フォーム</div>
                        <input class="w-full p-2 bg-indigo-100 rounded-md border-indigo-300 border mb-1" type="text"
                            name="sub" placeholder="Subject（件名）を入力"
                            onkeydown="return disableEnterKey(event);">
                        <textarea class="w-full p-2 bg-white rounded-md border-indigo-300 border" name="mes"
                            rows="8" placeholder="メッセージを入力してください" required></textarea>
                        <div class="mt-2">
                            <x-element.submitbutton color="indigo">書き込む</x-element.submitbutton>
                        </div>
                    </div>
                </form>
            </div>
        @endunless
    </div>
</x-app-layout>
