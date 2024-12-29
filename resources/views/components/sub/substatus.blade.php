@props([
    'submit_id' => null,
])
@php
    $sub = App\Models\Submit::find($submit_id);
@endphp
<!-- components.sub.status  親は -->
<x-element.component_name>
    substatus
</x-element.component_name>
<table class="min-w divide-y divide-gray-200 inline-block">
    <thead>
        <tr>
            <th class="p-1 bg-slate-300">ラウンド</th>
            <th class="p-1 bg-slate-300">{{ $sub->round }}</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($sub->heads() as $h => $hc)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td class="p-1 text-center">{{ $hc }}</td>
                <td class="p-1 text-center">{{ $sub->{$h} ?? '--' }}</td>
        @endforeach
        <tr>
            <td colspan=2 class="p-1 text-center">
                <x-review.commentpaper_link :sub="$sub" color="purple" label="査読報告をみる"></x-element.commentpaper_link>
            </td>
        </tr>
    </tbody>
</table>

@if (count($sub->reviews) > 0)
    @foreach ($sub->reviews as $review)
        <x-review.rstatus :review="$review"></x-review.rstatus>
    @endforeach
@else
    <div class="m-6 p-4 bg-yellow-200 inline-block align-top">
        <p class="text-center">査読者はまだ登録されていません。</p>
    </div>
@endif
