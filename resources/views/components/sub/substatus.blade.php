@props([
    'submit_id' => null,
    'readonly' => false,
])
@php
    $sub = App\Models\Submit::find($submit_id);
    $accepts = App\Models\Accept::pluck('name', 'id')->toArray();
    $statuses = App\Models\Status::pluck('name', 'id')->toArray();
@endphp
<!-- components.sub.status  親は -->
<div class="bg-pink-100 rounded-lg p-2 inline-block align-top dark:bg-pink-600">
    <p class="text-center">査読状況
        <x-element.component_name type="span">
            substatus
        </x-element.component_name>
    </p>
    <table class="min-w divide-y divide-gray-200 inline-block">
        <thead>
            <tr>
                <th class="p-1 bg-slate-300">ラウンド {{ $sub->round }}

                </th>
                <th class="p-1 bg-slate-300">
                    {{ $sub->paper->currentstatus->name }}
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($sub->heads() as $h => $hc)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white dark:bg-slate-400' }}">
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
                    {{-- 受領メールを送る --}}
                    <x-element.linkbutton href="{{ route('manage.sendreceipt', ['sub' => $sub->id]) }}" 
                        color="pink" size="sm">受領メールを送る
                    </x-element.linkbutton> <span class="mx-2"></span>

                    <x-review.commentsubmit_link :sub="$sub" color="purple"
                        label="査読報告をみる"></x-element.commentsubmit_link>
                        <br>
                        @if (!$readonly && $sub->accept_id != 5)
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

{{-- 削除済み（辞退） --}}
@if (count($sub->rejected_reviews()) > 0)
    <div class="m-2 p-2 bg-gray-200 inline-block align-top dark:bg-gray-500">
        <p class="text-center">担当外</p>
        @foreach ($sub->rejected_reviews() as $review)
            <x-element.login_as :user="$review->user"></x-element.login_as>
            （{{ $review->user->affil }}）
            @if ($review->request_at)
                <span class="text-blue-500">依頼済み</span>
            @endif
            @if ($review->status == -1)
                <span class="text-red-500">辞退</span>
            @endif
            <x-element.linkbutton href="{{ route('review.restore', ['review' => $review->id]) }}" color="teal"
                size="xs">
                復活
            </x-element.linkbutton>
            <br>
        @endforeach
    </div>
@endif
