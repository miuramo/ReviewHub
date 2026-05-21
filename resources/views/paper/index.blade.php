<x-app-layout>
    <!-- paper.index -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('投稿一覧') }}
            <span class="mx-10 text-sm text-blue-500 dark:text-blue-700">（投稿情報の編集や、ファイルアップロードするには、論文画像をクリックしてください）</span>
        </h2>
    </x-slot>
    @php
        $name_of_managers = \App\Models\Setting::getValue('NAME_OF_MANAGERS');

        $revreturn = App\Models\Category::select('status__revreturn_on', 'id')
            ->get()
            ->pluck('status__revreturn_on', 'id')
            ->toArray();
        define('STATUS_ACCEPTED', 10); // TODO: 定数を適切な場所に移動、設定を読み込むかDBで「採録」がある行から取得する
    @endphp
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-2 px-6">
        {{-- ファイルアップロードがあると、#filelist の中身をAjaxでかきかえていく --}}
        <div id="mypaperlist" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if (count($all) == 0)
                <div
                    class="xs:text-sm sm:text-xl text-orange-400 bg-yellow-200 dark:bg-yellow-800 dark:text-orange-700 p-4 rounded-md text-center">
                    あなたが作成した投稿情報はまだありません。
                    <div class="mt-5 mb-2">
                        <x-element.linkbutton href="{{ route('paper.create') }}" color="yellow">
                            新規投稿 </x-element.linkbutton>
                    </div>
                </div>
                <div></div>

                @if (auth()->user()->can('role', 'rev'))
                    <div
                        class="xs:text-sm sm:text-xl text-blue-700 bg-cyan-200 dark:bg-cyan-800 dark:text-blue-700 p-4 rounded-md text-center">
                        【査読者のかたへ】依頼された査読をはじめるには、以下の「査読一覧」ボタン（または、トップメニューの「査読」）をクリックしてください。
                        <div class="mt-5 mb-2">
                            <x-element.linkbutton href="{{ route('role.top', ['role' => 'rev']) }}" color="cyan">
                                査読一覧 </x-element.linkbutton>
                        </div>
                    </div>
                @endif
            @else
                @foreach ($all as $paper)
                    @if ($paper->accepted)
                        <div
                            class="bg-cyan-100 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250 dark:bg-cyan-300">
                        @else
                            <div
                                class="bg-slate-200 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250 dark:bg-slate-700">
                    @endif
                    <x-element.paperid size=2 :paper_id="$paper->id">
                    </x-element.paperid>
                    <span class="mx-1"></span>
                    <x-element.category :cat="$paper->category_id">
                    </x-element.category>
                    <span class="mx-2"></span>
                    {{-- コメント：submits はラウンド数が大きい順に並んでいます。 --}}
                    {{-- 第1回の再投稿期限は、第2ラウンド投稿後は、表示しなくてよい --}}
                    @php
                        $current_submit = $paper->submits->first();
                        $current_round = $current_submit->round;
                        $current_resubmit_until = $current_submit->resubmit_until;
                    @endphp

                    <span class="text-lg font-bold text-gray-800 dark:text-slate-400">
                        {{-- まだ採択ではない場合で、2回目以降の投稿であれば、ラウンド数を表示します。 --}}
                        @if ($current_submit->round > 1 && $paper->status_id < STATUS_ACCEPTED)
                            【第{{ $current_submit->round }}回】
                        @endif
                        {{ $paper->currentstatus->name }}
                    </span>

                    @foreach ($paper->submits as $sub)
                        @if ($sub->ec_decision_at != null)
                            <span class="mx-1"></span>
                            <x-element.linkbutton
                                href="{{ route('paper.review', ['sub' => $sub->id, 'token' => $paper->token()]) }}"
                                color="orange" target="_blank">
                                第{{ $sub->round }}回 査読結果 </x-element.linkbutton>
                        @endif
                        @if ($sub->resubmit_until != null && $sub->submitted_at == null)
                            <br>
                            <span
                                class="border-2 border-orange-500 bg-orange-200 hover:bg-yellow-200 p-2 font-extrabold">再投稿期限：{{ $sub->resubmit_until }}</span>
                        @endif
                    @endforeach

                    <span class="mx-1"></span>
                    <x-bb.bb_link :submit="$paper->currentsubmit" type="1" label="{{ $name_of_managers }}に連絡"></x-bb.bb_link>

                    <a href="{{ route('paper.edit', ['paper' => $paper->id]) }}">
                        <x-file.paperheadimg :paper=$paper>
                        </x-file.paperheadimg>
                    </a>
        </div>
        @endforeach
        @endif
    </div>


    @if (count($coauthor_all) == 0)
        <div
            class="xs:text-sm sm:text-xl text-slate-400 bg-slate-200 p-4 rounded-md text-center mt-10  dark:bg-slate-700 dark:text-slate-400">
            あなたが表示できる共著者投稿はありません。
            <div class="text-sm mt-5">
                ここに共著の投稿を表示するには、あなたの登録メールアドレスを投稿者に伝え、投稿連絡用メールアドレスへの追加を依頼してください。
            </div>
        </div>
    @else
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 pt-10 pb-3">
            {{ __('共著者分') }}
        </h2>
        <div id="coauthorpaperlist" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($coauthor_all as $paper)
                @php
                    $id_03d = sprintf(env('PID_FORMAT', '%04d'), $paper->id);
                @endphp
                @if ($paper->accepted)
                    <div
                        class="bg-cyan-100 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250  dark:bg-cyan-300">
                        <span class="border-2 border-blue-600 p-1 text-blue-600 font-bold">投稿完了</span>
                    @else
                        <div class="bg-slate-200 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250">
                @endif
                {{-- <div class="bg-yellow-100 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250"> --}}
                <x-element.paperid size=2 :paper_id="$paper->id">
                </x-element.paperid>
                &nbsp;
                &nbsp;
                <x-element.category :cat="$paper->category_id">
                </x-element.category>





                <a href="{{ route('paper.show', ['paper' => $paper->id]) }}">
                    <x-file.paperheadimg :paper=$paper>
                    </x-file.paperheadimg>
                </a>
        </div>
    @endforeach
    </div>
    @endif

    </div>

</x-app-layout>
