@props([
    'task' => null,
])
@php
    $bgcolor = $task->approved ? 'cyan' : 'yellow';
    $revrole = ['rev1' => '査読者1', 'rev2' => '査読者2', 'rev3' => '査読者3', 'meta' => 'メタ査読者', 'ec'=>'編集長', 'aec'=>'幹事'];
@endphp
<!-- components.paper.summarytable -->

<div class="bg-{{ $bgcolor }}-100 p-2 text-sm">
    {{-- 誰が --}}
    {{ $revrole[$task->workflow->subject] }}
    <x-element.login_as :user="$task->subject" />
    が
    {{-- 何を --}}
    {{ $task->submit->paper->id_03d() }} の
    {{ $task->workflow->description }}

    {{-- もし、割り当てタスクなら --}}
    @if ($task->workflow->task == 'assign')
        →<x-element.login_as :user="$task->object" />
    @elseif($task->workflow->task == 'confirm')
        →<x-element.login_as :user="$task->object" />
    @elseif($task->workflow->task == 'approve')

    @elseif($task->workflow->task == 'submit')
        →<x-element.login_as :user="$task->object" />
    @endif

    <span class="mx-2"></span>
    （報告完了日時: {{ $task->completed_at }}）
    <span class="mx-2"></span>

    {{-- （承認日時：{{ $task->approved_at }}）
    <span class="mx-2"></span> --}}
    {{-- @foreach ($task->log as $log)
        <span class="bg-slate-200 p-2 text-xs">
            コメント:{{ $log['comment'] ?? '未設定' }} 日時:{{ $log['datetime'] }}
        </span>
    @endforeach --}}

    {{-- <span class="mx-2"></span>
    TaskID: {{ $task->id }} --}}
</div>
