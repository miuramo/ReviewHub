<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('採択分の著者掲示板') }}
        </h2>
    </x-slot>
    <style>
        .hidden-content {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
    </style>
    <!-- paper.show -->

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $bbs = \App\Models\Bb::recent_bb_accepted();
        $acceptbbs = \App\Models\Bb::bb_accepted();
    @endphp

    <div class="px-6 py-4">
        <x-element.h1>
            最近一週間の著者掲示板一覧
        </x-element.h1>
        <div class="py-2 px-6">

            @foreach ($bbs as $bb)
                <div>
                    @isset($bb->paper)
                        <x-element.linkbutton href="{{ route('bb.show', ['bb' => $bb->id, 'key' => $bb->key]) }}"
                            :color="'teal'" target="_blank" size="sm">
                            {{ $bb->paper->id_03d() }} {{ $bb->paper->title }}
                            ({{ $bb->nummessages() }} messages)
                        </x-element.linkbutton>
                    @else
                        <div>Error: No Paper associated {{ $bb->id }}</div>
                    @endisset
                </div>
            @endforeach
        </div>

        <x-element.h1>
            採択された著者掲示板一覧
        </x-element.h1>
        <div class="py-2 px-6">

            @foreach ($acceptbbs as $bb)
                <div>
                    @isset($bb->paper)
                        <x-element.linkbutton href="{{ route('bb.show', ['bb' => $bb->id, 'key' => $bb->key]) }}"
                            :color="'teal'" target="_blank" size="sm">
                            {{ $bb->paper->id_03d() }} {{ $bb->paper->title }}
                            ({{ $bb->nummessages() }} messages)
                        </x-element.linkbutton>
                    @else
                        <div>Error: No Paper associated {{ $bb->id }}</div>
                    @endisset
                </div>
            @endforeach
        </div>
        <div class="m-6">
            <x-element.linkbutton href="{{ route('bb.multisubmit', ['type' => 3]) }}" color="teal" size="md">
                出版掲示板への一括書き込み
            </x-element.linkbutton>
        </div>

    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
