<x-app-layout>
    <!-- paper.index -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('査読状況') }}
            <span class="mx-2"></span>
            <x-element.paperid size=2 :paper_id="$paper->id"></x-element.paperid>

            <x-element.category :cat="$paper->category_id" size="lg"></x-element.category>

        </h2>
    </x-slot>
    <style>
        .hidden-content {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
    </style>
    @section('title', 'P' . $paper->id . ' ' . $paper->title)
    @php
        $name_of_managers = \App\Models\Setting::getValue('NAME_OF_MANAGERS');

        $can_manage = auth()->user()->can('manage_review', $paper->id);
    @endphp

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

        <div class="bg-gray-300 text-sm p-2 mx-2 dark:text-gray-300 dark:bg-gray-500">
            ファイル一覧：
            @foreach ($files as $file)
                <a class="underline text-blue-600 hover:bg-lime-200"
                    href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 10)]) }}"
                    target="_blank"> {{ $file->origname }} </a> {{ $file->created_at }}
                <span class="mx-4"></span>
            @endforeach
        </div>

        <div>
            @php
                // 回答可能(canedit)または参照可能(readonly)
                $enqs = \App\Models\Enquete::needForSubmit($paper);

                // 既存回答
                $eans = \App\Models\EnqueteAnswer::where('paper_id', $paper->id)->get();
                $enqans = [];
                foreach ($eans as $ea) {
                    $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
                }

            @endphp
            <div class="p-2 bg-lime-100 text-sm" id="div_enqans">
                @foreach ($enqs['canedit'] as $enq)
                    @can('see_enquete', $enq)
                        <x-enquete.view :enq="$enq" :enqans="$enqans" :inline="true">
                        </x-enquete.view>
                    @endcan
                @endforeach
                @foreach ($enqs['readonly'] as $enq)
                    @can('see_enquete', $enq)
                        <x-enquete.view :enq="$enq" :enqans="$enqans" :inline="true">
                        </x-enquete.view>
                    @endcan
                @endforeach
            </div>
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
            <x-sub.substatus :submit_id="$paper->currentsubmit->id" readonly="1"></x-sub.substatus>
        </div>

    </div>



    <div class="py-2 px-6">
        <x-element.h1>
            過去の査読ラウンド
        </x-element.h1>

        @foreach ($paper->submits_desc as $sub)
            @if ($sub->round == $paper->currentsubmit->round)
                @continue
            @endif
            @if ($sub->ec_decision_at != null)
                <div class="block">
                    <x-sub.substatus :submit_id="$sub->id" readonly="1"></x-sub.substatus>
                </div>
            @endif
        @endforeach
    </div>

    <div class="py-2 px-6">
        <x-element.h1c color="yellow">{{ $name_of_managers }}：
            @foreach ($paper->managers as $user)
                <x-element.login_as :user="$user"></x-element.login_as>
                <span class="mx-2"></span>
            @endforeach
        </x-element.h1c>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush
</x-app-layout>
