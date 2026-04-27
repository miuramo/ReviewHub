@props([
    'all' => [],
    'heads' => ['id', '種別', 'status', 'title', '投稿日時', '投稿者', '査-状況'],
    // 'heads' => ['id', 'status', 'title', '投稿日時', '投稿者', '査-状況'],
    'size' => 'md',
])
<!-- components.paper.summarytable -->
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

@endphp
<x-element.component_name>
    psummarytable2
</x-element.component_name>
<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-400 sortable" id="psummarytable">
    <thead>
        <tr>
            @foreach ($heads as $h)
                <th class="p-1 bg-slate-300 dark:bg-slate-500">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-300">
        @foreach ($all as $paper)
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-400' : 'bg-white dark:bg-slate-300' }}">
                <td class="p-1 text-center text-{{ $size }}">
                    {{ $paper->id_03d() }}
                </td>
                <td class="p-1 text-center text-{{ $size }}">
                    {{ $paper->category->name }}
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
                    @if($paper->currentsubmit->booth)
                        <span class="mx-1"></span>
                        <span class="text-sm font-bold text-blue-600">{{$paper->currentsubmit->booth}}</span>
                    @endif
                </td>
                <td class="p-1 text-center block break-all text-{{ $size }}">{{ $paper->title }}
                    @if (auth()->user()->can('manage_review', $paper->id))
                        @if ($paper->pdf_file_id != 0)
                            <a class="underline text-blue-600 hover:bg-lime-200"
                                href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                                target="_blank">
                                {{ $paper->pdf_file->pagenum }}page
                            </a>
                        @else
                            No PDF
                        @endif
                    @else
                            <span class="mx-4"></span>
                        {{-- 管理できないので、表示だけ --}}
                        @if ($paper->pdf_file_id != 0)
                            {{ $paper->pdf_file->pagenum }}page
                        @else
                            No PDF
                        @endif
                    @endif
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
                        <x-element.login_as :user="$paper->paperowner"></x-element.login_as> ({{ $paper->paperowner->affil }})
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
                    @else
                        (hidden)
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- components.paper.psummarytable2 end -->
