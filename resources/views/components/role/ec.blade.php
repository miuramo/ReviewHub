@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    // 査読プロセスをまわす（査読者を割り当てる）カテゴリ
    $cat_arrange_review = App\Models\Category::where('status__arrange_review', true)
        ->get()
        ->pluck('name', 'id')
        ->toArray();

    $tasks = App\Models\Task::with('submit')->where('subject_id', auth()->id())->where('completed', 0)->get();

    $recent = App\Models\Task::with('submit')
        ->where('subject_id', auth()->id())
        ->where('completed', 1)
        ->orderBy('updated_at', 'desc')
        ->get();

    $approvetasks = App\Models\Task::with('submit')
        ->where('object_id', auth()->id())
        ->where('completed', 1)
        ->where('require_approve', 1)
        ->where('approved', 0)
        ->get();

    $recentapproved = App\Models\Task::with('submit')
        ->where('object_id', auth()->id())
        ->where('completed', 1)
        ->where('require_approve', 1)
        ->where('approved', 1)
        ->orderBy('updated_at', 'desc')
        ->get();

    //投稿管理者に重複があったら、削除する
    // paper_manager table の paper_id と user_id の組み合わせはユニークであるべきだが、重複があった場合、1つだけ残して削除する
    $managers = App\Models\PaperManager::select('paper_id', 'user_id')
        ->groupBy('paper_id', 'user_id')
        ->havingRaw('COUNT(*) > 1')
        ->get();
    foreach ($managers as $manager) {
        $duplicates = App\Models\PaperManager::where('paper_id', $manager->paper_id)
            ->where('user_id', $manager->user_id)
            ->get();
        $first = true;
        foreach ($duplicates as $dup) {
            if ($first) {
                $first = false;
                continue;
            }
            $dup->delete();
        }
    }

@endphp
<!-- components.role.pc -->
@if (count($tasks) > 0)
    <div class="px-6 py-4">
        <x-element.h1>未完了のタスクがあります</x-element.h1>
        @foreach ($tasks as $task)
            <div class="mx-6">
                <x-task.panel :task="$task" />
            </div>
        @endforeach
    </div>
@endif

@if (count($approvetasks) > 0)
    <div class="px-6 py-4">
        <x-element.h1>未完了の承認タスクがあります</x-element.h1>
        @foreach ($approvetasks as $task)
            <div class="mx-6">
                <x-task.app_panel :task="$task" />
            </div>
        @endforeach
    </div>
@endif



