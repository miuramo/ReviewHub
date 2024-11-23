@props([
    'task' => null,
])
<!-- components.paper.summarytable -->
@php

@endphp

<x-element.h1c color="yellow" dark=300 :options="['font-bold']">
    {{-- 論文 --}}
    <x-element.paperid :paper_id="$task->submit->paper->id" />
    <span class="mx-1"></span>
    {{-- 誰が --}}
    @php
        $role = App\Models\Role::findByIdOrName($task->workflow->subject);
    @endphp
    {{ $role->desc ?? '???' }} が
    {{-- 何を --}}
    {{ $task->workflow->description }}
    <span class="mx-2"></span>
    締切: {{ $task->due_date }}
    <span class="mx-2"></span>
    {{ $task->dueForHumans() }}
    <span class="mx-2"></span>
    @if($task->workflow->need_approve)
    <span class="bg-red-200 text-red-800 p-1 rounded">割当後、承認プロセスあり</span>
    @endif
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
        @if ($task->workflow->task == 'assign')
            <form action="{{ route('task.update', ['task' => $task]) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="task" value="{{ $task->id }}">
                <input type="hidden" name="redirect_role" value="{{ $task->workflow->subject }}">
                @php
                    $rolename = $task->workflow->object;
                    $rolename = str_replace('1', '', $rolename);
                    $rolename = str_replace('2', '', $rolename);
                    $rolename = str_replace('3', '', $rolename);
                    $objectRole = App\Models\Role::findByIdOrName($rolename);
                @endphp
                <label for="object_id" class="text-sm bg-slate-100 font-thin mr-2 p-0 h-5">
                    選択してください→
                </label>
                <select name="object_id" id="object_id" class="">
                    @foreach ($objectRole->users as $user)
                        <option value="{{ $user->id }}" class="text-sm bg-slate-100 font-thin mr-2 p-0 h-5">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @if($task->workflow->need_approve)
                <span class="mx-2"></span>
                <input type="checkbox" name="skip_approve" id="skip_approve" value="1">
                <label for="skip_approve" class="text-sm hover:bg-pink-200 p-1">承認プロセスをスキップする（内諾あり） 
                </label>
                @endif
                <span class="mx-2"></span>
                <x-element.submitbutton color="blue" value="assign"
                    confirm='このタスクを完了し、次のワークフローに移行すると、戻ることはできません。本当に進めてよいですか？'>割り当てる</x-element.submitbutton>
            </form>
        @elseif($task->workflow->task == 'confirm')

        @elseif($task->workflow->task == 'approve')

        @elseif($task->workflow->task == 'submit')
        @endif

    </div>
