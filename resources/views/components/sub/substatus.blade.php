@props([
    'submit_id' => null,
    'readonly' => false,
])
@php
    $sub = App\Models\Submit::find($submit_id);
    $accepts = App\Models\Accept::pluck('name', 'id')->toArray();
@endphp
<!-- components.sub.status  親は -->
<div class="bg-pink-100 rounded-lg p-2 inline-block align-top">
    <p class="text-center">査読状況
        <x-element.component_name type="span">
            substatus
        </x-element.component_name>
    </p>
    <table class="min-w divide-y divide-gray-200 inline-block">
        <thead>
            <tr>
                <th class="p-1 bg-slate-300">ラウンド</th>
                <th class="p-1 bg-slate-300">{{ $sub->round }} &nbsp; <sub>(subid={{ $sub->id }})</sub></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($sub->heads() as $h => $hc)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                    @if ($h == 'accept_id')
                        <td class="p-1 text-center">判定</td>
                        <td class="p-1 text-center">{{ $accepts[$sub->accept_id] }}</td>
                    @else
                        <td class="p-1 text-center">{{ $hc }}</td>
                        <td class="p-1 text-center">{{ $sub->{$h} ?? '--' }}</td>
                    @endif
                </tr>
            @endforeach
            <tr class="bg-slate-200">
                <td colspan=2 class="p-1 text-center">
                    <x-review.commentsubmit_link :sub="$sub" color="purple"
                        label="査読報告をみる"></x-element.commentsubmit_link>
                        <br>
                        @if (!$readonly)
                            <x-sub.disclose :sub="$sub"></x-sub.disclose>
                        @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

@if (count($sub->reviews) > 0)
    @foreach ($sub->reviews as $review)
        <x-review.rstatus :review="$review" :readonly="$readonly"></x-review.rstatus>
    @endforeach
@else
    <div class="m-6 p-4 bg-yellow-200 inline-block align-top">
        <p class="text-center">査読者はまだ登録されていません。</p>
        @if ($sub->round > 1)
            <x-review.rassign_again :submit_id="$sub->id"></x-review.rassign_again>
        @endif
    </div>
@endif
