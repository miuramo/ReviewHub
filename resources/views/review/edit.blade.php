<x-app-layout>
    <!-- review.edit -->
    @php
        $catspans = App\Models\Category::spans();

        $roleofreview = ['rev', 'meta', 'rev'];
    @endphp
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => $roleofreview[$review->target]]) }}"
                color="gray" size="sm">
                &larr; 担当査読一覧に戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            @php
                $nameofmeta = App\Models\Setting::getval('NAME_OF_META');
            @endphp
            @if ($review->target == 1)
                {{ $nameofmeta }}
            @elseif ($review->target == 2)
                幹事
            @endif
            {{ __('査読（編集）') }}

            <x-element.paperid size=2 :paper_id="$review->paper->id">
            </x-element.paperid>

            &nbsp; {!! $catspans[$review->paper->category_id] !!}
        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="py-2 px-6">
        <x-element.h1>入力欄を空にすると <span class="text-red-600 font-extrabold">(未入力)</span>
            となります。<br>
            各項目は、編集後フォーカスを外すと、緑色にフラッシュして自動保存されます。本当に保存されたかどうか確認したいときはページを再読み込みしてください。（フォーム全体の保存ボタンはありません。）
        </x-element.h1>

        <table class="table-auto">
            <tbody>
                @foreach ($viewpoints as $vpt)
                    <form action="{{ route('review.update', ['review' => $review->id]) }}" method="post"
                        id="revform{{ $vpt->id }}">
                        @csrf
                        @method('put')
                        <input type="hidden" name="paper_id" value="{{ $review->paper->id }}">
                        <input type="hidden" name="review_id" value="{{ $review->id }}">
                        <input type="hidden" name="viewpoint_id" value="{{ $vpt->id }}">
                        <input type="hidden" name="mandatory" value="{{ $vpt->mandatory }}">
                        <input type="hidden" name="is_mandatory" value="{{ $vpt->is_mandatory }}">
                        @php
                            $formid = "revform{$vpt->id}";
                            $current = isset($scores[$vpt->id]) ? $scores[$vpt->id]->valuestr : null;
                        @endphp
                        <div class="mx-10">
                            <x-enquete.itmedit :itm="$vpt" :formid="$formid" :current="$current" :loop="$loop">
                            </x-enquete.itmedit>
                        </div>
                    </form>
                @endforeach
            </tbody>
        </table>

        <x-element.h1>投稿情報 <span class="mx-3"></span>
            <x-element.paperid size=2 :paper_id="$review->paper->id">
            </x-element.paperid>
            &nbsp; {!! $catspans[$review->paper->category_id] !!}
        </x-element.h1>
        <div class="mx-6 mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="w-full">
                <x-file.paperheadimg :paper="$review->paper">
                </x-file.paperheadimg>
            </div>
            <div class="text-sm mt-2 ml-2">
                {{-- まず、showonreviewerindex アンケートをあつめる。 --}}
                <x-enquete.Rev_enqview :rev="$review">
                </x-enquete.Rev_enqview>
            </div>
        </div>

        <x-element.h1>
            各項目は、編集後フォーカスを外すと、緑色にフラッシュして自動保存されます（フォーム全体の保存ボタンはありません）。<br>
            →の右に入力内容が表示されていれば、すでに保存されています。
            <x-element.linkbutton href="{{ route('role.top', ['role' => $roleofreview[$review->target]]) }}"
                color="cyan" >
                編集を保存・終了し、担当査読一覧に戻る
            </x-element.linkbutton> <br>
            <span class="text-pink-500 font-extrabold">担当査読一覧画面で「査読完了を報告する」を押していただくと、査読完了となります。</span>
        </x-element.h1>

        {{-- <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('role.top', ['role' => $roleofreview[$review->target]]) }}"
                color="gray" size="sm">
                &larr; 担当査読一覧に戻る
            </x-element.linkbutton>
        </div> --}}

    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed.js"></script>
    @endpush
    <script>
        function resizeTextarea(el) {
            el.style.height = "auto";
            el.style.height = el.scrollHeight + "px";
        }
        // ページロード時に全てのtextareaを自動リサイズ
        window.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll("textarea.h-auto-resize").forEach(el => resizeTextarea(el));
        });
    </script>
</x-app-layout>
