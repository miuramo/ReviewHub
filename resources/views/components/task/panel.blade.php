@props([
    'task' => null,
])
<!-- components.paper.summarytable -->
@php

@endphp

<x-element.h1c color="yellow" dark=300 :options="['font-bold', 'mb-0']">
    {{-- 論文 --}}
    <x-element.paperid :paper_id="$task->submit->paper->id" />
    <span class="mx-1"></span>
    {{-- 誰が --}}
    @php
        $subRN = $task->workflow->subject;
        $subRN = str_replace('1', '', $subRN);
        $subRN = str_replace('2', '', $subRN);
        $subRN = str_replace('3', '', $subRN);
        $role = App\Models\Role::findByIdOrName($subRN);
    @endphp
    (Step {{ $task->workflow->id }})
    {{ $role->desc ?? '???' }} ({{ $task->subject->name }}) が、
    {{-- 何を --}}
    {{ $task->workflow->description }}
    <span class="mx-2"></span>
    締切: {{ $task->due_date }}
    <span class="mx-2"></span>
    {{ $task->dueForHumans() }}
    <span class="mx-2"></span>
    @if ($task->workflow->need_approve)
        <span class="bg-red-200 text-red-800 p-1 rounded">割当後、承諾プロセスあり</span>
    @endif
    </x-element.h1>
    <div class="mx-2 bg-yellow-100 p-3">

        <div class="mx-3">

            論文ファイル：
            @if ($task->submit->paper->pdf_file_id != 0)
                <a class="underline text-blue-600 hover:bg-lime-200"
                    href="{{ route('file.showhash', ['file' => $task->submit->paper->pdf_file_id, 'hash' => substr($task->submit->paper->pdf_file->key, 0, 8)]) }}"
                    target="_blank">
                    {{ $task->submit->paper->pdf_file->origname }}
                </a>
            @else
                No File
            @endif
        </div>

        {{-- もし、割り当てタスクなら --}}
        @if ($task->workflow->task == 'assign')
            <form action="{{ route('task.update', ['task' => $task]) }}" method="post" class="items-center flex">
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
                        @if ($user->id != auth()->id())
                            <option value="{{ $user->id }}" class="text-sm bg-slate-100 font-thin mr-2 p-0 h-5">
                                {{ $user->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
                @if ($task->workflow->need_approve)
                    <span class="mx-2"></span>
                    <input type="checkbox" name="skip_approve" id="skip_approve" value="1">
                    <label for="skip_approve" class="text-sm hover:bg-pink-200 p-1">内諾あり（承諾プロセスをスキップ）
                    </label>
                @endif
                <textarea class="text-sm m-0 p-1" name="comment" placeholder="特別なメッセージがあれば、ここに書く" cols=50 rows=2></textarea>
                <span class="mx-2"></span>
                <x-element.submitbutton color="blue" value="assign"
                    confirm='このタスクを完了し、次のワークフローに移行すると、戻ることはできません。本当に進めてよいですか？'>割り当てる</x-element.submitbutton>
            </form>
            <div class="m-2 p-2 bg-pink-100">
                リストにないユーザに依頼したい場合は、
                <form action="{{ route('role.adduser') }}" method="post" class="inline-block">
                    @csrf
                    @method('PUT')
                    <input type="text" name="user" class="text-sm" placeholder="氏 名" size=8>
                    <input type="text" name="affil" class="text-sm" placeholder="所属" size=8>
                    <input type="text" name="email" class="text-sm" placeholder="メールアドレス" size=20>
                    <input type="hidden" name="role" value="{{ $rolename }}">
                    <input type="hidden" name="redirect_role" value="{{ $task->workflow->subject }}">
                    を入力して
                    <x-element.submitbutton color="pink">
                        査読者の新規作成
                    </x-element.submitbutton>
                </form>
                を先に行ってください。
            </div>
        @elseif($task->workflow->task == 'confirm')

        @elseif($task->workflow->task == 'approve')

        @elseif($task->workflow->task == 'submit')
            @php
                $rev = App\Models\Review::where('paper_id', $task->submit->paper->id)
                    ->where('user_id', $task->subject->id)
                    ->first();
            @endphp
            @if ($rev->status == 2)
                <div class="bg-cyan-100 px-3 pt-4">
                @else
                    <div class="bg-yellow-50 px-3 pt-4">
            @endif
            <x-element.paperid size=1 :paper_id="$rev->paper->id">
            </x-element.paperid>
            <span class="mx-2"></span>

            @if ($rev->ismeta)
                <x-element.linkbutton2 href="{{ route('review.edit', ['review' => $rev]) }}" color="red">
                    Edit (メタ)
                </x-element.linkbutton2>
            @else
                <x-element.linkbutton href="{{ route('review.edit', ['review' => $rev]) }}" color="blue">
                    Edit
                </x-element.linkbutton>
            @endif
            <x-element.linkbutton href="{{ route('review.show', ['review' => $rev]) }}" color="green">
                View
            </x-element.linkbutton>
            <span class="mx-2"></span>

            {{-- <x-element.bblink :rev="$rev">
            </x-element.bblink>
            <span class="mx-2"></span> --}}

            @if ($rev->status == 2)
                <span class="inline-block border-2 border-blue-600 p-0.5 text-blue-600 font-bold text-sm">
                    査読完了
                </span>
            @endif

            @if ($rev->paper->pdf_file_id != null)
                <div class="w-1/2">
                    <a href="{{ route('review.edit', ['review' => $rev]) }}">
            @endif
            <x-file.paperheadimg :paper="$rev->paper">
            </x-file.paperheadimg>
            @if ($rev->paper->pdf_file_id != null)
                </a>
            @endif
    </div>

    {{-- <div class="text-sm mt-2 ml-2">
                <x-enquete.Rev_enqview :rev="$rev">
                </x-enquete.Rev_enqview>
            </div> --}}


    @endif

    </div>
