@props([
    // 'paper' => null,
    'submit' => null,
    'type' => 4,
    'rev_id' => 0,
    'label' => null,
    'size' => 'md',
    'target' => '_blank',
])
<!-- components.bb.bb_link  -->
@php
    $name_of_managers = \App\Models\Setting::getValue("NAME_OF_MANAGERS");
    $bb = App\Models\Bb::where('paper_id', $submit->paper->id)->where('type', $type)->where('rev_id', $rev_id)->first();
    if ($bb){
        $bburl = $bb->url();
    } else {
        $bburl = App\Models\Bb::gen_make_url($submit->id, $type, $rev_id);
    }
    if (!$label){
        $ary = [1=>"著者との", 2=>"査読者との", 3=>"全査読者との", 4=>"{$name_of_managers}同士の"];
        $label = $ary[$type] . "掲示板";
    }
@endphp
{{-- <x-element.component_name type="span">
    bb_link
</x-element.component_name> --}}

<x-element.linkbutton2 href="{{ $bburl }}" color="green" size="{{ $size }}" target="{{ $target }}">
    {{$label}} 
</x-element.linkbutton2>
