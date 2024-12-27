<x-app-layout>
    <!-- sub.show -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            <x-element.paperid size=2 :paper_id="$sub->paper->id">
            </x-element.paperid>
            <span class="mx-2"></span>
            {{ __('投稿管理') }}
            Title: {{$sub->paper->title}} 
            Status: {{$sub->paper->currentstatus->name}} (stat={{$sub->paper->currentstatus->id}})
            <span class="mx-2"></span>
            <x-review.commentpaper_link :sub="$sub" color="purple" label="査読報告"></x-element.commentpaper_link>

        </h2>
    </x-slot>
    @section('title', 'P' . $sub->paper->id . ' s' . $sub->id . ' ' . $sub->paper->title)

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <x-element.component_name>
        subshow
    </x-element.component_name>
    <div class="mx-6">
            <x-sub.subsummarytable :sub="$sub" />
    </div>

    <div class="m-6">
        テスト用：自動でワークフローを進行させる
        <form action="{{ route('manage.submit_proceed', ['sub' => $sub->id]) }}" method="post">
            @csrf
            @method('PUT')
            <select name="phase">
                @foreach ($sub->tasks as $task)
                    @if($task->approved)
                        @continue
                    @endif
                    <option value="{{ $task->id }}"> 
                       ({{$task->workflow->id}}) {{ $task->workflow->description }} (by {{$task->workflow->subject}})</option>
                @endforeach
            </select>
            <x-element.submitbutton value="proceed" color="yellow">まで、ランダムに割り振って進行させる</x-element.submitbutton>

または            <x-element.submitbutton value="reset" color="red">すべてリセットする</x-element.submitbutton>
        </form>
    </div>

</x-app-layout>
