@php
    $reviews = App\Models\Review::where('user_id', auth()->id())->get();

    $tasks = App\Models\Task::with('submit')
        ->where('subject_id', auth()->id())
        ->where('completed', 0)
        ->get();

    $approvetasks = App\Models\Task::with('submit')
        ->where('object_id', auth()->id())
        ->where('completed', 1)
        ->where('require_approve', 1)
        ->where('approved', 0)
        ->get();

    // 現在依頼済み
    $review_requests = App\Models\Review::with('paper')
        ->where('user_id', auth()->id())
        ->where('status', 0)
        ->whereHas('paper', function ($query) {
            // if paper is null, skip
            $query->whereNull('deleted_at');
        })
        ->whereNotNull('request_at')
        ->get();

@endphp

<!-- components.role.reviewer -->
<div class="px-6 py-4">

    @if (count($approvetasks) > 0)
        <div class="px-6 py-4">
            <x-element.h1>未完了の承認タスクがあります</x-element.h1>
            @foreach ($approvetasks as $task)
                <div class="mx-6">
                    <x-task.app_panel :task="$task" />
                </div>
            @endforeach
        </div>
    @endif
    @if (count($review_requests) > 0)
        <div class="px-6 py-4">
            <x-element.h1c color="pink"><b>現在、承諾依頼中の査読があります。下のボタンから連絡してください。</b></x-element.h1>
                @foreach ($review_requests as $revreq)
                    {{-- 安全装置： 現在はreview_requests のpaper_idは null でないはず --}}
                    @if (!$revreq->paper)
                        @continue
                    @endif
                    <div class="mx-6 border-2 px-3 py-4 pb-3 bg-white">
                        <x-element.paperid size=1 :paper_id="$revreq->paper->id" />
                        第{{ $revreq->submit->round }}回査読<br>

                        {{ $revreq->paper->title }}<br>

                        <x-element.linkbutton
                            href="{{ route('review.req_confirm', ['review' => $revreq, 'token' => $revreq->token_for_request()]) }}"
                            color="pink">
                            査読の承諾（または辞退）を連絡する
                        </x-element.linkbutton>

                    </div>
                @endforeach
        </div>
    @endif

    @if (count($tasks) > 0)
        <div class="px-6 py-4">
            <x-element.h1>以下の査読について、ご対応をお願いします。<br><span
                    class="text-pink-500 font-extrabold">（「査読報告の編集」が完了したあとに表示される「査読完了を報告する」ボタンを押してください。）</span></x-element.h1>
            @foreach ($tasks as $task)
                <div class="mx-6">
                    <x-task.panel :task="$task" />
                </div>
            @endforeach
        </div>
    @endif

    @push('localjs')
        <script src="/js/sortable.js"></script>
    @endpush


    <div class="py-2 px-6">
        <livewire:inf-list-cm />
    </div>

    <div class="py-2 px-6">
    <x-element.linkbutton href="{{ route('term.index') }}" color="cyan">
        編集委員名簿
    </x-element.linkbutton>
</div>
