@props([
    'review' => null,
])
@php
    // $review = App\Models\Review::find($review);
@endphp

<!-- components.review.status  -->
<table class="min-w divide-y divide-gray-200 inline-block align-top">
    <thead>
        <tr>
            <th class="p-1 bg-slate-300">{{$review->user->name}}（{{$review->user->affil}}）</th>
            <th class="p-1 bg-slate-300"></th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($review->heads() as $h => $hc)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td class="p-1 text-center">{{$h}} <br><span class="text-xs">{{ $hc }}</span></td>
                <td class="p-1 text-center">{{ $review->{$h} ?? '--' }}</td>
        @endforeach
        <tr class="bg-white">
            <td class="p-1 text-center">
                <x-element.deletebutton action="{{ route('review.destroy', ['review' => $review]) }}"
                    color="orange" confirm="本当に{{$review->user->name}}さんを査読者から外してよいですか？">
                    査読者から外す
                </x-element.deletebutton>
            </td>
            <td class="p-1 text-left"></td>
        </tr>
    </tbody>
</table>
