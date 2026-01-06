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
    @section('title', 'P' . $paper->id . ' ' . $paper->title)

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    <div class="mx-6 my-2 dark:text-gray-300">
        <x-paper.shoshi_list :paper="$paper">
        </x-paper.shoshi_list>
        投稿者：<x-element.login_as :user="$paper->paperowner"></x-element.login_as>

        <span class="mx-2"></span>
        @if ($paper->pdf_file_id != 0)
            <a class="underline text-blue-600 hover:bg-lime-200"
                href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 10)]) }}"
                target="_blank">
                論文PDF ({{ $paper->pdf_file->pagenum }}page)
            </a>
            ({{ $paper->pdf_file->created_at }})
        @else
            No PDF
        @endif
        <span class="mx-2"></span>
        {{-- 著者との掲示板 --}}
        <x-bb.bb_link :submit="$paper->currentsubmit" type="1"></x-bb.bb_link>
        <span class="mx-6"></span>
        <x-element.linkbutton href="{{ route('paper.sendsubmitted', ['paper' => $paper->id]) }}"
            confirm="投稿状況メールを代理送信します。よろしいですか？" color="cyan" size="xs" target="_self">
            投稿状況メールを代理送信
        </x-element.linkbutton>
        {{-- ロック --}}
        <livewire:paper-lock :paper="$paper" />

        <div class="bg-gray-300 text-sm p-2 mx-2 dark:text-gray-300 dark:bg-gray-500">
            ファイル一覧：
            @foreach ($files as $file)
                <a class="underline text-blue-600 hover:bg-lime-200"
                    href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 10)]) }}"
                    target="_blank"> {{ $file->origname }} </a> {{ $file->created_at }}
                <livewire:file-lock :file="$file" />
                <span class="mx-4"></span>
            @endforeach
        </div>

    </div>


    <div class="py-2 px-6">
        <x-element.h1>
            現在の査読ラウンド
            <x-element.component_name type="span">
                manage
            </x-element.component_name>
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
                <livewire:review-assign :submit_id="$paper->currentsubmit->id" :paper_id="$paper->id"></livewire:review-assign>
            </div>
            <div class="py-2 px-6">
                <x-role.add_rev :submit_id="$paper->currentsubmit->id"></x-role.add_rev>
            </div>
        </div>
    </div>
    <div class="py-2 px-6">
        <div class="block">
            @php
                $tasks = $paper->currentsubmit->tasks;
            @endphp
            @foreach ($tasks as $task)
                <x-task.taskstatus :task="$task"></x-task.taskstatus>
            @endforeach
        </div>

    </div>


    <div class="py-2 px-6">
        <x-element.h1>投稿管理者：
            @foreach ($paper->managers as $user)
                <x-element.login_as :user="$user"></x-element.login_as>
                @if ($user->id == Auth::user()->id)
                    <x-role.remove_manager :submit_id="$paper->currentsubmit->id" :user_id="$user->id"></x-role.remove_manager>
                @endif
                <span class="mx-2"></span>
            @endforeach
            <x-bb.bb_link :submit="$paper->currentsubmit" type="4"></x-bb.bb_link>
            <span class="mx-2"></span>
            <x-bb.bb_link :submit="$paper->currentsubmit" type="3"></x-bb.bb_link>
        </x-element.h1>
    </div>

    <div class="py-2 px-6">
        <x-element.h1>
            過去の査読ラウンド
        </x-element.h1>

        @foreach ($paper->submits_desc as $sub)
            @if ($sub->ec_decision_at != null)
                <div class="block">
                    <x-sub.substatus :submit_id="$sub->id" readonly="1"></x-sub.substatus>
                </div>
            @endif
        @endforeach
    </div>


    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush
</x-app-layout>
