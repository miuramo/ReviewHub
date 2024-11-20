<x-app-layout>
    <!-- sub.show -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            <x-element.paperid size=2 :paper_id="$sub->paper->id">
            </x-element.paperid>
            <span class="mx-2"></span>
            {{ __('投稿管理') }}
        </h2>
    </x-slot>
    @section('title', 'P'.$sub->paper->id." s".$sub->id." ".$sub->paper->title)

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="mx-6">
        <x-sub.summarytable :sub="$sub" />
    </div>

</x-app-layout>