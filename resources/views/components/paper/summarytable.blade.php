@props([
    'all' => [],
    'heads' => ['カテゴリ','id', 'status', 'title', 'pdf', '投稿日', '投稿者', '所属', 'AEC', 'メタ', '1査','2査'],
    'enqans' => [],
])
<!-- components.paper.summarytable -->
@php
    $papers = App\Models\Paper::get();
@endphp

<table class="min-w-full divide-y divide-gray-200">
    <thead>
        <tr>
            @foreach ($heads as $h)
                <th class="p-1 bg-slate-300">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($papers as $paper)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td class="p-1 text-center">{{ $paper->category->name }}</td>
                <td class="p-1 text-center">
                    <a href="{{ route('paper.show', ['paper' => $paper]) }}"
                        class="underline text-blue-600 hover:bg-lime-200" target="_blank">
                    {{ $paper->id_03d() }}
                    </a>
                </td>
                <td class="p-1 text-center">
                    @php
                        $sub = $paper->currentsubmit;
                    @endphp
                    <a href="{{ route('sub.show', ['sub' => $sub]) }}"
                        class="underline text-blue-600 hover:bg-lime-200" target="_blank">
                    {{$paper->currentsubmit->round}}回目 
                    {{ $paper->currentstatus->name }}</td>
                    </a>
                <td class="p-1 text-center block break-all">{{ $paper->title }}</td>
                <td class="p-1 text-center">
                    @if ($paper->pdf_file_id != 0)
                        <a class="underline text-blue-600 hover:bg-lime-200" href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                            target="_blank">
                            {{ $paper->pdf_file->pagenum }}page
                        </a>
                    @else
                        No PDF
                    @endif
                </td>

                <td class="p-1 text-center">{{ $paper->currentsubmit->submitted_at ?? '---' }}</td>
                <td class="p-1 text-center">{{ $paper->paperowner->name }}
                </td>
                <td class="p-1 text-center">{{ $paper->paperowner->affil }}
                </td>
                <td class="p-1">{{ $paper->currentsubmit->aec->name ?? '---' }}
                </td>
                <td class="p-1">{{ $paper->currentsubmit->meta()->user->name ?? '---' }}
                </td>
                <td class="p-1">{{ $paper->currentsubmit->rev1()->user->name ?? '---' }}
                </td>
                <td class="p-1">{{ $paper->currentsubmit->rev2()->user->name ?? '---' }}
                </td>

            </tr>
        @endforeach
    </tbody>
</table>

