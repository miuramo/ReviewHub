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

</x-app-layout>
