<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            {{ __('「投稿者」の変更') }}
            <span class="mx-6"></span>
            <x-element.linkbutton
                href="https://scrapbox.io/reviewhub/%E6%8A%95%E7%A8%BF%E3%83%9E%E3%83%8B%E3%83%A5%E3%82%A2%E3%83%AB"
                color="lime" size="sm" target="_blank">
                {{ __('submit_manual') }} (Cosense/Scrapbox)</x-element.linkbutton>
        </h2>
    </x-slot>
    <!-- paper.change_owner -->
    <div class="mt-4 px-6 pb-0">
        <x-element.linkbutton href="javascript:history.back()" color="gray" size="lg">
            &larr; 投稿{{ $paper->id_03d() }} に戻る
        </x-element.linkbutton>
    </div>

    <div class="py-2">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif
        @if (session('feedback.error'))
            <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
        @endif

        <div class="container bg-yellow-200 dark:bg-yellow-800 dark:text-gray-300 rounded-lg m-6 p-6">
            「投稿連絡用メールアドレス」に設定されているアカウントのユーザに、この投稿の「投稿者」を変更（管理を委譲）することができます。<br>
            <b>「投稿者」を変更すると、あなたはこの投稿の管理ができなくなります。</b><br>
            <b>変更後も共著者として投稿情報を参照するには、自分のアカウントのメールアドレスを「投稿連絡用メールアドレス」に追加してから変更してください。</b><br>
            <span
                class="text-sm text-red-600 dark:text-red-400">（委譲後に、あなたが自分のアカウントのメールアドレスを変更すると、共著者としての参照権限が失われます。その場合は、委譲したユーザに連絡して「投稿連絡用メールアドレス」を修正してもらってください。）</span>

            <div class="hidden-content mt-2 bg-yellow-200 dark:bg-cyan-600 p-2" id="editcontact">
                <livewire:contact-email-editor :paper="$paper" />
            </div>
            @if (count($coauthors) === 0)
                <x-alert.warning>この投稿の「投稿連絡用メールアドレス」に設定されているアドレスを持つアカウントがみつかりません。<br>
                    そのため、この投稿の「投稿者」を変更（委譲）できるユーザが存在しません。<br>
                    「投稿者」を変更（委譲）するには、まずこの投稿の「投稿連絡用メールアドレス」に、ほかのユーザのメールアドレスを設定してください。
                    そのあと、
                    <x-element.button onclick="location.reload()" color="cyan" size="md"
                        value="このページを再読み込み"></x-element.button>
                    してください。
                </x-alert.warning>
            @else
                <x-element.button onclick="location.reload()" color="teal" size="md"
                    value="このページを再読み込み"></x-element.button>

                <x-element.h1c color="yellow">この投稿の「投稿者」を変更（委譲）する

                    <form action="{{ route('paper.change_ownerpost', ['paper' => $paper->id]) }}" method="POST">
                        @csrf
                        <div class="form-group m-4">
                            <label for="new_owner">委譲先のユーザを選択：</label>
                            <select name="new_owner" id="new_owner"
                                class="form-control dark:bg-gray-700 dark:text-white">
                                @foreach ($coauthors as $coauthor)
                                    <option value="{{ $coauthor->id }}">{{ $coauthor->name }}（{{ $coauthor->affil }}）
                                        {{ $coauthor->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-element.submitbutton color="red" size="md" confirm="本当にこの投稿の「投稿者」を変更（委譲）しますか？">
                            この投稿の「投稿者」を変更（委譲）する
                        </x-element.submitbutton>
                    </form>
                </x-element.h1c>
            @endif
        </div>
    </div>
    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="javascript:history.back()" color="gray" size="lg">
            &larr; 投稿{{ $paper->id_03d() }} に戻る
        </x-element.linkbutton>
    </div>

    {{-- EC/AEC専用操作 --}}
    @can('role_any', 'ec|aec')
    <div class="px-6 pb-10 space-y-6">

        {{-- (1) paper.id の変更 --}}
        <div class="container bg-red-100 dark:bg-red-900 dark:text-gray-300 rounded-lg p-6">
            <x-element.h1c color="red">【EC/AEC専用】投稿IDの変更

                @php
                    $relatedCounts = [
                        'files'           => \App\Models\File::where('paper_id', $paper->id)->count(),
                        'submits'         => \Illuminate\Support\Facades\DB::table('submits')->where('paper_id', $paper->id)->count(),
                        'bbs'             => \Illuminate\Support\Facades\DB::table('bbs')->where('paper_id', $paper->id)->count(),
                        'reviews'         => \Illuminate\Support\Facades\DB::table('reviews')->where('paper_id', $paper->id)->count(),
                        'rev_conflicts'   => \Illuminate\Support\Facades\DB::table('rev_conflicts')->where('paper_id', $paper->id)->count(),
                        'paper_contact'   => \Illuminate\Support\Facades\DB::table('paper_contact')->where('paper_id', $paper->id)->count(),
                        'enquete_answers' => \Illuminate\Support\Facades\DB::table('enquete_answers')->where('paper_id', $paper->id)->count(),
                        'paper_manager'   => \Illuminate\Support\Facades\DB::table('paper_manager')->where('paper_id', $paper->id)->count(),
                    ];
                @endphp

                <p class="text-sm mb-2">投稿IDを変更すると、関連するすべてのテーブルも同時に更新されます。<br>
                    <b>この操作は元に戻せません。必ず確認してから実行してください。</b></p>

                <div class="text-sm mb-3 bg-white dark:bg-gray-800 rounded p-3">
                    <p class="font-semibold mb-1">現在の投稿ID: <span class="text-red-600 dark:text-red-400">{{ $paper->id }}</span>（表示: {{ $paper->id_03d() }}）</p>
                    <p class="font-semibold mb-1">関連レコード件数（変更時に同時更新されます）：</p>
                    <ul class="list-disc ml-5">
                        @foreach ($relatedCounts as $tbl => $cnt)
                            <li>{{ $tbl }}: {{ $cnt }} 件</li>
                        @endforeach
                    </ul>
                </div>

                <form action="{{ route('paper.change_paper_id', ['paper' => $paper->id]) }}" method="POST"
                    onsubmit="return confirm('本当に投稿IDを変更しますか？この操作は元に戻せません。')">
                    @csrf
                    <div class="form-group m-4">
                        <label for="new_paper_id">新しい投稿ID（整数）：</label>
                        <input type="number" name="new_paper_id" id="new_paper_id" min="1" required
                            class="form-control dark:bg-gray-700 dark:text-white border rounded px-2 py-1 w-32"
                            placeholder="{{ $paper->id }}">
                    </div>
                    <x-element.submitbutton color="red" size="md">
                        投稿IDを変更する
                    </x-element.submitbutton>
                </form>
            </x-element.h1c>
        </div>

        {{-- (2) 著者リストから新規ユーザーを作成してオーナー変更 --}}
        <div class="container bg-orange-100 dark:bg-orange-900 dark:text-gray-300 rounded-lg p-6">
            <x-element.h1c color="orange">【EC/AEC専用】著者リストから新規ユーザーを作成してオーナーを変更

                <p class="text-sm mb-2">著者リストに登録された氏名・所属を使って新しいアカウントを作成し、この投稿の投稿者（オーナー）を変更します。<br>
                    指定したメールアドレスが既存ユーザと重複している場合はエラーになります。</p>

                @if (count($authorlist) > 0)
                    <form action="{{ route('paper.create_user_owner_ec', ['paper' => $paper->id]) }}" method="POST"
                        onsubmit="return confirm('著者リストの情報で新規ユーザーを作成し、オーナーを変更します。よろしいですか？')">
                        @csrf
                        <div class="form-group m-4">
                            <label for="author_index">著者リストから選択：</label>
                            <select name="author_index" id="author_index"
                                class="form-control dark:bg-gray-700 dark:text-white">
                                @foreach ($authorlist as $idx => $author)
                                    <option value="{{ $idx }}">
                                        {{ $author[0] ?? '（名前なし）' }}
                                        @if (!empty($author[1]))（{{ $author[1] }}）@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group m-4">
                            <label for="new_user_email">新規ユーザーのメールアドレス：</label>
                            <input type="email" name="new_user_email" id="new_user_email" required
                                class="form-control dark:bg-gray-700 dark:text-white border rounded px-2 py-1 w-80"
                                placeholder="example@domain.com">
                        </div>
                        <x-element.submitbutton color="orange" size="md">
                            新規ユーザーを作成してオーナーを変更する
                        </x-element.submitbutton>
                    </form>
                @else
                    <x-alert.warning>著者リストが空のため、この機能は使用できません。</x-alert.warning>
                @endif
            </x-element.h1c>
        </div>

    </div>
    @endcan

    {{-- Admin専用: 既存ユーザー検索でオーナー変更 --}}
    @can('admin')
    <div class="px-6 pb-10">
        <div class="container bg-purple-100 dark:bg-purple-900 dark:text-gray-300 rounded-lg p-6">
            <x-element.h1c color="purple">【Admin専用】既存ユーザーを検索してオーナーを変更

                <p class="text-sm mb-2">キーワードで既存ユーザーを検索し、選択した上でこの投稿の投稿者（オーナー）を変更します。</p>

                <div class="m-4">
                    <label for="admin_user_search" class="block mb-1">ユーザー検索（氏名・所属・メールアドレス）：</label>
                    <input type="text" id="admin_user_search" autocomplete="off"
                        class="form-control dark:bg-gray-700 dark:text-white border rounded px-2 py-1 w-full max-w-lg"
                        placeholder="氏名・所属・メールアドレスの一部を入力...">
                    <div id="admin_user_search_results"
                        class="mt-2 border rounded bg-white dark:bg-gray-800 max-h-60 overflow-y-auto hidden">
                    </div>
                </div>

                <form id="admin_assign_owner_form"
                    action="{{ route('paper.assign_owner_admin', ['paper' => $paper->id]) }}" method="POST"
                    onsubmit="return confirm('選択したユーザーにオーナーを変更します。よろしいですか？')">
                    @csrf
                    <input type="hidden" name="user_id" id="admin_selected_user_id">
                    <div id="admin_selected_user_info"
                        class="m-4 p-3 bg-purple-50 dark:bg-purple-800 rounded hidden">
                        <p class="text-sm font-semibold">選択中のユーザー：</p>
                        <p id="admin_selected_user_label" class="text-sm"></p>
                    </div>
                    <div class="m-4">
                        <x-element.submitbutton color="purple" size="md" id="admin_assign_submit">
                            このユーザーにオーナーを変更する
                        </x-element.submitbutton>
                    </div>
                </form>

                <script>
                (function () {
                    const searchInput  = document.getElementById('admin_user_search');
                    const resultsBox   = document.getElementById('admin_user_search_results');
                    const selectedId   = document.getElementById('admin_selected_user_id');
                    const selectedInfo = document.getElementById('admin_selected_user_info');
                    const selectedLbl  = document.getElementById('admin_selected_user_label');
                    const submitBtn    = document.getElementById('admin_assign_submit');
                    const searchUrl    = '{{ route('paper.user_search_admin', ['paper' => $paper->id]) }}';
                    const csrfToken    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                    let debounceTimer = null;

                    // 初期状態: submit 無効
                    submitBtn.disabled = true;

                    searchInput.addEventListener('input', function () {
                        clearTimeout(debounceTimer);
                        const q = this.value.trim();
                        if (q.length < 1) {
                            resultsBox.classList.add('hidden');
                            resultsBox.innerHTML = '';
                            return;
                        }
                        debounceTimer = setTimeout(() => fetchUsers(q), 300);
                    });

                    function fetchUsers(q) {
                        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                        })
                        .then(r => r.json())
                        .then(users => renderResults(users))
                        .catch(() => {});
                    }

                    function renderResults(users) {
                        resultsBox.innerHTML = '';
                        if (users.length === 0) {
                            resultsBox.innerHTML = '<p class="text-sm px-3 py-2 text-gray-500">該当するユーザーが見つかりません</p>';
                            resultsBox.classList.remove('hidden');
                            return;
                        }
                        users.forEach(u => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'w-full text-left px-3 py-2 text-sm hover:bg-purple-100 dark:hover:bg-purple-700 border-b last:border-b-0';
                            item.textContent = `[${u.id}] ${u.name}（${u.affil ?? ''}） ${u.email}`;
                            item.addEventListener('click', () => selectUser(u));
                            resultsBox.appendChild(item);
                        });
                        resultsBox.classList.remove('hidden');
                    }

                    function selectUser(u) {
                        selectedId.value = u.id;
                        selectedLbl.textContent = `[ID: ${u.id}] ${u.name}（${u.affil ?? ''}） ${u.email}`;
                        selectedInfo.classList.remove('hidden');
                        resultsBox.classList.add('hidden');
                        searchInput.value = `[${u.id}] ${u.name} ${u.email}`;
                        submitBtn.disabled = false;
                    }

                    // 検索ボックス外クリックで候補を閉じる
                    document.addEventListener('click', function (e) {
                        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
                            resultsBox.classList.add('hidden');
                        }
                    });
                })();
                </script>

            </x-element.h1c>
        </div>
    </div>
    @endcan

</x-app-layout>
