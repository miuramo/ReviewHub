@props([
    'submit_id' => null,
])
@php
    $sub = App\Models\Submit::find($submit_id);
@endphp
<x-element.component_name>
    rassign_again
</x-element.component_name>
<!-- components.review.assign  -->

<form action="{{ route('sub.review_assign_again', ['sub' => $sub]) }}" method="post" class="">
    @csrf
    @method('POST')

    <input type="hidden" name="submit_id" value="{{ $sub->id }}">
    <input type="hidden" name="redirect_page" value="{{ route('paper.manage',['paper'=>$sub->paper]) }}">

    <x-element.submitbutton color="cyan" value="assign">ひとつ前のラウンドと同一の査読者を割り当てる</x-element.submitbutton>
</form>

