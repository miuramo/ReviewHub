@props([
    'paper' => null,
])

@php
    $authorlist = $paper->authorlist_ary();
@endphp
<!-- components.admin.change_paper_owner -->

<div class="mx-6 mt-2">
    <div class="container">
        <x-element.button class="" id="toggleButton" value="新規ユーザーを作成して投稿者を変更する画面の開閉 (ec|aec)" color='orange' size='sm'
            onclick="openclose('editpaperowner')">
        </x-element.button>
    </div>
    {{-- (2) 著者リストから新規ユーザーを作成して投稿者変更 --}}
    <div class="hidden-content mt-2  bg-orange-100 dark:bg-orange-900 dark:text-gray-300 rounded-lg p-2"
        id="editpaperowner" style="display:none;">
        <x-element.h1c color="orange">著者リストから新規ユーザーを作成して投稿者を変更

            <p class="text-sm mb-2">著者リストに登録された氏名・所属を使って新しいアカウントを作成し、この投稿の投稿者を変更します。<br>
                指定したメールアドレスが既存ユーザと重複している場合はエラーになります。</p>

            @if (count($authorlist) > 0)
                <form action="{{ route('paper.create_user_owner_ec', ['paper' => $paper->id]) }}" method="POST"
                    onsubmit="return confirm('著者リストの情報で新規ユーザーを作成し、投稿者を変更します。よろしいですか？')">
                    @csrf
                    <div class="form-group m-4">
                        <label for="author_index">著者リストから選択：</label>
                        <select name="author_index" id="author_index"
                            class="form-control dark:bg-gray-700 dark:text-white">
                            @foreach ($authorlist as $idx => $author)
                                <option value="{{ $idx }}">
                                    {{ $author[0] ?? '（名前なし）' }}
                                    @if (!empty($author[1]))
                                        （{{ $author[1] }}）
                                    @endif
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
                        新規ユーザーを作成して投稿者を変更する
                    </x-element.submitbutton>
                </form>
            @else
                <x-alert.warning>著者リストが空のため、この機能は使用できません。</x-alert.warning>
            @endif
        </x-element.h1c>
    </div>

</div>
