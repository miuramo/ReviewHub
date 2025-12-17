<!-- components.enquete.answers -->
<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                &larr; アンケート一覧 に戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            「{{ $enq->name }}」 {{ __('アンケート回答') }}

        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="py-4 px-6  dark:text-gray-400">
        @if ($all)
            <x-element.linkbutton href="{{ route('enq.answers', ['enq' => $enq->id]) }}" color="blue" size="sm"
                class="m-4">
                採録済みのみ表示する
            </x-element.linkbutton>
        @else
            <x-element.linkbutton href="{{ route('enq.answers', ['enq' => $enq->id, 'all' => 1]) }}" color="lime"
                size="sm" class="m-4">
                すべて表示する
            </x-element.linkbutton>
        @endif


        @if ($enq->withpaper)
            <x-admin.enqtable :papers="$papers" :enqans="$enqans" :enq="$enq">
            </x-admin.enqtable>
        @else
            <x-admin.enqtable_nopaper :enqans="$enqans" :enq="$enq">
            </x-admin.enqtable_nopaper>
        @endif
    </div>

    <div class="py-2 px-6">


        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                &larr; アンケート一覧 に戻る
            </x-element.linkbutton>
        </div>
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
