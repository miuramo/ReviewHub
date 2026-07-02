@props([
    'all' => [],
    'heads' => ['id', '種別', 'status', 'title / author', '投稿日時', '投稿者', '査-状況'],
    'size' => 'md',
])
<!-- components.paper.psummarytable2 -->
@php
    $status_labels = [
        -1 => '<span class="text-gray-400">辞退</span>',
        0 => '<span class="text-orange-500">未了解</span>',
        1 => '<span class="text-green-600">査読中</span>',
        2 => '<span class="text-blue-300">完了</span>',
    ];
    if ($size === 'sm') {
        $size_s = 'xs';
    } elseif ($size === 'md') {
        $size_s = 'sm';
    }

    $show_aec_name = App\Models\Setting::isTrue('show_aec_name');
    if ($show_aec_name) {
        $heads = ['担当幹事', 'id', '種別', 'status', 'title / author', '投稿日時', '投稿者', '査-状況'];
    }
    if (!function_exists('fiscal_year')) {
        function fiscal_year(): int
        {
            $month = date('n');
            $year = date('Y');
            return $month >= 4 ? $year : $year - 1;
        }
    }
    // 役職者（編集委員メンバー） の名前リスト
    $terms = \App\Models\Term::with('user', 'post')->where('year', fiscal_year())->get();
    $editor_names = $terms
        ->map(function ($term) {
            return $term->user->name;
        })
        ->toArray();
    $editor_hash = array_flip($editor_names);
@endphp
<x-element.component_name>
    psummarytable2
</x-element.component_name>
<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-400 sortable" id="psummarytable">
    <thead>
        <tr>
            @foreach ($heads as $h)
                <th class="p-1 bg-slate-300 dark:bg-slate-500 text-{{ $size }}">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-300">
        @foreach ($all as $paper)
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-400' : 'bg-white dark:bg-slate-300' }}">
                @if ($show_aec_name)
                    <td class="p-1 text-center text-{{ $size }}">
                        {{ $paper->aec ? $paper->aec->name : '未設定' }}
                    </td>
                @endif
                <td class="p-1 text-center text-{{ $size }}">
                    {{ $paper->id_03d() }}
                </td>
                <td class="p-1 text-center text-{{ $size }}">
                    {{-- {{ $paper->category->name }} --}}
                    <x-element.category :cat="$paper->category_id" size="xs" />
                </td>
                <td class="p-1 text-center text-{{ $size }}">
                    @if (auth()->user()->can('manage_review', $paper->id))
                        @php
                            $sub = $paper->currentsubmit;
                        @endphp
                        <a href="{{ route('paper.manage', ['paper' => $paper]) }}"
                            class="underline text-blue-600 hover:bg-lime-200">
                            {{ $paper->currentsubmit->round }}回目
                            {{ $paper->currentstatus->name }}
                        </a>
                    @else
                        {{-- 管理できないので、表示だけ --}}
                        {{ $paper->currentsubmit->round }}回目
                        {{ $paper->currentstatus->name }}
                    @endif
                    @if ($paper->currentsubmit->booth)
                        <span class="mx-1"></span>
                        <span class="text-sm font-bold text-blue-600">{{ $paper->currentsubmit->booth }}</span>
                    @endif
                </td>


                <td class="p-1 text-center break-all text-{{ $size }}">
                    {{ $paper->title }}
                    @if ($paper->pdf_file_id != 0)
                        <a class="underline text-blue-600 hover:bg-lime-200"
                            href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                            target="_blank">
                            {{ $paper->pdf_file->pagenum }}page
                        </a>
                    @else
                        No PDF
                    @endif
                    {{-- 著者名リスト --}}
                    <div class="w-full text-xs">

                        @if (strlen($paper->authorlist) > 10)
                            @foreach (explode("\n", $paper->authorlist) as $author)
                                @php
                                    $aname = trim(explode('(', $author)[0]);
                                @endphp
                                @if (isset($editor_hash) && isset($editor_hash[$aname]))
                                    <span
                                        class="text-left mr-2 font-bold text-red-600 bg-yellow-200 rounded px-1 py-0.5">{{ $author }}
                                    </span>
                                @else
                                    <span class="text-left mr-2 text-gray-500">{{ $author }}</span>
                                @endif
                            @endforeach
                        @else
                            <span class="text-gray-400">{{ $paper->paperowner->name }}
                                （{{ $paper->paperowner->affil }}）が投稿申請中</span>
                        @endif
                    </div>
                </td>

                <td class="p-1 text-center text-{{ $size }}">
                    @if ($paper->currentsubmit->submitted_at)
                        {{ $paper->currentsubmit->submitted_at }}
                    @elseif($paper->currentsubmit->resubmit_until)
                        <span class="text-sm text-gray-500">{{ $paper->currentsubmit->resubmit_until }} 再投稿期限</span>
                    @else
                        ---
                    @endif
                </td>
                <td class="p-1 text-center text-{{ $size }}">
                    @if (auth()->user()->can('manage_review', $paper->id))
                        <x-element.login_as :user="$paper->paperowner"></x-element.login_as> ({{ $paper->paperowner?->affil }})
                    @else
                        (hidden)
                    @endif
                </td>
                <td class="p-1 text-center leading-tight text-nowrap text-{{ $size_s }}">
                    {{-- 査読者と状況結果を表示する。ただし、閲覧者が管理権限がある場合のみ --}}
                    @if (auth()->user()->can('manage_review', $paper->id))
                        @foreach ($paper->currentsubmit->reviews as $review)
                            <div>
                                @php
                                    $bb = App\Models\Bb::where('paper_id', $paper->id)
                                        ->where('type', 2)
                                        ->where('rev_id', $review->id)
                                        ->first();
                                @endphp
                                @if ($bb)
                                    <a class="hover:underline text-green-600 hover:bg-lime-200"
                                        href="{{ $bb->url() }}" target="_blank">
                                        {{ substr($review->user->email, 0, 5) }}
                                    </a>-
                                @else
                                    {{ substr($review->user->email, 0, 5) }}-
                                @endif
                                {!! $status_labels[$review->status] !!}
                                @if ($review->status < 2)
                                    <span class=" text-red-400 dark:text-red-700 font-bold text-sm">
                                        {{ $review->task->due_date ?? '' }}
                                    </span>
                                @else
                                    {{ $review->judge() ?? '' }}
                                @endif
                            </div>
                        @endforeach
                        <x-element.linkbutton2 href="{{ route('paper.bb_summary', ['paper' => $paper->id]) }}"
                            color="green" size="xs" target="_blank">
                            やりとり一覧
                        </x-element.linkbutton2>
                    @else
                        (hidden)
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- components.paper.psummarytable2 end -->
