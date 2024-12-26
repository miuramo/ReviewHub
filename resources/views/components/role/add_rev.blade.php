@props([
    'submit_id' => null,
])
@php
    $sub = App\Models\Submit::find($submit_id);
@endphp
<!-- components.role.add_rev -->
<div class="m-0 p-2 bg-pink-100 text-sm text-gray-500">
    リストにないユーザを割り当てたい場合は、
    <form action="{{ route('role.adduser') }}" method="post" class="inline-block leading-relaxed">
        @csrf
        @method('PUT')
        <input type="text" name="user" class="text-sm" placeholder="氏 名" size=8>
        <input type="text" name="affil" class="text-sm" placeholder="所属" size=8>
        <input type="text" name="email" class="text-sm" placeholder="メールアドレス" size=20>
        <input type="hidden" name="role" value="rev">
        <input type="hidden" name="redirect_page" value="{{ route('paper.manage',['paper'=>$sub->paper]) }}">
        を入力して
        <x-element.submitbutton color="pink">
            査読者の新規作成
        </x-element.submitbutton>
        を先に行ってください。
    </form>
</div>

<x-element.linkbutton2 href="{{ route('role.edit', ['role'=>'rev']) }}" color="pink">
    査読者リストの管理
</x-element.linkbutton2>
