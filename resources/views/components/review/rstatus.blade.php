@props([
    'review' => null,
    'readonly' => false,
])
@php
    // $review = App\Models\Review::find($review);
    $setumei = [
        'target' => '種別',
        'status' => '状況',
        'request_at' => '依頼日時',
        'start_at' => '開始日時',
        'end_at' => '終了日時',
        'will_end_at' => '予定終了日時',
    ];
@endphp

<!-- components.review.rstatus  -->
<table class="min-w divide-y divide-gray-200 inline-block align-top">
    <thead>
        <tr>
            <th class="p-1 bg-slate-300" colspan=2>
                <x-element.login_as :user="$review->user"></x-element.login_as>
                （{{ $review->user->affil }}）<sub>uid={{ $review->user->id }}</sub>
            </th>
            {{-- <th class="p-1 bg-slate-300"></th> --}}
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($review->heads() as $h => $hc)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white dark:bg-slate-400' }}">
                {{-- もし、hcに、が含まれていたら、分割して表示する。 --}}
                <td class="p-1 text-center">{{ $setumei[$h] }} → </td>
                @if (strpos($hc, '、') !== false)
                    @php
                        $hcs = explode('、', $hc);
                        $kv = [];
                        foreach ($hcs as $ichi_wa_hogehoge) {
                            $suuji_hogehoge = explode('は', $ichi_wa_hogehoge);
                            $kv[$suuji_hogehoge[0]] = $suuji_hogehoge[1];
                        }
                        $kv[-1] = '（辞退）';
                    @endphp
                    <td class="p-1 text-center">{{ $kv[$review->{$h}] }}</td>
                @else
                    @if ($h == 'end_at' && $review->end_at == null)
                        <td class="p-1 text-center text-red-400 dark:text-red-700 font-bold text-sm">
                            @php
                                $task = App\Models\Task::where('submit_id', $review->submit->id)
                                    ->where('subject_id', $review->user_id)
                                    ->first();
                            @endphp
                            @isset($task)
                                <livewire:task-duedate :task="$task" />
                            @endisset
                        </td>
                    @else
                        <td class="p-1 text-center">{{ $review->{$h} }}</td>
                    @endif
                @endif
            </tr>
        @endforeach
        <tr class="bg-slate-200">
            <td class="p-1 text-center" colspan=2>
                <div>
                    <x-task.tswitch :review="$review"></x-task.tswitch>
                </div>
                <x-bb.bb_link :submit="$review->submit" type="2" :rev_id="$review->id" size="sm"></x-bb.bb_link>
                @if (!$readonly)
                    <span class="mx-1"></span>
                    <x-element.deletebutton action="{{ route('review.destroy', ['review' => $review]) }}" color="orange"
                        size="sm" confirm="本当に{{ $review->user->name }}さんを査読担当から外してよいですか？（復元はできます）">
                        査読担当から外す
                    </x-element.deletebutton>
                @endif
                <div class="p-2">
                    <x-element.linkbutton href="{{ route('logac.show', ['review' => $review]) }}" color="gray"
                        size="xs" target="_blank">
                        査読活動ログ（別タブ）
                    </x-element.linkbutton>
                </div>
                <form class="inline" action="{{ route('admin.crud') }}?table=reviews" method="post" target="_blank"
                    id="admincrudwhereid{{ $review->id }}">
                    @csrf
                    @method('post')
                    <input id="whereby" type="hidden"
                        class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full" name="whereBy__id"
                        value={{ $review->id }}>
                    <x-element.submitbutton color="white" size="xs">編集({{ $review->id }})（別タブ）
                    </x-element.submitbutton>
                </form>

                <x-element.component_name>
                    rstatus {{ $review->id }}
                </x-element.component_name>

            </td>
        </tr>
    </tbody>
</table>

{{-- 査読完了をおすと、 completed = true (completed_at) になる。(approved_at も同時にセットされる)
開始は created_at で判断する。 --}}
{{-- require_apprive はつかっていない --}}
