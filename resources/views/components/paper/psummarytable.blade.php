@props([
    'all' => [],
    'heads' => ['カテゴリ', 'id', 'status', 'title', '投稿日時', '投稿者', '査-状況'],
    'enqans' => [],
])
<!-- components.paper.summarytable -->
@php
    // $papers = App\Models\Paper::get();
    $papers = App\Models\User::with('managed_papers')->find(auth()->id())->managed_papers;
    $papers = $papers->sortBy([
        ['status_id', 'asc'],
        ['submitted_at', 'desc'],
        // ['id', 'desc'],
    ]);

    $unmanaged = auth()->user()->unmanaged_papers();
    //     function ($paper) {
    // $papers = $papers->sortBy(function ($paper) {
    //     return ($paper->status_id ?? null);
    //     // return $paper->currentsubmit->submitted_at ?? null;
    // });
@endphp
<x-element.component_name>
    psummarytable
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
        @foreach ($papers as $paper)
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-400' : 'bg-white dark:bg-slate-300' }}">
                <td class="p-1 text-center">{{ $paper->category->name }}</td>
                <td class="p-1 text-center">
                    {{-- <a href="{{ route('paper.manage', ['paper' => $paper]) }}"
                        class="underline text-blue-600 hover:bg-lime-200" target="_blank"> --}}
                    {{ $paper->id_03d() }}
                    {{-- </a> --}}
                </td>
                <td class="p-1 text-center">
                    @php
                        $sub = $paper->currentsubmit;
                    @endphp
                    {{-- <a href="{{ route('sub.show', ['sub' => $sub]) }}" class="underline text-blue-600 hover:bg-lime-200"
                        target="_blank"> --}}
                    <a href="{{ route('paper.manage', ['paper' => $paper]) }}"
                        class="underline text-blue-600 hover:bg-lime-200">
                        {{ $paper->currentsubmit->round }}回目
                        {{ $paper->currentstatus->name }}
                    </a>
                </td>
                <td class="p-1 text-center block break-all">{{ $paper->title }}
                    @if ($paper->pdf_file_id != 0)
                        <a class="underline text-blue-600 hover:bg-lime-200"
                            href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                            target="_blank">
                            {{ $paper->pdf_file->pagenum }}page
                        </a>
                    @else
                        No PDF
                    @endif
                </td>

                <td class="p-1 text-center">
                    @if ($paper->currentsubmit->submitted_at)
                        {{ $paper->currentsubmit->submitted_at }}
                    @elseif($paper->currentsubmit->resubmit_until)
                        <span class="text-sm text-gray-500">{{ $paper->currentsubmit->resubmit_until }} 再投稿期限</span>
                    @else
                        ---
                    @endif
                </td>
                <td class="p-1 text-center">
                    <x-element.login_as :user="$paper->paperowner"></x-element.login_as> ({{ $paper->paperowner->affil }})
                </td>
                <td class="p-1 text-center leading-tight text-nowrap">
                    @foreach ($paper->currentsubmit->reviews as $review)
                        <div>
                            @php
                                $bb = App\Models\Bb::where('paper_id', $paper->id)
                                    ->where('type', 2)
                                    ->where('rev_id', $review->id)
                                    ->first();
                            @endphp
                            @if ($bb)
                                <a class="hover:underline text-green-600 hover:bg-lime-200" href="{{ $bb->url() }}"
                                    target="_blank">
                                    {{ substr($review->user->email, 0, 5) }}
                                </a>-
                            @else
                                {{ substr($review->user->email, 0, 5) }}-
                            @endif
                            {{ $review->status }}
                            @if ($review->status < 2)
                                <span class=" text-red-400 dark:text-red-700 font-bold text-sm">
                                    {{ $review->task->due_date ?? '' }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                    {{-- <x-element.login_as :user="$paper->currentsubmit->aecrep()->user" />
                        {{ $paper->currentsubmit->isAssigned('aec') ? '' : '?' }} --}}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@if ($unmanaged->count() > 0)
    <div class="mt-10 p-4 bg-yellow-100 rounded-md">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 pt-1 pb-3">
            {{ __('あなたが共著者・利害関係者となっている投稿論文（上の投稿論文一覧には含まれていません）') }}
        </h2>
        {{-- <p class="text-sm mb-4">あなたが担当していない投稿です。担当する場合は、投稿管理者に連絡してください。</p> --}}
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-400 sortable" id="psummarytable_unmanaged">
            <thead>
                <tr>
                    @foreach ($heads as $h)
                        <th class="p-1 bg-slate-300 dark:bg-slate-500           ">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-300">
                @foreach ($unmanaged as $paper)
                    <tr
                        class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-400' : 'bg-white dark:bg-slate-300' }}">
                        <td class="p-1 text-center">{{ $paper->category->name }}</td>
                        <td class="p-1 text-center">
                            {{-- <a href="{{ route('paper.manage', ['paper' => $paper]) }}"
                            class="underline text-blue-600 hover:bg-lime-200" target="_blank"> --}}
                            {{ $paper->id_03d() }}
                            {{-- </a> --}}
                        </td>
                        <td class="p-1 text-center">
                            @php
                                $sub = $paper->currentsubmit;
                            @endphp
                            {{-- <a href="{{ route('sub.show', ['sub' => $sub]) }}" class="underline text-blue-600 hover:bg-lime-200"
                            target="_blank"> --}}
                            {{ $paper->currentsubmit->round }}回目
                            {{ $paper->currentstatus->name }}
                        </td>
                        <td class="p-1 text-center block break-all">{{ $paper->title }}
                            @if ($paper->pdf_file_id != 0)
                                <a class="underline text-blue-600 hover:bg-lime-200"
                                    href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                                    target="_blank">
                                    {{ $paper->pdf_file->pagenum }}page
                                </a>
                            @else
                                No PDF
                            @endif
                        </td>
                        <td class="p-1 text-center">
                            @if ($paper->currentsubmit->submitted_at)
                                {{ $paper->currentsubmit->submitted_at }}
                            @elseif($paper->currentsubmit->resubmit_until)
                                <span class="text-sm text-gray-500">{{ $paper->currentsubmit->resubmit_until }}
                                    再投稿期限</span>
                            @else
                                ---
                            @endif
                        </td>
                        <td class="p-1 text-center">
                            <x-element.login_as :user="$paper->paperowner"></x-element.login_as>
                            ({{ $paper->paperowner->affil }})
                        </td>
                        <td class="p-1 text-center leading-tight text-nowrap">
                            (hidden)
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@push('localjs')
    <script src="/js/sortable.js"></script>
@endpush
