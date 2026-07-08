<x-app-layout>
    <!-- review.commentpaper -->
    @php
        $catspans = App\Models\Category::spans();
        $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $cats = App\Models\Category::select('name', 'id')->get()->pluck('name', 'id')->toArray();

        $nameofmeta = App\Models\Setting::getval('name_of_meta');

        $count = 0;

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
    @section('title', $paper->id_03d() . ' r' . $sub->round . ' 査読結果（審議用）')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            第{{ $sub->round }}回 {{ __('査読結果') }} &nbsp; {{ $paper->id_03d() }} &nbsp; {{ $paper->title }} &nbsp;

            {!! $catspans[$cat_id] !!}

        </h2>
        <x-element.component_name>
            commentpaper
        </x-element.component_name>
    </x-slot>

    <div class="py-2 px-6">
        @if ($paper->pdf_file_id != 0 && $paper->pdf_file != null)
            <a class="underline text-blue-600 hover:bg-lime-200 p-2"
                href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                target="_blank">
                PDF ({{ $paper->pdf_file->pagenum }}pages)
            </a>
            <span class="text-sm text-gray-500">{{ substr($paper->pdf_file->created_at, 0, 16) }}</span>
        @endif
        @if ($paper->video_file_id != 0 && $paper->video_file != null)
            <span class="mx-2"></span>
            <a class="underline text-blue-600 hover:bg-lime-200 p-2"
                href="{{ route('file.showhash', ['file' => $paper->video_file_id, 'hash' => substr($paper->video_file->key, 0, 8)]) }}"
                target="_blank">
                Video
            </a>
            <span class="text-sm text-gray-500">{{ substr($paper->video_file->created_at, 0, 16) }}</span>
        @endif
        @if ($paper->img_file_id != 0 && $paper->img_file != null)
            <span class="mx-2"></span>
            <a class="underline text-blue-600 hover:bg-lime-200 p-2"
                href="{{ route('file.showhash', ['file' => $paper->img_file_id, 'hash' => substr($paper->img_file->key, 0, 8)]) }}"
                target="_blank">
                Image
            </a>
            <span class="text-sm text-gray-500">{{ substr($paper->img_file->created_at, 0, 16) }}</span>
        @endif
        @isset($bb)
            <span class="mx-2"></span>
            @isset($bb->paper)
                <x-element.linkbutton2 href="{{ route('bb.show', ['bb' => $bb->id, 'key' => $bb->key]) }}" color="green"
                    target="_blank" size="sm">
                    著者との掲示板
                    ({{ $bb->nummessages() }} messages)
                </x-element.linkbutton2>
            @else
                <div>Error: No Paper associated {{ $bb->id }}</div>
            @endisset
        @endisset
        <span class="mx-2"></span>
        <x-element.linkbutton2 href="{{ route('paper.review', ['sub' => $sub->id, 'token' => $paper->token()]) }}"
            color="purple" target="_blank" size="sm">
            著者がみる査読結果 </x-element.linkbutton2>

    </div>

    <div class="mx-6 mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
        <div class="w-full">
            <x-file.paperheadimg :paper="$paper">
            </x-file.paperheadimg>
        </div>
        <div class="w-full">
            @if ($paper->img_file_id != 0 && $paper->img_file != null)
                <img
                    src="{{ route('file.showhash', ['file' => $paper->img_file_id, 'hash' => substr($paper->img_file->key, 0, 8)]) }}">
            @endif
        </div>
    </div>

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
        <div class="m-6">
            <table id="review-table-{{ $loop->index }}" class="table-auto scroll-mt-14">

                <thead>
                    <tr>
                        <th colspan=2 class="bg-slate-300 border-4 border-slate-300 text-left pl-10">
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

                            <span class="text-gray-200 ml-10">（査{{ $rev->id }}）</span>

                            @if ($rev->ismeta)
                                <span class="mx-2 font-bold text-purple-500">({{ $nameofmeta }}) </span>
                            @endif
                        </th>
                        {{-- <th class="bg-slate-300 border-4 border-slate-300">
                        </th> --}}
                    </tr>
                </thead>
                <tbody>
                    {{-- 最初の引数が1だと、著者に帰るコメント・スコアのみが表示されるので、ここでは0にしている。 --}}
                    @foreach ($rev->scores_and_comments(0, 0, $sub->accept_id > 0) as $vpdesc => $valstr)
                        <tr
                            class="border-4 border-slate-300 {{ $loop->iteration % 2 === 0 ? 'bg-neutral-200 dark:bg-slate-600' : 'bg-white-50 dark:bg-slate-800' }}">
                            <td class="p-2 bg-slate-100 border-2 border-slate-300 text-sm dark:bg-slate-400">
                                {{ $vpdesc }}
                            </td>
                            <td class="p-2 text-left dark:text-gray-200">
                                {{-- @if ($valstr == '(未入力)')
                                    （とくにお伝えする事項は、ありません）
                                @else --}}
                                {!! nl2br(App\Models\Review::urllink($valstr)) !!}
                                {{-- @endif --}}
                                @if (strlen($valstr) < 2)
                                    @php
                                        $item_title = App\Models\Viewpoint::firstContent($vpdesc);
                                        $item_title = str_replace('で評価してください．', '', $item_title);
                                    @endphp
                                    <span class="text-gray-400 text-sm pl-8">（{{ $item_title }}）</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
    {{-- // また、その下に、各査読者のスコアとコメントをすべて表示する。 --}}

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
