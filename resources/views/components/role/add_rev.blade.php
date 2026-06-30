@props([
    'submit_id' => null,
])
@php
    $sub = App\Models\Submit::find($submit_id);
@endphp
<x-element.component_name>
    add_rev
</x-element.component_name>

<!-- components.role.add_rev -->
<div class="m-0 p-2 bg-lime-100 text-sm text-gray-500">
    登録されていないユーザを登録して割り当てたい場合は、
    <form action="{{ route('role.adduser') }}" method="post" class="inline-block leading-relaxed">
        @csrf
        @method('PUT')
        <input type="text" name="user" class="text-sm" placeholder="氏_名（※氏と名の間にスペース）" size=30>
        <input type="text" name="affil" class="text-sm" placeholder="所属（※括弧は不要）" size=20>
        <input type="text" name="email" class="text-sm" placeholder="メールアドレス" size=40>
        <input type="hidden" name="role" value="rev">
        <input type="hidden" name="submit_id" value="{{ $sub->id }}">
        <input type="hidden" name="redirect_page" value="{{ route('paper.manage', ['paper' => $sub->paper]) }}">
        <select name="target" id="target" class="m-1 py-1 pl-2 pr-8">
            <option value="1">通常査読</option>
            <option value="2">メタ査読</option>
            <option value="3">最終判定</option>
        </select>

        を入力して
        <x-element.submitbutton color="lime">
            査読者の新規作成と候補者への追加
        </x-element.submitbutton>
        を押してください。
    </form>
</div>

<x-element.linkbutton2 href="{{ route('role.edit', ['role' => 'rev']) }}" color="green">
    査読者リストの管理
</x-element.linkbutton2>
