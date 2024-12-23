@props([
    'paper_id' => 1,
])
@php
    $paper = App\Models\Paper::find($paper_id);
    $reviewers = App\Models\Role::findByIdOrName('rev')->users;
@endphp

<!-- components.review.assign  -->
<select name="reviewer_id" id="reviewer_id">
    @foreach ($reviewers as $reviewer)
        <option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>
    @endforeach
</select>
