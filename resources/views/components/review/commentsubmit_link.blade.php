@props([
    'sub' => null,
    'label' => null,
    'color' => 'blue',
])

@php
    $label = ($label == null) ? $sub->paper->title : $label;
@endphp
<!-- components.review.commentpaper_link  -->
@if($color)
    <x-element.linkbutton href="{{ route('review.commentsubmit', ['sub'=>$sub, 'token' => $sub->token() ]) }}" color="{{ $color }}">
        {{ $label }}
    </x-element.linkbutton>
@else
<a class="hover:underline" href="{{ route('review.commentsubmit', ['sub'=>$sub, 'token' => $sub->token() ]) }}" >
    {{ $label }}
</a>
@endif
