<!-- task.sendrequest_confirm -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('査読依頼メールの確認・編集') }}
        </h2>
    </x-slot>
    @section('title', '査読依頼メールの確認')

    <div class="py-2 px-6">
        <div class="my-5">
            <x-element.linkbutton href="{{ route('paper.manage', ['paper' => $review->paper_id]) }}" color="gray"
                size="sm">
                &larr; 戻る
            </x-element.linkbutton>
        </div>

        <p class="mb-3 dark:text-slate-300">
            {{ $reviewer->name }} さま宛の査読依頼メールです。本文を確認・編集してから送信してください。
        </p>

        <form action="{{ route('task.sendrequest.prepare', ['review' => $review->id, 'revuid' => $revuid]) }}"
            method="post">
            @csrf

            <div class="bg-slate-100 py-2 px-2 dark:bg-slate-700">
                <label for="subject" class="font-bold text-sm dark:text-slate-200">Subject</label><br>
                <input type="text" name="subject" id="subject"
                    class="w-full max-w-3xl font-mono text-gray-600 p-1 border dark:bg-slate-800 dark:text-slate-100 dark:border-slate-500"
                    value="{{ $subject }}">
            </div>
            <div class="px-2">
                <textarea name="body" rows="20"
                    class="w-full max-w-3xl font-mono text-gray-600 p-1 border dark:bg-slate-800 dark:text-slate-100 dark:border-slate-500">{{ $body }}</textarea>
            </div>

            <div class="mt-3 px-4 flex items-center gap-2">
                <x-element.submitbutton color="pink" confirm="本当に{{ $reviewer->name }}さんに査読依頼メールを送信してよいですか？">
                    この内容で送信する
                </x-element.submitbutton>
                <span class="mx-4"></span>
                <button type="submit" formaction="{{ route('task.sendrequest.preview', ['review' => $review->id, 'revuid' => $revuid]) }}"
                    formtarget="_reviewrequest_preview"
                    class="inline-flex justify-center py-1 px-2 mb-0.5 border border-transparent shadow-sm text-md font-medium
                     rounded-md text-white bg-gray-500 hover:bg-gray-600 focus:outline-none
                     focus:ring-2 focus:ring-offset-2 focus:ring-gray-500
                     dark:bg-gray-800 dark:hover:bg-gray-600 dark:hover:text-gray-200">
                    メール文面プレビュー
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
