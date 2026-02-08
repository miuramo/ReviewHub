<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            {{ __('新規投稿') }}
            <span class="mx-6"></span>
            <x-element.linkbutton
            href="https://scrapbox.io/reviewhub/%E6%8A%95%E7%A8%BF%E3%83%9E%E3%83%8B%E3%83%A5%E3%82%A2%E3%83%AB"
            color="lime" size="sm" target="_blank">
            {{ __('submit_manual')}} (Cosense/Scrapbox)</x-element.linkbutton>

        </h2>
    </x-slot>
    <!-- paper.create -->

    <div class="py-2">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif

        @if ($errors->any())
            <x-alert.error>入力エラーがあります。ご確認ください。</x-alert.error>
        @endif

        <div class="py-2 px-6">
            <form action="{{ route('paper.store') }}" method="post" id="newpaper">
                @csrf
                @method('post')

                <div class="m-6">
                    <x-element.h1>{{ __('以下の「投稿に関する事項」について確認し、すべてチェックをいれてください。') }}</x-element.h1>
                    <ul class="m-4">
                        @foreach ($kakunin as $name => $mes)
                            {{-- <input type="hidden" name="{{ $name }}" value="off"> --}}
                            <li><x-input-error class="mt-2 px-1" :messages="$errors->get($name)" />
                                <input type="checkbox" id="{{ $name }}" name="{{ $name }}"
                                    {{ old($name) == 'on' ? 'checked' : '' }} class="dark:bg-slate-300"> <label
                                    for="{{ $name }}"
                                    class="hover:bg-yellow-100 dark:text-slate-400 dark:hover:bg-yellow-950">{!! $mes !!}</label>
                            </li>
                        @endforeach
                    </ul>

                    <x-element.h1>
                        {{ __('「投稿連絡用メールアドレス」を入力してください。') }}
                        （<b>{{ __('1件は必須') }}</b>{{ __('、') }}{{ __('最大') }}{{ env('CONTACTEMAILS_MAX', 5) }}{{ __('件まで、1行に1件ずつ') }}   ）
                        <div class="text-sm mx-4 mt-2">
                            {{ __('投稿に関する連絡や通知は、投稿者アカウント（あなた）のメールアドレスと、ここで入力したメールアドレスに送信します。') }}<br>
                            {{ __('共著者の投稿者アカウントのメールアドレスが入力されている場合は、本投稿を共著者のアカウントと紐付けます。（共著者は紐付けられた投稿を「投稿一覧」で見ることができます。ただし共著者はファイルの差し替えや入力情報の修正はできません。）') }}<br>
                            <b>{{ __('あなたの第2メールアドレス等') }}</b>
                            {{ __('と、連絡・通知の受信を承諾された共著者のアドレス') }}
                            {{ __('を入力されることを推奨いたします。なお、投稿連絡用メールアドレスはいつでも（投稿締切後でも）追加・修正できます。') }}
                        </div>
                        {{-- <br>
                        連絡用メールアドレスへの送信失敗が繰り返される場合は、該当メールアドレスを削除します。 --}}
                    </x-element.h1>
                    @if (session('feedback.error'))
                        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
                    @endif
                    <div class="mb-3">
                        <label for="contact"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Email
                            addresses for notifications: Please enter one email address per line. At least one item is
                            required. The maximum is {{ env('CONTACTEMAILS_MAX', 5) }}.</label>
                        <x-input-error class="mx-2 mt-2 px-1" :messages="$errors->get('ema.0')" />
                        <x-input-error-md class="mx-2 mt-2 px-1" :messages="$errors->get('contactemails')" />
                        <textarea id="contact" name="contactemails" rows="5"
                            class="mx-2 block p-2.5 w-3/4 text-lg text-gray-900 bg-gray-50 rounded-lg  border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="your-secondary@email.com&#10;coauthor1@email.com&#10;coauthor2@email.com">{{ old('contactemails') }}</textarea>

                    </div>
                    <x-element.h1>
                        {{ __('以下の「投稿連絡用メールアドレスに関する事項」についても確認し、すべてチェックをいれてください。') }}
                    </x-element.h1>
                    <div class="mb-3 mx-2">
                        <ul class="m-4">
                            @foreach ($mailkakunin as $name => $mes)
                                <li><x-input-error class="mt-2 px-1" :messages="$errors->get($name)" />
                                    <input type="checkbox" id="{{ $name }}" name="{{ $name }}"
                                        {{ old($name) == 'on' ? 'checked' : '' }} class="dark:bg-slate-300"> <label
                                        for="{{ $name }}"
                                        class="hover:bg-lime-100 dark:text-slate-400 dark:hover:bg-lime-950">{{ $mes }}</label>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <x-element.button onclick="CheckAll('newpaper')" color="lime" value="{{ __('すべてチェック') }}">
                    </x-element.button>
                    &nbsp;
                    <x-element.button onclick="UnCheckAll('newpaper')" color="orange" value="{{ __('すべてチェック解除') }}">
                    </x-element.button>

                    @php
                        $cats = App\Models\Category::all();
                        $anyopen = false;
                        foreach ($cats as $c) {
                            if ($c->isOpen()) {
                                $anyopen = true;
                                break;
                            }
                        }
                        $anylimit = false;
                        foreach ($cats as $c) {
                            if ($c->isOpen() && $c->upperlimit > 0) {
                                $anylimit = true;
                                break;
                            }
                        }
                    @endphp

                    <x-element.h1>
                        @if ($anyopen)
                            {{ __('チェック事項を了解したうえで、') }}
                            @foreach ($cats as $c)
                                @if ($c->isOpen() && $c->isnotUpperLimit())
                                    <x-element.submitbutton value="{{ $c->id }}" color="{{ $c->color }}">
                                        {{ __("NewSubmitTo", ['catname' => $c->name]) }}
                                        {{-- {{ $c->name }}に新規投稿する --}}
                                    </x-element.submitbutton>
                                    &nbsp;
                                @endif
                            @endforeach
                        @else
                            （現在、投稿可能なカテゴリはありません）
                            <!-- PCのかたは、 投稿受付管理→ openstart, openend を確認してください。 -->
                        @endif
                    </x-element.h1>

                    @if ($anylimit)
                        <x-paper.upperlimit :cats=$cats></x-paper.upperlimit>
                    @endif

                </div>
            </form>
            {{-- @can('admin')
                <x-element.button onclick="debug_em()" color="white" value="debug">
                </x-element.button>
            @endcan --}}

        </div>

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

        function debug_em() {
            var textarea = document.getElementById('contact');
            textarea.value = "your-secondary@email.com\ncoauthor1@email.com\ncoauthor2@email.com";
        }
    </script>


</x-app-layout>
