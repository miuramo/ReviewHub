@props([
    'task' => null,
])
<!-- components.task.app_panel -->
@php

@endphp

<x-element.h1c color="cyan" dark=200 :options="['font-bold mb-0']">
    {{-- 論文 --}}
    <x-element.paperid :paper_id="$task->submit->paper->id" />
    <span class="mx-1"></span>
    {{-- 誰が --}}
    @php
        $role = App\Models\Role::findByIdOrName($task->workflow->subject);
    @endphp
    {{ $role->desc ?? '???' }} が あなたに
    {{-- 何を --}}
    {{ str_replace("割り当てる","割り当てました", $task->workflow->description) }}
    <span class="mx-2"></span>
    締切: {{ $task->due_date }}
    <span class="mx-2"></span>
    {{ $task->dueForHumans() }}
    </x-element.h1>
    <div class="mx-4 p-2 bg-cyan-100">
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
        @if ($task->workflow->task == 'assign')
            <form action="{{ route('task.approve', ['task' => $task]) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="task" value="{{ $task->id }}">
                <input type="hidden" name="redirect_role" value="{{ $task->workflow->object }}">
                <label for="object_id" class="text-sm bg-slate-100 font-thin mr-2 p-0 h-5">
                    選択してください→
                </label>
                <input type="radio" name="approve" id="approve" value="1">
                <label for="approve" class="text-sm bg-lime-200 hover:bg-lime-300 p-1">承認する</label>
                <span class="mx-2"></span>
                <input type="radio" name="approve" id="reject" value="0">
                <label for="reject" class="text-sm bg-pink-200 hover:bg-pink-300 p-1">承認しない</label>
                <span class="mx-2"></span>
                <input type="text" name="comment" id="comment" class="text-sm bg-slate-100 font-thin p-2" size="30"
                    placeholder="不承認の理由・コメント">
                <x-element.submitbutton color="blue" value="approve"
                    confirm='本当に送信してよいですか？'>送信する</x-element.submitbutton>
            </form>
        @elseif($task->workflow->task == 'confirm')

        @elseif($task->workflow->task == 'approve')

        @elseif($task->workflow->task == 'submit')
        @endif

    </div>
