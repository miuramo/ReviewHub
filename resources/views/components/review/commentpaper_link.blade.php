@props([
    'sub' => null,
    'label' => null,
])

@php
    $label = ($label == null) ? $sub->paper->title : $label;
@endphp
<!-- components.review.commentpaper_link  -->
<a class="hover:underline" href="{{ route('review.commentpaper', ['cat'=>$sub->category_id, 'paper' => $sub->paper, 'token' => $sub->token() ]) }}" target="_blank">
    {{ $label }}
</a>
