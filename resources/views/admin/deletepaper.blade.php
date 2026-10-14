<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            {{-- <x-element.linkbutton href="{{ route('role.top', ['role' => 'ec']) }}" color="gray" size="sm">
                &larr; 編集長 Topに戻る
            </x-element.linkbutton> --}}
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('投稿の削除と復活') }}
        </h2>
    </x-slot>

    <div class="py-1">
    </div>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @if ($errors->any())
        <x-alert.error>
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </x-alert.error>
    @endif

    <form action="{{ route('admin.deletepaper') }}" method="post" id="admin_deletepaper">
        @csrf
        @method('post')
        <div class="mx-10 py-4">
            <x-admin.papertable_withfile :all="$all">
            </x-admin.papertable_withfile>

            <div class="my-2"></div>
            チェックを入れた投稿を
            <span class="mx-1"></span>
            <x-element.submitbutton value="revoke" color="cyan">復活する
            </x-element.submitbutton>
            <span class="mx-2"></span>
            <x-element.submitbutton value="delete" color="orange">論理削除する
            </x-element.submitbutton>
            @can('role_any', 'ec')
                <span class="mx-2"></span>
                <x-element.submitbutton value="force_delete" color="red"
                    confirm="論理削除済みの投稿と関連レコード・アップロードファイルを完全削除します。この操作は取り消せません。アクセスログは残ります。よろしいですか？">
                    選択した論理削除済み投稿を完全削除する
                </x-element.submitbutton>
            @endcan

        </div>
    </form>
    <div class="mx-10 py-4 bg-yellow-100 text-sm">
        【注】復活・論理削除のどちらを行っても、メールの送信はしません。完全削除は論理削除済みの投稿のみ対象で、関連レコードとアップロードファイルも削除されます。アクセスログは保持されます。
    </div>
    </x-app-layout>
