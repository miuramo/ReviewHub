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
    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="gray" size="lg">
            &larr; 投稿{{ $paper->id_03d() }} に戻る
        </x-element.linkbutton>
    </div>

    <div class="py-2">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif

        <div class="container bg-white dark:bg-slate-800 rounded-lg m-6 p-6">
            投稿連絡用メールアドレスに設定されているアドレスを持つアカウントのユーザに、この投稿の「投稿者」を変更（委譲）することができます。<br>
            「投稿者」を変更（委譲）すると、あなたはこの投稿の管理ができなくなります。<br>

            @if (count($coauthors) === 0)
                <x-alert.warning>この投稿の投稿連絡用メールアドレスに設定されたアカウントは、現在この投稿の共著者リストに含まれていません。<br>
                    そのため、この投稿の「投稿者」を変更（委譲）できるユーザが存在しません。<br>
                    「投稿者」を変更（委譲）するには、まずこの投稿の共著者リストに、投稿連絡用メールアドレスに設定されたアカウントを追加してください。</x-alert.warning>
                <div class="hidden-content mt-2 bg-yellow-200 dark:bg-cyan-600 p-2" id="editcontact">
                    <x-paper.contactemail :paper="$paper">
                    </x-paper.contactemail>
                </div>
            @else
                <x-element.h1c color="orange">この投稿の「投稿者」を変更（委譲）する</x-element.h1c>

                <form action="{{ route('paper.change_ownerpost', ['paper' => $paper->id]) }}" method="POST">
                    @csrf
                    <div class="form-group m-4">
                        <label for="new_owner">委譲先のユーザを選択:</label>
                        <select name="new_owner" id="new_owner" class="form-control">
                            @foreach ($coauthors as $coauthor)
                                <option value="{{ $coauthor->id }}">{{ $coauthor->name }}（{{ $coauthor->affil }}）
                                    {{ $coauthor->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <x-element.submitbutton color="orange" size="md" confirm="本当にこの投稿の「投稿者」を変更（委譲）しますか？">
                         この投稿の「投稿者」を変更（委譲）する
                    </x-element.submitbutton>
                </form>
            @endif
        </div>
    </div>
    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="gray" size="lg">
            &larr; 投稿{{ $paper->id_03d() }} に戻る
        </x-element.linkbutton>
    </div>

</x-app-layout>
