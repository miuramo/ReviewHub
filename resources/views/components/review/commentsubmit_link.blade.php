@props([
    'sub' => null,
    'label' => null,
    'color' => 'blue',
    'target' => '_blank',
])

@php
    $label = ($label == null) ? $sub->paper->title : $label;
@endphp
<!-- components.review.commentpaper_link  -->
@if($color)
    <x-element.linkbutton href="{{ route('review.commentsubmit', ['sub'=>$sub, 'token' => $sub->token() ]) }}" color="{{ $color }}" target="{{ $target }}">
        {{ $label }}
    </x-element.linkbutton>
@else
<a class="hover:underline" href="{{ route('review.commentsubmit', ['sub'=>$sub, 'token' => $sub->token() ]) }}" target="{{ $target }}">
    {{ $label }}
</a>
@endif
