@props([
    'task' => null,
])
<!-- components.task.panel -->
@php
    $sub = App\Models\Submit::find($task->submit_id);
    $paper = App\Models\Paper::find($sub->paper_id);

    if($task->submit->round>=2){
        $answerfile = $paper->answer_file();
    }
@endphp
<x-element.component_name>panel</x-element.component_name>

<x-element.h1c color="yellow" dark=300 :options="['font-bold', 'mb-0']">
    {{-- 論文 --}}
    <x-element.paperid size=1 :paper_id="$paper->id" />
    <span class="mx-1"></span>
    {{-- 誰が --}}
    {{ $task->subject->name }} 様が、
    {{-- 何を --}}
    {{ $task->workflow->description }}
    &nbsp; 
    (第{{ $task->submit->round }}回目の査読) 
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
                    confirm='このタスクを完了し、次のワークフローに移行すると、戻ることはできません。本当に進めてよいですか？'>依頼する（割り当てる）</x-element.submitbutton>
            </form>
            <div class="m-2 p-2 bg-pink-100 text-sm text-gray-500">
                リストにないユーザを割り当てたい場合は、
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
            @php
                $rev = App\Models\Review::where('paper_id', $task->submit->paper->id)
                    ->where('user_id', $task->object->id)
                    ->first();
            @endphp
            <x-element.linkbutton href="{{ route('review.show', ['review' => $rev]) }}" color="green" target="_blank">
                View
            </x-element.linkbutton>


            <form action="{{ route('task.update', ['task' => $task]) }}" method="post" class="items-center flex">
                @csrf
                @method('PUT')
                <input type="hidden" name="task" value="{{ $task->id }}">
                <input type="hidden" name="redirect_role" value="{{ $task->workflow->subject }}">
                <x-element.submitbutton color="lime" value="assign"
                    confirm='このタスクを完了し、次のワークフローに移行すると、戻ることはできません。本当に進めてよいですか？'>報告者（{{ $task->object->name }}）に、確認したことを通知する</x-element.submitbutton>
            </form>
            （予定：査読報告をロックする機能をつける、再度査読を修正してもらうための掲示板をつくる）
        @elseif($task->workflow->task == 'approve')
            <x-review.commentpaper_link :sub="$task->submit" label="総合判定結果"></x-element.commentpaper_link>

                <form action="{{ route('task.update', ['task' => $task]) }}" method="post" class="items-center flex">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="task" value="{{ $task->id }}">
                    <input type="hidden" name="redirect_role" value="{{ $task->workflow->subject }}">
                    <x-element.submitbutton color="lime" value="assign">最終承認する</x-element.submitbutton>
                </form>
            @elseif($task->workflow->task == 'submit')
                @php
                    $rev = App\Models\Review::where('submit_id', $task->submit->id)
                        ->where('user_id', $task->subject->id)
                        ->first();
                    $rev1obj = $task->submit->rev1();
                    $rev2obj = $task->submit->rev2();
                    $metaobj = $task->submit->meta();
                    $types = ['査読報告', 'メタ査読', '最終判定'];
                @endphp

                @isset($rev->start_at)
                    @if ($rev->status == 2)
                        <div class="bg-cyan-100 px-3 pt-4">
                        @else
                            <div class="bg-yellow-50 px-3 pt-4">
                    @endif
                    <x-element.paperid size=1 :paper_id="$rev->paper->id">
                    </x-element.paperid>
                    <span class="mx-2"></span>

                    {{-- <div class="mx-3"> --}}
                    @if ($task->submit->paper->pdf_file_id != 0)
                        <x-element.linkbutton
                            href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 10)]) }}"
                            color="orange" target="_blank">
                            論文PDFをひらく
                        </x-element.linkbutton>
                    @else
                        No File
                    @endif
                    @isset($answerfile)
                        <x-element.linkbutton2
                            href="{{ route('file.showhash', ['file' => $answerfile->id, 'hash' => substr($answerfile->key, 0, 10)]) }}"
                            color="orange" target="_blank">
                            回答書 をひらく
                        </x-element.linkbutton2>
                    @endif
                    <span class="mx-2"></span>
                    <x-element.linkbutton href="{{ route('review.edit', ['review' => $rev]) }}" color="blue">
                        {{ $types[$rev->target] }}の編集
                    </x-element.linkbutton>
                    <span class="mx-2"></span>
                    <x-element.linkbutton href="{{ route('review.show', ['review' => $rev]) }}" color="green">
                        {{ $types[$rev->target] }}の参照
                    </x-element.linkbutton>
                    {{-- @endif --}}
                    <span class="mx-2"></span>
                    @if($rev->target > 0)
                        @php
                            $revs = App\Models\Review::where('submit_id', $sub->id)
                                ->where('paper_id', $paper->id)
                                ->where('target', '<', $rev->target)
                                ->whereNot('user_id', auth()->id())
                                ->get();
                        @endphp
                        @foreach($revs as $revobj)
                            <x-element.linkbutton href="{{ route('review.show', ['review' => $revobj]) }}" color="lime" size="sm" target="_blank">
                                {{$loop->iteration}}査 {{ $types[$revobj->target] }}({{$revobj->id}})の参照
                            </x-element.linkbutton>
                            <span class="mx-2"></span>
                        @endforeach
                    @endif
                    @if ($rev->status == 2)
                        <span class="inline-block border-2 border-blue-600 p-0.5 text-blue-600 font-bold text-sm">
                            査読完了
                        </span>
                        <span class="mx-1"></span>
                        <form action="{{ route('task.update', ['task' => $task]) }}" method="post"
                            class="inline-block">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="task" value="{{ $task->id }}">
                            <input type="hidden" name="rev_id" value="{{ $rev->id }}">
                            <input type="hidden" name="redirect_role" value="{{ $task->workflow->subject }}">
                            <x-element.submitbutton color="cyan" value="assign">査読完了を報告する</x-element.submitbutton>
                        </form>
                    @endif
                    <span class="mx-2"></span>
                    <x-bb.bb_link :submit="$rev->submit" type="2" :rev_id="$rev->id" size="md"
                        label="投稿管理者に連絡する"></x-bb.bb_link>

                    <span class="mx-2"></span>

                    @if ($rev->paper->pdf_file_id != null)
                        <div class="w-1/2">
                            <a href="{{ route('review.edit', ['review' => $rev]) }}" class="inline">
                    @endif
                    <x-file.paperheadimg :paper="$rev->paper">
                    </x-file.paperheadimg>
                    @if ($rev->paper->pdf_file_id != null)
                        </a>
        </div>
        @endif
    @else
        <x-file.link_pdfthumb :fileid="$rev->paper->pdf_file_id" page="1" label="タイトルページ画像">
        </x-file.link_pdfthumb>
        をみて、利害関係に問題がなければ、

        {{-- <span class="mx-2"></span> --}}
        <form action="{{ route('review.start', ['review' => $rev]) }}" method="post" class="inline-block">
            @csrf
            @method('PUT')
            <input type="hidden" name="task" value="{{ $task->id }}">
            <input type="hidden" name="redirect_page" value="{{ route('role.top', ['role' => 'rev']) }}">

            <x-element.submitbutton color="cyan" value="assign">査読を開始する</x-element.submitbutton>
        </form>
        をおしてください。
        <span class="mx-2"></span>
        <x-bb.bb_link :submit="$rev->submit" type="2" :rev_id="$rev->id" size="md"
            label="投稿管理者に連絡する"></x-bb.bb_link>
        <div class="w-1/2">
            <x-file.paperheadimg :paper="$rev->paper">
            </x-file.paperheadimg>
        </div>
    @endisset
    </div>

    @endif

    </div>
