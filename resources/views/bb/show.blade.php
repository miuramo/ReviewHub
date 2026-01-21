<x-app-layout>
    @php
        $names = [1 => '著者と投稿管理者の連絡', 2 => '査読者との', 3 => '全査読者との', 4 => '投稿管理者同士の'];

        // もし、type=2(査読掲示板) で、bbs->rev_id でとってきたReview.user_id （査読者）が同じ、かつpaper_idが同じなら、
        // 兄弟Bbを探してくる。
        // そして、作成時刻が古いほうから順番にループをまわしていく。
        // 投稿するときのBbは、最新のものを使う。
        // まず、メインのBb->rev_id のReview.user_id と同じユーザIDのものを探す。
        if ($bb->type == 2) {
            $revobj = \App\Models\Review::find($bb->rev_id);
            if (!$revobj) {
                abort(404, '対応する査読情報が見つかりません。');
            }
            $main_rev_user_id = $revobj->user_id;
            $related_reviews = \App\Models\Review::where('paper_id', $bb->paper_id)
                ->where('category_id', $revobj->category_id)
                ->where('user_id', $main_rev_user_id)
                ->orderBy('created_at', 'asc')
                ->pluck('id')
                ->toArray();
            // info($related_reviews);
            $related_bbs = \App\Models\Bb::with('messages')
                ->where('paper_id', $bb->paper_id)
                ->where('type', 2)
                ->whereIn('rev_id', $related_reviews)
                ->orderBy('created_at', 'asc')
                ->get()
                ->keyBy('id');
        } else {
            $main_rev_user_id = null;
        }

    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ $names[$bb->type] . __('掲示板') }}

            <span class="mx-2"></span>
            <x-element.paperid size=2 :paper_id="$bb->paper_id">
            </x-element.paperid>
            <span class="mx-2"></span>
            <x-element.category :cat="$bb->category_id">
            </x-element.category>
        </h2>
        {{-- <div class="text-lg mt-4 font-bold bg-slate-200 py-2 px-4 inline-block rounded-md">{{ $bb->paper->title }}</div> --}}
    </x-slot>
    @section('title', $bb->paper->id_03d() . ' 掲示板')

    <!-- paper.show -->

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="py-2 px-6">
        <x-paper.shoshi_list :paper="$bb->paper"></x-paper.shoshi_list>

        <div class="py-0.5"></div>
        @if ($isEC)
            <x-element.button id="toggleButton" size="sm" value="参加者をみる（投稿管理者のみ）" color="lime"
                onclick="openclose('div_bbparticipants')">
            </x-element.button>
            @php
                $uclist = $bb->get_participants();
            @endphp
            <div class="hidden-content p-2 bg-lime-100" style="display:none" id="div_bbparticipants">
                @foreach ($uclist as $uc)
                    @if ($uc instanceof App\Models\User)
                        <div class="inline-block bg-lime-200 p-1 m-1 rounded-md">
                            <x-element.login_as :user="$uc"></x-element.login_as>
                        </div>
                    @else
                        <div class="inline-block bg-lime-200 p-1 m-1 rounded-md">
                            {{ $uc->email }}
                        </div>
                    @endif
                @endforeach
            </div>
            <span class="mx-4"></span>
            <x-element.linkbutton :href="route('paper.manage', ['paper' => $bb->paper_id])" color="gray" size="sm">
                論文管理画面（投稿管理者のみ）
            </x-element.linkbutton>
        @endif

        {{-- 査読掲示板で、複数の兄弟掲示板があるとき、古い方から全部表示する --}}
        @if ($bb->type == 2 && isset($related_bbs) && count($related_bbs) > 1)
            @foreach ($related_bbs as $rbb)
                <hr class="mt-2">
                <div
                    class="font-extrabold text-lg py-2 text-gray-500 text-center bg-gray-200 hover:bg-lime-100 hover:transition-colors transition-all">
                    {{ \App\Models\Bb::ordinal($loop->iteration) }} review </div>
                <hr class="mb-2">
                @foreach ($rbb->messages as $mes)
                    <x-bb.mes :mes="$mes"></x-bb.mes>
                @endforeach

                {{-- そして、書き込みは最後の掲示板に対して行う。 --}}
                @php
                    $bb = $rbb;
                @endphp
            @endforeach
        @else
            @foreach ($bb->messages as $mes)
                <x-bb.mes :mes="$mes"></x-bb.mes>
            @endforeach
        @endif

        <div class="text-right mt-1">
            <form action="{{ route('bbmes.store', ['bb' => $bb->id, 'key' => $bb->key]) }}" method="post"
                id="post_bbmes" enctype="multipart/form-data" onsubmit="return showMessageBeforeSend();">
                @csrf
                @method('post')
                <input type="hidden" name="key" value="{{ $bb->key }}">

                <div
                    class="inline-block w-3/4 bg-green-300 p-2 rounded-md mt-5 hover:bg-green-400 hover:transition-colors duration-500">
                    <div class="px-2 text-left text-sm">送信フォーム</div>
                    <input class="w-full p-2 bg-green-200 rounded-md border-green-300 border-2" type="text"
                        size="70" name="sub" id="bbsub" placeholder="ここに Subject (Title) を入力"
                        onkeydown="return disableEnterKey(event);"
                        @isset($revid)
                            value="[RevID : {{ $revid }}]  "
                        @endisset>
                    <textarea class="w-full mt-1 p-2 bg-green-100 rounded-md border-green-300  border-2" name="mes" id="bbmes"
                        cols="70" rows="10" placeholder="ここにメッセージを入力"></textarea>
                    <label for="bbfile" class="text-sm">ファイル添付（オプション）</label>
                    <input class="text-sm" type="file" name="bbfile" id="bbfile">
                    送信すると、関係者にメールで通知されます。<x-element.submitbutton value="submit" color="green" id="bb_submit">了解して送信する
                    </x-element.submitbutton>
                    @if ($bb->paper->owner != auth()->id())
                        <div class="text-left">
                            <span
                                class="mx-2 p-1 text-xs  bg-yellow-200 dark:bg-yellow-500">差替用のファイルを添付するときは、投稿時の著者アカウントでログインしてください。</span>
                        </div>
                    @endif
                </div>
            </form>
            @if ($isEC)
                <x-element.button id="toggleButton" size="sm" value="定型文の選択画面をひらく（投稿管理者のみ）" color="teal"
                    onclick="openclose('div_bbtemplate')">
                </x-element.button>
                <div class="hidden-content p-2 bg-teal-100" style="display:none" id="div_bbtemplate">
                    ボタンを押すと、Subject と Message に定型文が入力されます。<br>
                    @php
                        $templates = [];
                        if ($bb->type == 2) {
                            $templates = $bb->getRevTemplates();
                        }
                        $json_templates = json_encode($templates);
                    @endphp
                    @foreach ($templates as $tempname => $tempvalue)
                        <x-element.button id="bbtemplate{{ $loop->index }}" size="sm" value="{{ $tempname }}"
                            color="teal" onclick="insertTemplate('{{ $tempname }}')">
                        </x-element.button>
                    @endforeach
                </div>
                <script>
                    const templates = {!! $json_templates !!};

                    function insertTemplate(tempname) {
                        let sub = document.getElementById('bbsub');
                        let mes = document.getElementById('bbmes');
                        console.log(templates);
                        sub.value = templates[tempname].sub;
                        mes.value = templates[tempname].mes;
                        // if (tempname === '査読結果の開示報告') {
                        //     sub.value = '査読結果を著者に開示しました';
                        //     mes.value = '査読者の皆様へ、\n\n';
                        // } else if (tempname === '査読依頼') {
                        //     sub.value = '査読依頼: [論文タイトル]';
                        //     mes.value = '査読者の皆様へ、\n\n';
                        // } else if (tempname === '査読結果の送付') {
                        //     sub.value = '査読結果をお送りいたします';
                        //     mes.value = '査読者の皆様へ、\n\n';
                        // }
                        mes.focus();
                    }
                </script>
            @endif

        </div>
        <div class="my-10"></div>

    </div>
    <script>
        let isComposing = false;

        document.getElementById("bbsub").addEventListener("compositionstart", () => {
            isComposing = true;
        });
        document.getElementById("bbsub").addEventListener("compositionend", () => {
            isComposing = false;
        });

        function disableEnterKey(event) {
            // IME確定時ではなく、通常のEnterキーのみ無効化
            if (event.key === "Enter" && !isComposing) {
                document.getElementById("bbmes").focus();
                return false; // イベントをキャンセル（送信を無効化）
            }
            return true;
        }

        function showMessageBeforeSend() {
            var mes = document.getElementById('bbmes').value;
            var sub = document.getElementById('bbsub').value;
            if (mes == '' || sub == '') {
                alert('Subject と Message は必ず入力してください。');
                if (sub == '') document.getElementById("bbsub").focus();
                else document.getElementById("bbmes").focus();
                return false;
            }
            // 確認ダイアログを表示
            if (!confirm('この内容で送信します。よろしいですか？（通知メールにはファイルは添付されません。）')) {
                document.getElementById("bbmes").focus();
                return false;
            }
            // submitボタンを無効化
            document.getElementById('bb_submit').disabled = true;
            return true;
        }
    </script>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
