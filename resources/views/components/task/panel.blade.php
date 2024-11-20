@props([
    'task' => null,
])
<!-- components.paper.summarytable -->
@php

@endphp

<x-element.h1c color="yellow" dark=300 :options="['font-bold']">
    {{-- 誰が --}}
    @php
    $role = App\Models\Role::findByIdOrName($task->workflow->subject);
    @endphp
    {{ $role->desc ?? '???'}} が
    {{-- 何を --}}
    {{ $task->workflow->description }}
    <span class="mx-2"></span>
    締切: {{ $task->due_date }}
    <span class="mx-2"></span>
    {{ $task->dueForHumans() }} 
</x-element.h1>
<div class="mx-4">
ファイル：
@if ($task->submit->paper->pdf_file_id != 0)
    <a class="underline text-blue-600 hover:bg-lime-200"
        href="{{ route('file.showhash', ['file' => $task->submit->paper->pdf_file_id, 'hash' => substr($task->submit->paper->pdf_file->key, 0, 8)]) }}"
        target="_blank">
        {{ $task->submit->paper->pdf_file->origname }}
    </a>
@else
    No File
@endif

{{-- もし、割り当てタスクなら --}}
@if ($task->workflow->task == "assign")
<form action="{{ route('task.update', ['task' => $task]) }}" method="post">
    @csrf
    @method('PUT')
    <input type="hidden" name="task" value="{{ $task->id }}">
    @php
        $objectRole = App\Models\Role::findByIdOrName($task->workflow->object);
    @endphp
    <label for="task" class="text-sm bg-slate-100 font-thin mr-2 p-0 h-5">
        選択してください→
    </label>
    <select name="task" id="task" class="">
        @foreach($objectRole->users as $user)
            <option value="{{ $user->id }}" class="text-sm bg-slate-100 font-thin mr-2 p-0 h-5">
                {{ $user->name }}
            </option>
        @endforeach
    </select>
    
    <x-element.submitbutton color="blue" confirm='このタスクを完了し、次のワークフローに移行すると、戻ることはできません。本当に進めてよいですか？'>割り当てる</x-element.submitbutton>
</form>
@elseif( $task->workflow->task == "confirm")

@elseif( $task->workflow->task == "approve")

@elseif( $task->workflow->task == "submit")

@endif

</div>