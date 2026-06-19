@props([
    'all' => [],
    'heads' => ['id', '種別', 'status', 'title', '査-状況'],
    'size' => 'md',
])
<!-- components.paper.summarytable -->
@php
    if ($size === 'sm') {
        $size_s = 'xs';
    } elseif ($size === 'md') {
        $size_s = 'sm';
    }
    $show_aec_name = App\Models\Setting::isTrue('show_aec_name');
    if ($show_aec_name) {
        $heads = ['担当幹事', 'id', '種別', 'status', 'title', '査-状況'];
    }
@endphp
<x-element.component_name>
    psummarytable_cm
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
                @if ($show_aec_name)
                    <td class="p-1 text-center text-{{ $size }}">
                        {{ $paper->aec ? $paper->aec->name : '未設定' }}
                    </td>
                @endif
                <td class="p-1 text-center text-{{ $size }}">
                    {{ $paper->id_03d() }}
                </td>
                <td class="p-1 text-center text-{{ $size }}">{{ $paper->category->name }}</td>

                <td class="p-1 text-center text-{{ $size }}">
                    @if (auth()->user()->can('see_review', $paper->id))
                        @php
                            $sub = $paper->currentsubmit;
                        @endphp
                        <a href="{{ route('paper.revstatus', ['paper' => $paper]) }}"
                            class="underline text-blue-600 hover:bg-lime-200" target="_blank">
                            {{ $paper->currentsubmit->round }}回目
                            {{ $paper->currentstatus->name }}
                        </a>
                    @else
                        {{-- 管理できないので、表示だけ --}}
                        {{ $paper->currentsubmit->round }}回目
                        {{ $paper->currentstatus->name }}
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

                <td class="p-1 text-center leading-tight text-nowrap text-{{ $size_s }}">
                    @foreach ($paper->judge() as $num => $judge)
                        <div>({{ $num }}回目) {{ $judge }}</div>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


<!-- components.paper.psummarytable_cm end -->
