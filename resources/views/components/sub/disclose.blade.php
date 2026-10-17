@props([
    'sub' => null,
])
@php
    // $sub = App\Models\Submit::find($submit_id);
    // メモ：sub.disclose → コントローラーを経て、submit.setDecision を呼ぶ。
    $resubmit_duration_days = \App\Models\Setting::getary('RESUBMIT_DURATION_DAYS')[$sub->paper->category_id] ?? 30; //

    $thirtyDaysLater = now()->addDays($resubmit_duration_days);
@endphp
<!-- components.sub.disclose  -->
@if ($sub->ec_decision_at == null)
    <x-element.linkbutton2 :href="route('sub.disclose', ['sub' => $sub])" color="red"
        confirm="【注1】OKを押しても、著者に開示を伝えるメールは送信しません。お手数ですが、下の「査読結果開示通知を送る」で、連絡してください。【注2】本日、開示した場合の再投稿期限（{{ $resubmit_duration_days }}日後）は {{ $thirtyDaysLater->format('Y年m月d日') }} になります。最終判定に再投稿期限日を記載している場合は今一度ご確認ください。">
        査読結果を著者に開示する
    </x-element.linkbutton2>
@else
    <div class="text-red-400 p-2">査読結果は著者に開示済み</div>
@endif
<x-element.component_name>
    disclose
</x-element.component_name>
