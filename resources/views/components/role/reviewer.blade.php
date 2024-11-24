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


    {{-- <div class="px-6 py-2 pb-6">
        @foreach ($reviews as $rev)
            <div class="mx-4">
                {{ $rev->paper->title }}
                {{ $rev->paper->id }}
                {{ $rev->accept_id }}
                {{ $rev->ismeta }}
                <x-element.linkbutton href="{{ route('review.edit', ['review' => $rev]) }}" color="lime">
                    査読
                </x-element.linkbutton>

            </div>
        @endforeach
    </div> --}}

    {{-- <x-element.h1>
        過去の担当査読
    </x-element.h1>
    <div class="mx-6 my-4">
        <x-element.linkbutton href="{{ route('review.index') }}" color="lime">
            査読を担当していただく投稿の一覧
        </x-element.linkbutton>
    </div> --}}

</div>
