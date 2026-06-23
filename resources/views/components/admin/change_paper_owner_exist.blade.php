@props([
    'paper' => null,
])

@php
    $authorlist = $paper->authorlist_ary();
@endphp
<!-- components.admin.change_paper_owner -->

<div class="mx-6 mt-2">
    <div class="container">
        <x-element.button class="" id="toggleButton" value="既存ユーザーを検索して投稿者を変更する画面の開閉 (admin)" color='purple' size='sm'
            onclick="openclose('editpaperownerexist')">
        </x-element.button>
    </div>
    {{-- (2) 既存ユーザーを検索して投稿者変更 --}}
    <div class="hidden-content mt-2  bg-purple-100 dark:bg-purple-900 dark:text-gray-300 rounded-lg p-2"
        id="editpaperownerexist" style="display:none;">
        <x-element.h1c color="purple">既存ユーザーを検索してオーナーを変更

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

            <form id="admin_assign_owner_form" action="{{ route('paper.assign_owner_admin', ['paper' => $paper->id]) }}"
                method="POST" onsubmit="return confirm('選択したユーザーにオーナーを変更します。よろしいですか？')">
                @csrf
                <input type="hidden" name="user_id" id="admin_selected_user_id">
                <div id="admin_selected_user_info" class="m-4 p-3 bg-purple-50 dark:bg-purple-800 rounded hidden">
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
                (function() {
                    const searchInput = document.getElementById('admin_user_search');
                    const resultsBox = document.getElementById('admin_user_search_results');
                    const selectedId = document.getElementById('admin_selected_user_id');
                    const selectedInfo = document.getElementById('admin_selected_user_info');
                    const selectedLbl = document.getElementById('admin_selected_user_label');
                    const submitBtn = document.getElementById('admin_assign_submit');
                    const searchUrl = '{{ route('paper.user_search_admin', ['paper' => $paper->id]) }}';
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                    let debounceTimer = null;

                    // 初期状態: submit 無効
                    submitBtn.disabled = true;

                    searchInput.addEventListener('input', function() {
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
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
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
                            item.className =
                                'w-full text-left px-3 py-2 text-sm hover:bg-purple-100 dark:hover:bg-purple-700 border-b last:border-b-0';
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
                    document.addEventListener('click', function(e) {
                        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
                            resultsBox.classList.add('hidden');
                        }
                    });
                })();
            </script>

        </x-element.h1c>
    </div>

</div>

</div>
