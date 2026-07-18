@php
    $name_of_managers = \App\Models\Setting::getValue('NAME_OF_MANAGERS');

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
        <div class="py-4">
            <x-element.h1>未完了の承認タスクがあります</x-element.h1>
            @foreach ($approvetasks as $task)
                <div class="mx-6">
                    <x-task.app_panel :task="$task" />
                </div>
            @endforeach
        </div>
    @endif
    @if (count($review_requests) > 0)
        <div class="py-4">
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
        <div class="py-4">
            <x-element.h1>以下の査読について、ご対応をお願いします。<br><span
                    class="text-pink-500 font-extrabold">（「査読報告の編集」が完了したあとに表示される「査読完了を報告する」ボタンを押してください。）</span></x-element.h1>
            @foreach ($tasks as $task)
                <x-task.panel :task="$task" />
            @endforeach
        </div>
    @endif

    @php
        $recentapproved = App\Models\Task::with('submit')
            ->where('subject_id', auth()->id())
            ->where('completed', 1)
            ->where('approved', 1)
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();
    @endphp
    {{-- @if (count($recentapproved) > 0)
        <div class="px-6 py-4">
            <x-element.h1>最近完了した査読タスク</x-element.h1>
            @foreach ($recentapproved as $task)
                <div class="mx-6">
                    <x-task.revfinishpanel :task="$task" />
                </div>
            @endforeach
        </div>
    @endif --}}


    @php
        $myreviews = App\Models\Review::with('paper', 'submit')
            ->where('user_id', auth()->id())
            ->whereNotNull('end_at')
            ->orderBy('created_at', 'desc')
            ->get();
    @endphp
    <div class="px-6 py-4">
        <x-element.h1>最近担当した査読</x-element.h1>
        @foreach ($myreviews as $rev)
            <div class="mx-6 border-2 px-3 py-4 pb-3 bg-white">
                <x-element.paperid size=1 :paper_id="$rev->paper->id" />
                <x-element.category :category="$rev->paper->category" size="xs" />
                第{{ $rev->submit->round }}回査読<br>

                {{ $rev->paper->title }}<br>

                <div class="bg-gray-200 text-sm p-2 mx-2 dark:text-gray-300 dark:bg-gray-500">
                    論文ファイル：
                    @foreach ($rev->paper->past_pdf_files() as $file)
                        {{-- @foreach ($rev->paper->files as $file) --}}
                        <a class="underline text-blue-600 hover:bg-lime-200 dark:text-blue-200 dark:hover:bg-lime-500"
                            href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 10)]) }}"
                            target="_blank"> {{ $file->origname }} </a>
                        <span class="mx-4"></span>
                    @endforeach
                </div>

                {{-- <span class="mx-2"></span> --}}

                <x-element.linkbutton href="{{ route('review.show', ['review' => $rev, 'token' => $rev->token()]) }}" color="green">
                    査読報告の参照
                </x-element.linkbutton>
                <span class="mx-4"></span>
                @if (!$rev->submit->ec_decision_at)
                    @if ($rev->locked)
                        <span class="text-sm text-gray-500 ">{{ substr($rev->submit->ec_decision_at, 0, 10) }}
                            修正ロック中</span>
                    @else
                        <x-element.linkbutton href="{{ route('review.edit', ['review' => $rev]) }}" color="blue">
                            査読報告の修正
                        </x-element.linkbutton>
                    @endif
                @else
                    <span
                        class="text-sm text-gray-300 hover:text-gray-500">{{ substr($rev->submit->ec_decision_at, 0, 10) }}
                        通知済み（査読報告の修正はできません）</span>

                    <x-element.linkbutton2 href="{{ $rev->submit->url_reviewresult_for_author() }}" color="purple"
                        target="_blank" size="sm">
                        著者に通知した査読結果 </x-element.linkbutton2>
                @endif
                <span class="mx-4"></span>
                <x-bb.bb_link :submit="$rev->submit" type="2" :rev_id="$rev->id" size="sm"
                    label="{{ $name_of_managers }}との掲示板">
                </x-bb.bb_link>
            </div>
        @endforeach
    </div>
</div>
