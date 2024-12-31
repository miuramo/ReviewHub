@props([
    'review' => null,
])
@php
    $task = App\Models\Task::where('subject_id', $review->user->id)
        ->where('submit_id', $review->submit->id)
        ->where('workflow_id', 4)
        ->first();
@endphp
<!-- components.task.tswitch -->
{{-- <x-element.component_name>
    tswitch
</x-element.component_name> --}}
@isset($task)
    @if ($task->completed)
        査読完了
    @else
        査読タスク依頼中
    @endif
@else
<span class="text-red-500 font-extrabold">まだ開始していません</span>
    <x-element.linkbutton href="{{ route('task.create', ['review' => $review, 'revuid' => $review->user->id]) }}"
        color="blue">
        査読開始
    </x-element.linkbutton>
@endisset
