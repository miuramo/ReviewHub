<x-app-layout>
    <!-- paper.index -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('投稿管理') }}
            <span class="mx-2"></span>
            <x-element.paperid size=2 :paper_id="$paper->id"></x-element.paperid>
        </h2>
    </x-slot>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif



    <div class="py-2 px-6">
        <x-element.h1>
            現在の査読ラウンド
        </x-element.h1>
        <div class="block">
            <x-sub.status :submit_id="$paper->currentsubmit->id"></x-sub.status>
        </div>
        <div class="p-2 bg-cyan-100">
            <div class="py-2 px-6">
                査読者の割り当て：
                <x-review.assign :submit_id="$paper->currentsubmit->id"></x-review.assign>
            </div>
            <div class="py-2 px-6">
                <x-role.add_rev :submit_id="$paper->currentsubmit->id"></x-role.add_rev>
            </div>
        </div>

    </div>


    <div class="py-2 px-6">
        <x-element.h1>投稿管理者：
            @foreach ($paper->managers as $user)
                {{ $user->name }}
                <span class="mx-2"></span>
            @endforeach
        </x-element.h1>
    </div>


</x-app-layout>
