@props([
    'review' => null,
])
@php
    $task = App\Models\Task::where('subject_id', $review->user->id)
        ->where('submit_id', $review->submit->id)
        ->where('workflow_id', 4)
        ->first();
    $dates_sendrequest = App\Models\LogAccess::dates_sendrequest($review->id, $review->user->id);
@endphp
<!-- components.task.tswitch -->
{{-- <x-element.component_name>
    tswitch
</x-element.component_name> --}}
@isset($task)
    @if ($task->completed)
        査読完了
        <span class="mx-1"></span>
        <span class="text-sm">
            <livewire:review-lock :review="$review" />
        </span>
        <span class="mx-1"></span>
        <x-element.linkbutton2 href="{{ route('review.show', ['review' => $review]) }}" color="purple" size="xs"
            target="_blank">
            査読報告
        </x-element.linkbutton2>
    @else
        査読タスク依頼中
    @endif
@else
    @if ($review->request_at)
        <span class="text-cyan-600 font-extrabold">査読依頼送信済み</span>
        <span class="text-cyan-600 text-sm">
            @foreach ($dates_sendrequest as $logac)
                {{ substr($logac->created_at, 5, 11) }}<br>
            @endforeach
        </span>
    @else
        <span class="text-red-500 font-extrabold">依頼メール未送信です</span>
    @endif
    <x-element.linkbutton href="{{ route('task.sendrequest', ['review' => $review, 'revuid' => $review->user->id]) }}"
        color="pink" size="sm" confirm="本当に{{ $review->user->name }}さんに査読依頼メールを送信してよいですか？">
        依頼メール送信
        {{-- {{$review->id}} {{$review->user->id}} --}}
    </x-element.linkbutton>

    <br>
    <x-element.req_confirm_link :rev="$review">
    </x-element.req_confirm_link>
    <br>

    <x-element.linkbutton href="{{ route('task.sendfirstmessage', ['review' => $review, 'revuid' => $review->user->id]) }}"
        color="pink" size="sm" confirm="本当に{{ $review->user->name }}さんにパスワード設定方法（最初のログインの方法）メールを送信してよいですか？">
        パスワード設定方法を送信
        {{-- {{$review->id}} {{$review->user->id}} --}}
    </x-element.linkbutton><br>

    <x-element.linkbutton href="{{ route('task.create', ['review' => $review, 'revuid' => $review->user->id]) }}"
        size="sm" color="blue">
        査読開始（内諾が得られてから押す）
        {{-- {{$review->id}} {{$review->user->id}} --}}
    </x-element.linkbutton>
@endisset
