@props([
    'sub' => null,
    'label' => null,
    'color' => 'blue',
    'size' => 'md',
])

@php
    $label = ($label == null) ? $sub->paper->title : $label;
@endphp
<!-- components.review.commentpaper_link  -->
@if($color)
    <x-element.linkbutton target="_blank" href="{{ route('review.commentpaper', ['cat'=>$sub->category_id, 'paper' => $sub->paper, 'token' => $sub->token() ]) }}" color="{{ $color }}" size="{{ $size }}">
        {{ $label }}
    </x-element.linkbutton>
@else
<a class="hover:underline text-{{ $size}}" href="{{ route('review.commentpaper', ['cat'=>$sub->category_id, 'paper' => $sub->paper, 'token' => $sub->token() ]) }}" target="_blank">
    {{ $label }}
</a>
@endif
