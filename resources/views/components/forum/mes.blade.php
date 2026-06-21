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
        '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-800 break-all">$1</a>',
        $body
    );
    $is_mine  = $mes->user_id === auth()->id();
    $is_system = $mes->user_id == 0 || $mes->user_id === null;
    $indent   = min($depth, 8) * 2; // rem 単位（最大16rem）
    $bg       = $is_system  ? 'bg-yellow-50 border-yellow-200'
              : ($is_mine   ? 'bg-indigo-100 border-indigo-300'
                            : 'bg-slate-100 border-slate-300');
    $textBg   = $is_system  ? ''
              : ($is_mine   ? 'bg-indigo-50'
                            : 'bg-white');
    $nameColor = $is_mine   ? 'text-indigo-800' : 'text-slate-700';
@endphp

<div style="margin-left: {{ $indent }}rem" class="my-1">
    @if ($is_system)
        {{-- システムメッセージ --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-md px-3 py-2 text-sm text-gray-600">
            <div class="font-semibold">{{ $mes->subject }}</div>
            <div>{!! nl2br($body) !!}</div>
            <div class="text-right text-xs text-gray-400 mt-1">{{ $mes->created_at }}</div>
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
                <span class="text-xs text-gray-500 ml-2">{{ $mes->created_at }}</span>
            </div>
            <div class="{{ $textBg }} rounded-md px-2 py-1 text-sm">{!! nl2br($body) !!}</div>

            @unless ($isClose)
                <div class="mt-1 text-right">
                    <button type="button" @click="replyOpen = !replyOpen"
                        class="text-xs text-indigo-600 hover:text-indigo-800 underline">
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
                        class="w-full text-sm p-1 mb-1 border border-indigo-300 rounded-md bg-white"
                        onkeydown="return disableEnterKey(event);">
                    <textarea name="mes" rows="3" required
                        placeholder="返信メッセージを入力"
                        class="w-full text-sm p-1 border border-indigo-300 rounded-md bg-white"></textarea>
                    <div class="text-right mt-1">
                        <button type="button" @click="replyOpen = false"
                            class="text-xs text-gray-500 hover:text-gray-700 mr-2">キャンセル</button>
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
