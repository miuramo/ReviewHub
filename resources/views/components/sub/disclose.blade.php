@props([
    'sub' => null,
])
@php
    // $sub = App\Models\Submit::find($submit_id);
@endphp
<!-- components.sub.disclose  -->
<x-element.linkbutton2 :href="route('sub.disclose', ['sub' => $sub])" color="purple"
    confirm="この操作は元に戻せません。よろしいですか？">
    査読報告を著者に開示する
</x-element.linkbutton2>
<x-element.component_name>
    disclose
</x-element.component_name>
