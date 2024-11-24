@props([
    'task' => null,
])
<!-- components.paper.summarytable -->

<div class="bg-cyan-100 p-2 text-sm">
    {{-- 誰が --}}
    @php
        $role = App\Models\Role::findByIdOrName($task->workflow->subject);
    @endphp
    {{ $role->desc ?? '???' }}
    <x-element.login_as :user="$task->subject" />
    が
    {{-- 何を --}}
    {{ $task->submit->paper->id_03d() }} の
    {{ $task->workflow->description }}

    {{-- もし、割り当てタスクなら --}}
    @if ($task->workflow->task == 'assign')
        →→
        <x-element.login_as :user="$task->object" />
    @elseif($task->workflow->task == 'confirm')

    @elseif($task->workflow->task == 'approve')

    @elseif($task->workflow->task == 'submit')
    @endif

    <span class="mx-2"></span>
    （依頼日時: {{ $task->completed_at }}）
    <span class="mx-2"></span>

    （承認日時：{{ $task->approved_at }}）
    <span class="mx-2"></span>
    {{ $task->log }}

</div>
