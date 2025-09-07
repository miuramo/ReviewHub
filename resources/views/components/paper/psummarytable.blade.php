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
        ['status_id','asc'],
        ['submitted_at','desc'],
        // ['id', 'desc'],
    ]);
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
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-400' : 'bg-white dark:bg-slate-300' }}">
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
                    @if($paper->currentsubmit->submitted_at)
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
                <td class="p-1 text-center">
                    @foreach ($paper->currentsubmit->reviews as $review)
                        <div>
                            {{ substr($review->user->email, 0, 4) }}-{{ $review->status }}
                        </div>
                    @endforeach
                    {{-- <x-element.login_as :user="$paper->currentsubmit->aecrep()->user" />
                        {{ $paper->currentsubmit->isAssigned('aec') ? '' : '?' }} --}}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@push('localjs')
    <script src="/js/sortable.js"></script>
@endpush
