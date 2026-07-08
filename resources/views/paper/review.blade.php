<x-app-layout>
    <!-- paper.review -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            <x-element.paperid size=2 :paper_id="$sub->paper->id">
            </x-element.paperid>
            <span class="mx-2"></span>
            第{{ $sub->round }}回 {{ __('査読結果') }}
        </h2>
    </x-slot>
    @section('title', '査読結果 ' . $sub->paper->id_03d() . ' r' . $sub->round )
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    @if ($sub->notify_at == null)
        <div class="m-10 p-8 bg-orange-200 text-3xl">
            著者のかたへ：査読結果を確認後、
            <x-sub.confirm_review_link :sub="$sub">査読結果を確認した</x-sub.confirm_review_link>
            をおしてください。
        </div>
    @else
        @if ($sub->ec_decision_at != null)
            <div class="mx-6 my-2 p-2 bg-slate-50 hover:bg-lime-200 text-md text-gray-400">
                査読結果通知日時：{{ $sub->ec_decision_at }}
            </div>
        @endif
        @if ($sub->notify_at != null)
            <div class="mx-6 my-1 p-2 bg-slate-50 hover:bg-cyan-200 text-md text-gray-400">
                著者確認日時：{{ $sub->notify_at }}
            </div>
        @endif
    @endif


    <div class="pt-2 px-6">
        <div class="bg-slate-200 p-3 dark:bg-slate-600 dark:text-gray-300">
            <x-element.category :cat="$sub->category_id" size="lg">
            </x-element.category>
            <span class="font-bold mx-4">第{{ $sub->round }}回 {{ __('査読結果') }}</span>
            <span class="mx-1"></span>
            <span class="text-2xl font-bold">{{ $accepts[$sub->accept_id] }}</span>
        </div>
    </div>

    <div class="mx-6 my-2">
        @php
            $count = 0;
            $accept = App\Models\Accept::find($sub->accept_id);
            $isaccepted = $accept->judge > 0; // 不採択の場合、返さない項目があるので、ここで調べておく
            $vpsubdescs = App\Models\Viewpoint::where('category_id', $sub->category_id)
                ->select('subdesc', 'desc')
                ->get()
                ->pluck('subdesc', 'desc')
                ->toArray();
            $nameofmeta = App\Models\Setting::getval('name_of_meta');
            if ($nameofmeta == null) {
                $nameofmeta = 'メタ';
            }
            // スティッキーナビ用リスト
            $navItems = [];
            $navCount = 0;
            foreach ($sub->reviews as $navIdx => $navRev) {
                if ($navRev->target == 1) {
                    $navLabel = 'メタ査読者';
                } elseif ($navRev->target == 2) {
                    $navLabel = '判定結果';
                } else {
                    $navCount++;
                    $navLabel = '査読者' . mb_convert_kana($navCount, 'N');
                }
                $navItems[] = ['id' => 'review-table-' . $navIdx, 'label' => $navLabel];
            }
        @endphp

        {{-- スティッキーナビゲーション --}}
        <div id="sticky-nav"
            class="fixed top-0 left-0 right-0 z-40 bg-white dark:bg-slate-800 shadow-md py-2 px-4 transition-transform duration-300 -translate-y-full">
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 mr-2">目次：</span>
                @foreach ($navItems as $item)
                    <a href="#{{ $item['id'] }}"
                        class="text-sm mx-1 px-4 py-1 bg-slate-200 hover:bg-slate-300 dark:bg-slate-600 dark:hover:bg-slate-500 dark:text-slate-200 rounded transition-colors">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <span class="mx-4"></span>
                <span class="text-sm text-gray-500 dark:text-gray-400">※数字キー(1〜4)でも移動できます。0 or Escapeで最上部へスクロールします。</span>

            </div>
        </div>

        @foreach ($sub->reviews as $rev)
            <table id="review-table-{{ $loop->index }}" class="table-auto my-2 scroll-mt-14">
                <thead>
                    <tr>
                        <th colspan="2" class="bg-slate-300 border-4 border-slate-300 text-left pl-6">
                            @if ($rev->target == 1)
                                メタ査読者
                            @elseif ($rev->target == 2)
                                判定結果
                            @else
                                @php
                                    $count++;
                                @endphp
                                査読者{{ mb_convert_kana($count, 'N') }}
                            @endif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rev->scores_and_comments(1, 0, $isaccepted) as $vpdesc => $valstr)
                        <tr
                            class="border-4 border-slate-300 {{ $loop->iteration % 2 === 0 ? 'bg-neutral-200 dark:bg-neutral-200' : 'bg-white-50 dark:bg-neutral-300' }}">
                            <td nowrap class="p-2 bg-slate-100 border-2 border-slate-300">
                                {{ $vpdesc }}
                            </td>
                            <td class="p-2 hover:bg-lime-50 transition-colors text-left">
                                @if ($valstr == '(未入力)')
                                    （とくにお伝えする事項は、ありません）
                                @else
                                    {!! nl2br(htmlspecialchars($valstr)) !!}
                                    @if (strlen($valstr) < 2)
                                        @php
                                            $item_title = App\Models\Viewpoint::firstContent($vpdesc);
                                            $item_title = str_replace('で評価してください．', '', $item_title);
                                        @endphp
                                        <span class="text-gray-400 text-sm pl-8">〈参考〉{{ $item_title }}</span>
                                    @endif
                                @endif
                                {{-- vpsubdesc スコアの意味などを表示する --}}
                                @isset($vpsubdescs[$vpdesc])
                                    <span class="mx-6"></span>
                                    <span class="text-gray-400 text-sm">{{ $vpsubdescs[$vpdesc] }}</span>
                                @endisset
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    </div>


    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="{{ route('paper.index') }}" color="gray" size="lg">
            &larr; 投稿一覧に戻る
        </x-element.linkbutton>
    </div>

    @push('localjs')
        <script>
            (function() {
                const stickyNav = document.getElementById('sticky-nav');
                if (!stickyNav) return;

                window.addEventListener('scroll', function() {
                    if (window.scrollY > 200) {
                        stickyNav.classList.remove('-translate-y-full');
                    } else {
                        stickyNav.classList.add('-translate-y-full');
                    }
                });

                // スムーススクロール
                stickyNav.querySelectorAll('a').forEach(function(anchor) {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    });
                });

                // 数字キー(1〜4)でナビボタンをクリック、0/Escapeで最上部へスクロール
                document.addEventListener('keydown', function(e) {
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target
                        .isContentEditable) return;
                    if (e.key === '0' || e.key === 'Escape') {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    const index = parseInt(e.key, 10);
                    if (index >= 1 && index <= 4) {
                        const links = stickyNav.querySelectorAll('a');
                        const link = links[index - 1];
                        if (link) link.click();
                    }
                });

            })();
        </script>
    @endpush

</x-app-layout>
