@props([
    'sub' => null,
])
@php
    // $sub = App\Models\Submit::find($submit_id);
    // メモ：sub.disclose → コントローラーを経て、submit.setDecision を呼ぶ。
@endphp
<!-- components.sub.disclose  -->
@if($sub->ec_decision_at == null)
<x-element.linkbutton2 :href="route('sub.disclose', ['sub' => $sub])" color="purple"
    confirm="OKを押しても、著者に開示を伝えるメールは送信しません。お手数ですが、下の「査読結果開示通知を送る」で、連絡してください。）">
    査読結果を著者に開示する
</x-element.linkbutton2>
@else
<div class="text-pink-300 p-2">査読結果は著者に開示済み（判定通知日時）</div>
@endif
<x-element.component_name>
    disclose
</x-element.component_name>
