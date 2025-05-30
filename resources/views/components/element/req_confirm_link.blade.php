@props([
    'rev' => null,
    'label' => '(参考)承諾確認ページ',
])
@php
    $url = route('review.req_confirm', ['review'=>$rev, 'token'=>$rev->token_for_request()]);
@endphp
<!-- components.element.bblink -->
@isset($url)
    <x-element.linkbutton2 href="{{ $url }}" color="cyan" size="sm">
        {{$label}}
    </x-element.linkbutton2>
@else
    <div class="m-2 p-2 bg-pink-200 text-sm">エラー(revid)</div>
@endisset
