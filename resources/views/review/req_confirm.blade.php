<x-app-layout>
    <!-- review.req_confirm -->
    @section('title', '以下の査読をお願いします')

    @php
        $name_of_managers = \App\Models\Setting::getValue("NAME_OF_MANAGERS");

        $paper = $review->submit->paper;
        $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('査読の承諾確認') }} &nbsp;

            <x-element.paperid size=2 :paper_id="$paper->id"></x-element.paperid>
        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="py-2 px-6">
        <div class="text-lg py-4">
            {{ $conf->value }} に投稿された以下の論文につきまして、{{ $review->user->name }} さまにぜひ査読をお願いしたいと考えております。<br>
            お引き受けいただける場合は、「承諾する」を押してください。<br>
        </div>
        <div class="px-4 pb-6 text-md text-blue-600">※「承諾する」または「今回は辞退する」を押すと、確認画面がでます。<br>
            確認画面でOKを押すと、以下に入力された連絡事項とともに、{{ $name_of_managers }}に通知されます。</div>
        <form action="{{ route('review.req_confirm_post', ['review' => $review, 'token' => $token]) }}" method="post"
            id="req_confirm_form">
            @csrf
            @method('post')

            <textarea name="comment" id="comment" rows="4" class="w-full p-2 border border-gray-300 rounded-md"
                placeholder="査読期間への要望や辞退の理由など、{{ $name_of_managers }}への連絡事項がありましたら、ここに入力してください。なお、標準的な査読期間は24日間となっております。"></textarea>
            <x-element.submitbutton value="accept" color="cyan" confirm="「承諾する」で送信して、よろしいですか？">承諾する (Accept)
            </x-element.submitbutton>
            <span class="mx-4"></span>
            <x-element.submitbutton value="deny" color="gray" confirm="「今回は辞退する」で送信して、よろしいですか？">今回は辞退する (Deny)
            </x-element.submitbutton>

        </form>
    </div>

    <div class="py-2 px-6">
        <x-paper.shoshi_list :paper="$paper">
        </x-paper.shoshi_list>
    </div>

</x-app-layout>
