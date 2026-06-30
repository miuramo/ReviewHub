@props([
    'submit_id' => null,
])
@php
    $sub = App\Models\Submit::find($submit_id);
    $reviewers = App\Models\Role::findByIdOrName('cm')->users_desc;
@endphp
<x-element.component_name>
    rassign
</x-element.component_name>
<!-- components.review.assign  -->

<form action="{{ route('sub.review_assign', ['sub' => $sub]) }}" method="post" class="">
    @csrf
    @method('POST')

    <input type="hidden" name="submit_id" value="{{ $sub->id }}">
    <input type="hidden" name="redirect_page" value="{{ route('paper.manage',['paper'=>$sub->paper]) }}">

    <select name="reviewer_id" id="reviewer_id">
        @foreach ($reviewers as $reviewer)
            <option value="{{ $reviewer->id }}">{{ $reviewer->name }} （{{$reviewer->affil}}）</option>
        @endforeach
    </select>
    さんを
    <select name="target" id="target">
        <option value="1">通常査読</option>
        <option value="2">メタ査読</option>
        <option value="3">最終判定</option>
    </select>
    の
    <x-element.submitbutton color="blue" value="assign">候補者にする</x-element.submitbutton>
    <span class="text-sm text-gray-500">※「候補者にする」を押しても、候補に追加されるのみで、解除可能です。メール送信もしません。</span>
</form>

