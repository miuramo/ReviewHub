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
            <x-element.h1>未完了のタスクがあります</x-element.h1>
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
        $myreviews = App\Models\Review::where('user_id', auth()->id())->where('status', 2)->get();
    @endphp
    <div class="px-6 py-4">
        <x-element.h1>最近担当した査読</x-element.h1>
        @foreach ($myreviews as $rev)
            <div class="mx-6 border-2 px-3 py-4 pb-3 bg-white">
                <x-element.paperid size=1 :paper_id="$rev->paper->id" />

                {{ $rev->paper->title }}

                <span class="mx-4"></span>
                <x-element.linkbutton href="{{ route('review.edit', ['review' => $rev]) }}" color="blue">
                    Edit
                </x-element.linkbutton>
                <span class="mx-2"></span>

                <x-element.linkbutton href="{{ route('review.show', ['review' => $rev]) }}" color="green">
                    View
                </x-element.linkbutton>

                <span class="mx-2"></span>
                @if($rev->target==1)
                    <x-element.linkbutton href="{{ route('review.show', ['review' => $rev->submit->rev1()]) }}" color="green">
                        View Rev1
                    </x-element.linkbutton>
                    <x-element.linkbutton href="{{ route('review.show', ['review' => $rev->submit->rev2()]) }}" color="green">
                        View Rev2
                    </x-element.linkbutton>
                @endif
            </div>
        @endforeach
    </div>
</div>
