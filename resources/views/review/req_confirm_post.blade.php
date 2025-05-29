<x-app-layout>
    <!-- review.req_confirm -->
    @section('title', 'ご回答ありがとうございました')

    @php
        $paper = $review->submit->paper;
        $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('ご回答ありがとうございました') }} &nbsp;

            {{-- <x-element.paperid size=2 :paper_id="$paper->id"></x-element.paperid> --}}
        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="py-2 px-6">
        <div class="text-lg py-4">
            {!! $message !!}
        </div>
    </div>

    {{-- <div class="py-2 px-6">
        <x-paper.shoshi_list :paper="$paper">
        </x-paper.shoshi_list>
    </div> --}}

</x-app-layout>
