@props([
    'submit_id' => null,
])
@php
    $sub = App\Models\Submit::find($submit_id);
    $reviewers = App\Models\Role::findByIdOrName('rev')->users;
@endphp

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
    <x-element.submitbutton color="blue" value="assign"
        confirm='本当に割り当ててよいですか？'>割り当てる</x-element.submitbutton>
</form>

