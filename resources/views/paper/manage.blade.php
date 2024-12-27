<x-app-layout>
    <!-- paper.index -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('投稿管理') }}
            <span class="mx-2"></span>
            <x-element.paperid size=2 :paper_id="$paper->id"></x-element.paperid>
        </h2>
    </x-slot>
    <style>
        .hidden-content {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
    </style>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <x-element.component_name>
        manage
    </x-element.component_name>

    <div class="py-2 px-6">
        <x-element.h1>
            現在の査読ラウンド
        </x-element.h1>
        <div class="block">
            <x-sub.substatus :submit_id="$paper->currentsubmit->id"></x-sub.substatus>
        </div>

        <x-element.button id="toggleButton" value="査読者の割り当て画面をひらく" color="blue" onclick="openclose('div_rassign')">
        </x-element.button>

        <div class="hidden-content p-2 bg-cyan-100" style="display:none" id="div_rassign">
            <div class="py-2 px-6">
                <x-review.rassign :submit_id="$paper->currentsubmit->id"></x-review.rassign>
            </div>
            <div class="py-2 px-6">
                <x-role.add_rev :submit_id="$paper->currentsubmit->id"></x-role.add_rev>
            </div>
        </div>

        <div class="block">
            @php
                $tasks = $paper->currentsubmit->tasks;
            @endphp
            @foreach($tasks as $task)
                <x-task.taskstatus :task="$task"></x-task.taskstatus>
            @endforeach
        </div>

    </div>


    <div class="py-2 px-6">
        <x-element.h1>投稿管理者：
            @foreach ($paper->managers as $user)
                <x-element.login_as :user="$user"></x-element.login_as>
                <span class="mx-2"></span>
            @endforeach
        </x-element.h1>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush
</x-app-layout>
