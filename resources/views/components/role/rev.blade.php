@php
    $reviews = App\Models\Review::where('user_id', auth()->id())->get();

    $tasks = App\Models\Task::with('submit')->where('subject_id', auth()->id())->where('completed', 0)->get();

    $approvetasks = App\Models\Task::with('submit')
        ->where('object_id', auth()->id())
        ->where('completed', 1)
        ->where('require_approve', 1)
        ->where('approved', 0)
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

    @if (count($tasks) > 0)
        <div class="px-6 py-4">
            <x-element.h1>以下の査読について、ご対応をお願いします。<br>（「査読報告の編集」が完了したあとに表示される「査読完了を報告する」ボタンを押してください。）</x-element.h1>
            @foreach ($tasks as $task)
                <div class="mx-6">
                    <x-task.panel :task="$task" />
                </div>
            @endforeach
        </div>
    @endif

    @php
        $recentapproved = App\Models\Task::with('submit')
            ->where('subject_id', auth()->id())
            ->where('completed', 1)
            ->where('approved', 1)
            ->orderBy('updated_at', 'desc')
            ->get();
    @endphp
    @if (count($recentapproved) > 0)
        <div class="px-6 py-4">
            <x-element.h1>最近完了した査読タスク</x-element.h1>
            @foreach ($recentapproved as $task)
                <div class="mx-6">
                    <x-task.revfinishpanel :task="$task" />
                </div>
            @endforeach
        </div>
    @endif


    @php
        $myreviews = App\Models\Review::where('user_id', auth()->id())
            ->whereNotNull('end_at')
            ->orderBy('created_at', 'desc')
            ->get();
    @endphp
    <div class="px-6 py-4">
        <x-element.h1>最近担当した査読</x-element.h1>
        @foreach ($myreviews as $rev)
            <div class="mx-6 border-2 px-3 py-4 pb-3 bg-white">
                <x-element.paperid size=1 :paper_id="$rev->paper->id" />
                第{{ $rev->submit->round }}回査読<br>

                {{ $rev->paper->title }}<br>

                {{-- <span class="mx-2"></span> --}}

                <x-element.linkbutton href="{{ route('review.show', ['review' => $rev]) }}" color="green">
                    査読・報告の参照
                </x-element.linkbutton>
                <span class="mx-4"></span>
                <x-element.linkbutton href="{{ route('review.edit', ['review' => $rev]) }}" color="blue">
                    査読・報告の修正
                </x-element.linkbutton>
                <span class="mx-4"></span>
                <x-bb.bb_link :submit="$rev->submit" type="2" :rev_id="$rev->id" size="sm" label="投稿管理者との掲示板">
                </x-bb.bb_link>
            </div>
        @endforeach
    </div>
</div>