<div class="px-6 py-4">


    <div>
        {{-- 投稿論文一覧 --}}
        <x-paper.psummarytable />
    </div>

    <div class="my-20"></div>


    <x-element.h1> <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('admin.paperlist') }}" color="lime">
            投稿情報の確認
        </x-element.linkbutton>
        <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('admin.deletepaper', ['cat' => 1]) }}" color="red">
            投稿の削除と復活
        </x-element.linkbutton>
        <span class="px-2"></span>

        <x-element.linkbutton href="{{ route('admin.catsetting', ['toukou' => 'on']) }}" color="cyan">
            投稿受付管理
        </x-element.linkbutton>
        <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('admin.catsetting', ['mandatoryfile' => 'on']) }}" color="lime">
            サプリメントファイル受付管理
        </x-element.linkbutton>
        <span class="px-2"></span>
        {{-- <x-element.linkbutton href="{{ route('admin.catsetting') }}" color="orange">
            査読進行管理
        </x-element.linkbutton>
        <span class="px-2"></span> --}}
        <x-element.linkbutton2 href="{{ route('admin.catsetting', ['leadtext' => 'on']) }}" color="gray">
            カテゴリ固有の案内(リード文など)
        </x-element.linkbutton2>
    </x-element.h1>

    <x-element.h1>メール送信
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('mt.index') }}" color="pink">
            メール雛形
        </x-element.linkbutton>
        <span class="px-3"></span>
        {{-- <span class="px-3">掲示板</span>
        <x-element.linkbutton href="{{ route('bb.index') }}" color="pink">
            掲示板一覧
        </x-element.linkbutton> --}}
        <span class="px-3">アンケート</span>
        <x-element.linkbutton href="{{ route('enq.index') }}" color="green">
            アンケート一覧
        </x-element.linkbutton>
    </x-element.h1>

    {{-- <x-element.h1>査読結果と判定 <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @php
                $btncolor = isset($cat_arrange_review[$catid]) ? 'purple' : 'gray';
            @endphp
            <x-element.linkbutton href="{{ route('review.result', ['cat' => $catid]) }}" color="{{ $btncolor }}"
                target="_blank">
                {{ $catname }}
            </x-element.linkbutton>
            <span class="mx-1"></span>
        @endforeach
    </x-element.h1>

    <x-element.h1>査読結果（コメント非表示・スコアのみ） <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('review.comment_scoreonly', ['cat' => $catid]) }}" color="purple"
                    target="_blank">
                    {{ $catname }}
                </x-element.linkbutton>
            @endisset
        @endforeach
        <span class="mx-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('review.comment_scoreonly', ['cat' => $catid, 'excel' => 'dl']) }}"
                    color="teal">
                    {{ $catname }}Excel
                </x-element.linkbutton>
            @endisset
        @endforeach
    </x-element.h1>

    <x-element.h1>査読結果＋コメント <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('review.comment', ['cat' => $catid]) }}" color="purple"
                    target="_blank">
                    {{ $catname }}
                </x-element.linkbutton>
            @endisset
        @endforeach
        <span class="mx-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('review.comment', ['cat' => $catid, 'excel' => 'dl']) }}"
                    color="teal">
                    {{ $catname }}Excel
                </x-element.linkbutton>
            @endisset
        @endforeach
    </x-element.h1>

    <x-element.h1>査読進捗 <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('revcon.revstatus') }}" color="orange" target="_blank">査読進捗
        </x-element.linkbutton>
    </x-element.h1> --}}

    {{-- <x-element.h1>査読者一覧と利害表明者 <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('revcon.revname', ['cat' => $catid]) }}" color="lime">
                    {{ $catname }}
                </x-element.linkbutton>
            @endisset
        @endforeach
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('revcon.revname', ['cat' => $catid, 'excel' => 'dl']) }}"
                    color="teal">
                    {{ $catname }} Excel
                </x-element.linkbutton>
            @endisset
        @endforeach
    </x-element.h1> --}}


    {{-- <x-element.h1>査読割り当て <span class="px-2"></span>
        @php
            $roles = App\Models\Role::where('name', 'like', '%reviewer')->get();
        @endphp
        @foreach ($roles as $role)
            @if ($role->users->count() > 1)
                @foreach ($cats as $catid => $catname)
                    @isset($cat_arrange_review[$catid])
                        <x-element.linkbutton href="{{ route('role.revassign', ['cat' => $catid, 'role' => $role]) }}"
                            color="lime">
                            {{ $catname }}→{{ $role->desc }}
                        </x-element.linkbutton>
                    @endisset
                @endforeach
            @endif
        @endforeach
        <span class="mx-3"></span>
        <x-element.linkbutton href="{{ route('revcon.index') }}" color="orange" target="_blank">
            Bidding未完了状態
        </x-element.linkbutton>
        <span class="mx-3"></span>
        <x-element.linkbutton href="{{ route('revcon.stat') }}" color="green" target="_blank">
            Bidding Stat
        </x-element.linkbutton>
        <span class="mx-3"></span>
        <x-element.linkbutton href="{{ route('revcon.revstat') }}" color="lime" target="_blank">
            査読割り当て Stat
        </x-element.linkbutton>


    </x-element.h1> --}}



    {{-- <x-element.h1>ファイルと書誌情報の保護
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('file.adminlock') }}" color="orange">
            投稿ファイルの管理
        </x-element.linkbutton> <span
            class="text-sm mx-2 mr-10">ファイルを修正ロック（査読中に使用）したり、ロック解除（査読結果通知前に使用）したりできる設定画面が開きます。</span>

        <x-element.linkbutton href="{{ route('paper.adminlock') }}" color="green">
            書誌情報(Paper)の管理
        </x-element.linkbutton> <span class="text-sm mx-2 mr-10">書誌情報（タイトル、著者名と所属、概要など）の編集権限をカテゴリ別に設定できる画面が開きます。</span>
    </x-element.h1> --}}


    <x-element.h1>査読観点(Viewpoint)の管理
        <span class="mx-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton
                    href="{{ route('viewpoint.itmsetting', ['cat_id' => $catid, 'cat_name' => $catname]) }}" color="yellow"
                    size="sm">
                    {{ $catname }}
                </x-element.linkbutton>

                {{-- <form class="inline" action="{{ route('admin.crud') }}?table=viewpoints" method="post"
                    id="admincrudwhere{{ $catid }}">
                    @csrf
                    @method('post')
                    <input id="whereby" type="hidden"
                        class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full" name="whereBy__category_id"
                        value={{ $catid }}>
                    <x-element.submitbutton color="yellow" size="sm">{{ $catname }}
                    </x-element.submitbutton>
                </form> --}}
                <span class="mx-2"></span>
            @endisset
        @endforeach
        <span class="text-sm mx-2 mr-10">編集画面をひらくとき、orderintを自動再調整します。</span>

        <br>
        プレビュー用査読フォーム
        <span class="mx-2"></span>
        @php
            $nameofmeta = App\Models\Setting::getval('NAME_OF_META');
        @endphp
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                @foreach (['一般', $nameofmeta, '幹事'] as $ismeta => $revtype)
                    <x-element.linkbutton2 href="{{ route('review.edit_dummy', ['cat' => $catid, 'target' => $ismeta]) }}"
                        color="blue" size="sm" target="_blank">
                        {{ $catname }}({{ $revtype }})
                    </x-element.linkbutton2>

                    {{-- <form class="inline" action="{{ route('admin.crud') }}?table=viewpoints" method="post"
                    id="admincrudwhere{{ $catid }}">
                    @csrf
                    @method('post')
                    <input id="whereby" type="hidden"
                        class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full" name="whereBy__category_id"
                        value={{ $catid }}>
                    <x-element.submitbutton color="yellow" size="sm">{{ $catname }}
                    </x-element.submitbutton>
                </form> --}}
                    <span class="mx-2"></span>
                @endforeach
            @endisset
        @endforeach
        {{-- <div class="my-2 px-6 py-2 dark:text-gray-300 bg-slate-300 text-sm">
            <x-element.linkbutton href="{{ route('viewpoint.export') }}" color="yellow">
                Viewpoint Download
            </x-element.linkbutton>
            でダウンロードしたExcelを修正して、<br>↓でアップロードしても変更できます。
            <form action="{{ route('viewpoint.import') }}" method="post" id="vpimport"
                enctype="multipart/form-data">
                @csrf
                @method('post')
                <input type="file" name="file" id="file">
                <div>
                    <input type="hidden" id="append" name="append" value="off">
                    <input type="checkbox" id="append" name="append" checked switch>
                    <label class="form-check-label" for="append">
                        アップロードした内容を追加する(一旦全削除してから追加する場合は、チェックを外す)
                    </label>
                </div>
                <x-element.submitbutton color="yellow">Viewpoint Upload
                </x-element.submitbutton>
            </form>
        </div> --}}
    </x-element.h1>

    <x-element.h1>自分の権限確認（Role一覧）
        <span class="mx-3"></span>
        @php
            $user = App\Models\User::find(auth()->id());
        @endphp
        @foreach ($user->roles as $ro)
            <span
                class="inline-block bg-slate-300 rounded-md p-1 mb-0.5 dark:bg-slate-500 dark:text-gray-300">{{ $ro->desc }}
                ({{ $ro->name }})
            </span>
        @endforeach
    </x-element.h1>

    {{-- <x-element.h1> <x-element.linkbutton href="{{ route('admin.hiroba_excel') }}" color="teal">
            情報学広場登録用Excel Download
        </x-element.linkbutton>
    </x-element.h1> --}}


</div>


@php
    // 担当の査読状況

    // もし担当したときの査読フォーム review.
@endphp
