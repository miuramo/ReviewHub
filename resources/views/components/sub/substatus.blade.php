@props([
    'submit_id' => null,
    'readonly' => false,
])
@php
    $sub = App\Models\Submit::find($submit_id);
    $accepts = App\Models\Accept::pluck('name', 'id')->toArray();
    $statuses = App\Models\Status::pluck('name', 'id')->toArray();
@endphp
<!-- components.sub.status  親は -->
<div class="bg-pink-100 rounded-lg p-2 inline-block align-top dark:bg-pink-600">
    <p class="text-center">査読状況
        <x-element.component_name type="span">
            substatus
        </x-element.component_name>
    </p>
    <table class="min-w divide-y divide-gray-200 inline-block">
        <thead>
            <tr>
                <th class="p-1 bg-slate-300">ラウンド {{ $sub->round }}

                </th>
                <th class="p-1 bg-slate-300">
                    @if ($sub->paper->submits->first == $sub)
                        {{ $sub->paper->currentstatus->name }}
                    @else
                        {{ $accepts[$sub->accept_id] }}
                    @endif
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($sub->heads() as $h => $hc)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white dark:bg-slate-400' }}">
                    @if ($h == 'accept_id')
                        <td class="p-1 text-center">判定</td>
                        <td class="p-1 text-center">{{ $accepts[$sub->accept_id] }}</td>
                    @else
                        <td class="p-1 text-center">{{ $hc }}</td>
                        <td class="p-1 text-center">{{ $sub->{$h} ?? '--' }}</td>
                    @endif
                </tr>
            @endforeach
            <tr class="bg-slate-200">
                <td colspan=2 class="p-1 text-center">
                    @if (!$readonly)
                        {{-- 受領メールを送る --}}
                        @if (isset($sub->submitted_at))
                            @if ($sub->receiptsent_at == null && $sub->accept_id == 5)
                                <x-element.linkbutton href="{{ route('manage.sendreceipt', ['sub' => $sub->id]) }}"
                                    color="pink" size="sm"
                                    confirm="★★注意！！こちらは、受領した原稿に対して、査読をスタートする場合の受領通知です。「〜回目の査読に進みますので、しばらくお待ちください。」といった案内になります。★★（確認）あなたの名前で、受領通知を送ってよいですか？（著者との掲示板に書き込み、メール送信します）">受領通知（査読に進みます）を送る
                                </x-element.linkbutton> <br>
                            @else
                                <span class="text-pink-300">受領通知送信済み</span><br>
                            @endif
                        @endif
                        @if ($sub->ec_decision_at != null)
                            <x-element.linkbutton href="{{ route('manage.sendreceipt_final', ['sub' => $sub->id]) }}"
                                color="teal" size="sm"
                                confirm="★★注意！！こちらは、最終原稿を受領した場合の通知です。「最終原稿を受領いたしました。出版までしばらくお時間いただく場合がありますがご了承ください。」といった案内になります。★★（確認）あなたの名前で、受領通知を送ってよいですか？（著者との掲示板に書き込み、メール送信します）">最終原稿受領通知を送る
                            </x-element.linkbutton> <br>
                        @endif
                    @endif

                    @if (!$readonly && isset($sub->submitted_at) && count($sub->tasks) == 0)
                        <div>
                            <x-element.linkbutton href="{{ route('sub.gen_tasks', ['sub' => $sub->id]) }}"
                                color="yellow" size="xs" target="_self"
                                confirm="現在の査読ラウンドのタスクを生成します。よろしいですか？（あなたが発起人となります。）">
                                （高度）タスクを生成する
                            </x-element.linkbutton>
                        </div>
                    @endif

                    @if (count($sub->reviews) > 0)
                        <div>
                            <x-review.commentsubmit_link :sub="$sub" color="purple" label="査読結果をみる">
                            </x-review.commentsubmit_link>
                            <span class="mx-2"></span>
                            <x-element.linkbutton2
                                href="{{ route('paper.review', ['sub' => $sub->id, 'token' => $sub->paper->token()]) }}"
                                color="purple" target="_blank" size="sm">
                                著者がみる査読結果 </x-element.linkbutton2>
                            @php
                                $neutral_accept_id = \App\Models\Accept::where('name', '---')->first()?->id ?? 5;
                            @endphp
                            @if (!$readonly && $sub->accept_id != $neutral_accept_id)
                                <div>
                                    <x-sub.disclose :sub="$sub"></x-sub.disclose>

                                    <x-element.linkbutton
                                        href="{{ route('manage.senddisclose', ['sub' => $sub->id]) }}" color="pink"
                                        size="sm" confirm="査読結果開示通知を送ってよいですか？（著者との掲示板に書き込み、メール送信します）">査読結果開示通知を送る
                                    </x-element.linkbutton>
                                </div>
                                <span class="mx-2"></span>
                            @endif

                            @can('role', 'admin')
                                SubID: {{ $sub->id }}
                                <form class="inline" action="{{ route('admin.crud') }}?table=submits" method="post"
                                    target="_blank" id="admincrudwhereid{{ $sub->id }}">
                                    @csrf
                                    @method('post')
                                    <input id="whereby" type="hidden"
                                        class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full"
                                        name="whereBy__id" value={{ $sub->id }}>
                                    <x-element.submitbutton color="white" size="xs">編集(Sub{{ $sub->id }})（別タブ）
                                    </x-element.submitbutton>
                                </form>
                            @endcan
                        </div>
                    @endif

                </td>
            </tr>
        </tbody>
    </table>
</div>

@if (!$readonly)
    @if (count($sub->reviews) > 0)
        @foreach ($sub->reviews as $review)
            <x-review.rstatus :review="$review" :readonly="$readonly"></x-review.rstatus>
        @endforeach
    @else
        <div class="m-6 p-4 bg-yellow-200 inline-block align-top">
            <p class="text-center">査読者はまだ登録されていません。</p>
            @if ($sub->round > 1)
                <x-review.rassign_again :submit_id="$sub->id"></x-review.rassign_again>
            @endif
        </div>
    @endif
@endif

@if (!$readonly)
    {{-- 削除済み（辞退） --}}
    @if (count($sub->rejected_reviews()) > 0)
        <div class="m-2 p-2 bg-gray-200 inline-block align-top dark:bg-gray-500">
            <p class="text-center">担当外</p>
            @foreach ($sub->rejected_reviews() as $review)
                @if ($review->user == null)
                    {{-- 削除済みの査読者は表示しない --}}
                    @continue
                @endif

                <x-element.login_as :user="$review->user"></x-element.login_as>
                （{{ $review->user->affil }}）
                @if ($review->request_at)
                    <span class="text-blue-500">依頼済み</span>
                @endif
                @if ($review->status == -1)
                    <span class="text-red-500">辞退</span>
                @endif
                <x-element.linkbutton href="{{ route('review.restore', ['review' => $review->id]) }}" color="teal"
                    size="xs">
                    復活
                </x-element.linkbutton>
                <form class="inline" action="{{ route('admin.crud') }}?table=reviews" method="post" target="_blank"
                    id="admincrudwhereid{{ $review->id }}">
                    @csrf
                    @method('post')
                    <input id="whereby" type="hidden"
                        class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full" name="whereBy__id"
                        value={{ $review->id }}>
                    <x-element.submitbutton color="white" size="xs">編集({{ $review->id }})
                    </x-element.submitbutton>
                </form>
                <br>
            @endforeach
        </div>
    @endif
@endif
