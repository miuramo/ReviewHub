@props([
    'mes'     => null,
    'depth'   => 0,
    'isClose' => false,
])
{{-- components/forum/mes.blade.php --}}
@php
    $body     = htmlspecialchars($mes->mes ?? '', ENT_QUOTES, 'UTF-8');
    $body     = preg_replace(
        '/(https?:\/\/[^\s<>"\']+)/u',
        '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-300 underline hover:text-blue-800 dark:hover:text-blue-200 break-all">$1</a>',
        $body
    );
    $is_mine  = $mes->user_id === auth()->id();
    $is_system = $mes->user_id == 0 || $mes->user_id === null;
    $indent   = min($depth, 8) * 2; // rem 単位（最大16rem）
    $bg       = $is_system  ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900 dark:border-yellow-700'
              : ($is_mine   ? 'bg-indigo-100 border-indigo-300 dark:bg-indigo-900 dark:border-indigo-700'
                            : 'bg-slate-100 border-slate-300 dark:bg-slate-700 dark:border-slate-600');
    $textBg   = $is_system  ? ''
              : ($is_mine   ? 'bg-indigo-50 dark:bg-indigo-800'
                            : 'bg-white dark:bg-slate-800');
    $nameColor = $is_mine   ? 'text-indigo-800 dark:text-indigo-200' : 'text-slate-700 dark:text-slate-200';
@endphp

<div id="mes-{{ $mes->id }}" style="margin-left: {{ $indent }}rem" class="my-1">
    @if ($is_system)
        {{-- システムメッセージ --}}
        <div class="bg-yellow-50 border border-yellow-200 dark:bg-yellow-900 dark:border-yellow-700 rounded-md px-3 py-2 text-sm text-gray-600 dark:text-gray-200">
            <div class="font-semibold">{{ $mes->subject }}</div>
            <div>{!! nl2br($body) !!}</div>
            <div class="text-right text-xs text-gray-400 dark:text-yellow-300 mt-1">{{ $mes->created_at }}</div>
        </div>
    @else
        {{-- 通常メッセージ --}}
        <div x-data="{ replyOpen: false }" class="border {{ $bg }} rounded-lg p-2">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-semibold {{ $nameColor }}">
                    {{ $mes->user->name ?? '(不明)' }}
                    @if ($mes->subject)
                        ／ {{ $mes->subject }}
                    @endif
                </span>
                <span class="text-xs text-gray-500 dark:text-slate-400 ml-2">{{ $mes->created_at }}</span>
            </div>
            <div class="{{ $textBg }} rounded-md px-2 py-1 text-sm dark:text-gray-200">{!! nl2br($body) !!}</div>

            @unless ($isClose)
                <livewire:forum-mes-reaction-editor :forum-mes-id="$mes->id" :user-id="auth()->id()" :key="'forum-mes-reaction-'.$mes->id" />
                <div class="mt-1 text-right">
                    <button type="button" @click="replyOpen = !replyOpen"
                        class="text-xs text-indigo-600 dark:text-indigo-300 hover:text-indigo-800 dark:hover:text-indigo-200 underline">
                        返信
                    </button>
                </div>
                <form x-show="replyOpen" x-cloak
                    action="{{ route('forum.mes.store', ['forum' => $mes->forum_id]) }}"
                    method="POST" class="mt-2">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $mes->id }}">
                    <input type="text" name="sub"
                        placeholder="件名（省略可）"
                        class="w-full text-sm p-1 mb-1 border border-indigo-300 dark:border-indigo-600 rounded-md bg-white dark:bg-slate-800 dark:text-slate-100"
                        onkeydown="return disableEnterKey(event);">
                    <textarea name="mes" rows="3" required
                        placeholder="返信メッセージを入力"
                        class="w-full text-sm p-1 border border-indigo-300 dark:border-indigo-600 rounded-md bg-white dark:bg-slate-800 dark:text-slate-100"></textarea>
                    <div class="text-right mt-1">
                        <button type="button" @click="replyOpen = false"
                            class="text-xs text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 mr-2">キャンセル</button>
                        <button type="submit"
                            class="text-xs bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded-md">
                            返信する
                        </button>
                    </div>
                </form>
            @endunless
        </div>
    @endif

    {{-- 返信を再帰的に表示 --}}
    @foreach ($mes->replies as $reply)
        <x-forum.mes :mes="$reply" :depth="$depth + 1" :is-close="$isClose" />
    @endforeach
</div>
