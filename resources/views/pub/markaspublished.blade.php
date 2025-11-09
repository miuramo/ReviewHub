<x-app-layout>
    <!-- review.result -->
    @php
        $catspans = App\Models\Category::spans();
        $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $cats = App\Models\Category::manage_cats();
    @endphp
        @section('title', '発行・出版済みにする')

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            発行・出版済みにする
        </h2>
    </x-slot>

    <div class="px-6 py-4">

        <div class="mb-4 text-blue-600">
            発行・出版済みにした論文は、一括ダウンロードの対象から外れます。<br>
            発行・出版済みにしたい論文のチェックボックスを選択し、画面下部の「発行・出版済みにする」ボタンを押してください。<br>
            すでに発行・出版済みとしてマークされている論文のチェックを外すと、発行・出版済みのマークが解除されます。
        </div>
        <form method="POST" action="{{ route('pub.markaspublished') }}" id="publishedform">
            @csrf
            <table>
                <tr>
                    <th class="text-left px-2 py-1 border">論文ID</th>
                    <th class="text-left px-2 py-1 border">タイトル</th>
                    <th class="text-left px-2 py-1 border">Vol-XX</th>
                    <th class="text-left px-2 py-1 border">出版済み</th>
                </tr>
                @foreach ($subs as $sub)
                    <tr>
                        <td class="px-2 py-1 border text-center">{{ $sub->paper_id }}</td>
                        <td class="px-2 py-1 border">{{ $sub->paper->title }}</td>
                        <td class="px-2 py-1 border text-center">{{ $sub->booth }}</td>
                        <td class="px-2 py-1 border text-center">
                            <input type="hidden" name="all_ids[]" value="{{ $sub->paper_id }}">
                            <input type="checkbox" name="published_ids[]" value="{{ $sub->paper_id }}"
                                {{ $sub->paper->published ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
            </table>

            <div class="flex items-center justify-end mt-4">
                <x-element.button onclick="UnCheckAll('publishedform')" color="orange" value="すべてチェック解除" size="sm">
                </x-element.button>
                <span class="mx-2"></span>
                <x-element.button onclick="CheckAll('publishedform')" color="lime" value="すべてチェック" size="sm">
                </x-element.button>
                <span class="mx-2"></span>
                <x-element.submitbutton color="blue" value="markaspublished">
                    {{ __('発行・出版済みにする') }}
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    <script>
        function CheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = true;
                }
            }
        }

        function UnCheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = false;
                }
            }
        }
    </script>
</x-app-layout>