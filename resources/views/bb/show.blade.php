<x-app-layout>
    @php
        $names = [1 => '著者と投稿管理者の連絡', 2 => '査読者との', 3 => '全査読者との', 4 => '投稿管理者同士の'];
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
        @endif


        @foreach ($bb->messages as $mes)
            <x-bb.mes :mes="$mes"></x-bb.mes>
        @endforeach

        <div class="text-right mt-1">
            <form action="{{ route('bbmes.store', ['bb' => $bb->id, 'key' => $bb->key]) }}" method="post"
                id="post_bbmes" enctype="multipart/form-data" onsubmit="return showMessageBeforeSend();">
                @csrf
                @method('post')
                <input type="hidden" name="key" value="{{ $bb->key }}">

                <div
                    class="inline-block w-3/4 bg-green-300 p-2 rounded-md mt-5 hover:bg-green-400 hover:transition-colors">
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
