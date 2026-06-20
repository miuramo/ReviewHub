@props([
    'mes' => [],
])
{{-- components/forum/mes.blade.php --}}
@php
    $body = htmlspecialchars($mes->mes ?? '', ENT_QUOTES, 'UTF-8');
    $is_mine = $mes->user_id === auth()->id();
    $is_system = $mes->user_id == 0 || $mes->user_id === null;
@endphp

@if ($is_system)
    {{-- システムメッセージ --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-md px-3 py-2 my-2 text-sm text-gray-600">
        <div class="font-semibold">{{ $mes->subject }}</div>
        <div>{!! nl2br($body) !!}</div>
        <div class="text-right text-xs text-gray-400 mt-1">{{ $mes->created_at }}</div>
    </div>
@elseif ($is_mine)
    {{-- 自分のメッセージ（右寄せ） --}}
    <div class="text-right my-1">
        <div class="inline-block w-3/4 bg-indigo-200 p-2 rounded-lg">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-semibold text-indigo-800"> {{ $mes->user->name ?? '(不明)' }}
                    @if ($mes->subject)
                        ／ {{ $mes->subject }}
                    @endif
                </span>
                <span class="text-xs text-gray-500 ml-2">{{ $mes->created_at }}</span>
            </div>
            <div class="bg-indigo-50 rounded-md px-2 py-1 text-left">{!! nl2br($body) !!}</div>
        </div>
    </div>
@else
    {{-- 他のユーザのメッセージ（左寄せ） --}}
    <div class="text-left my-1">
        <div class="inline-block w-3/4 bg-slate-200 p-2 rounded-lg">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-semibold text-slate-700">
                    {{ $mes->user->name ?? '(不明)' }}
                    @if ($mes->subject)
                        ／ {{ $mes->subject }}
                    @endif
                </span>
                <span class="text-xs text-gray-500 ml-2">{{ $mes->created_at }}</span>
            </div>
            <div class="bg-white rounded-md px-2 py-1">{!! nl2br($body) !!}</div>
        </div>
    </div>
@endif
