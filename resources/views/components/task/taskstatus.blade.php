@props([
    'task' => null,
])
@php
    $bgcolor = $task->approved ? 'cyan' : 'yellow';
@endphp
<!-- components.task.taskstatus -->
<x-element.component_name>
    taskstatus
</x-element.component_name>
<div class="bg-{{ $bgcolor }}-100 p-2 text-sm">
    {{-- 誰が --}}
    @php
        $role = App\Models\Role::findByIdOrName($task->workflow->subject);
    @endphp
    {{-- {{ $role->desc ?? '???' }} --}}
    <x-element.login_as :user="$task->subject" />
    が
    {{-- 何を --}}
    {{ $task->submit->paper->id_03d() }} の
    {{ $task->workflow->description }}

    {{-- もし、割り当てタスクなら --}}
    @if ($task->workflow->task == 'assign')
        → <x-element.login_as :user="$task->object" />
    @elseif($task->workflow->task == 'confirm')
        → <x-element.login_as :user="$task->object" />
    @elseif($task->workflow->task == 'approve')

    @elseif($task->workflow->task == 'submit')
        → <x-element.login_as :user="$task->object" />
    @endif
    {{-- 締切 --}}

    <span class="mx-2"></span>
    （報告完了日時: {{ $task->completed_at }}）
    {{-- <span class="mx-2"></span>

    （承認日時：{{ $task->approved_at }}）
    <span class="mx-2"></span>
    @foreach ($task->log as $log)
        <span class="bg-slate-200 p-2 text-xs">
            コメント:{{ $log['comment'] ?? '未設定' }} 日時:{{ $log['datetime'] }}
        </span>
    @endforeach

    <span class="mx-2"></span>
    TaskID: {{ $task->id }} --}}
</div>
