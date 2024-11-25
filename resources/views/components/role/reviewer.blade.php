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



</div>
