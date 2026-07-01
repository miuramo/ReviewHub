@props([
    'all' => [],
    'heads' => ['id', '種別', 'status', 'title / author', '査-状況'],
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
    psummarytable_cm
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
                        <span class="text-xs text-white bg-red-400 rounded px-1 py-0.5">利害関係者のため参照不可</span>
                    @endif
                </td>


                <td class="p-1 text-center block break-all text-{{ $size }}">
                    {{ $paper->title }}
                    {{-- @if (auth()->user()->can('manage_review', $paper->id)) --}}
                        @if ($paper->pdf_file_id != 0)
                            <a class="underline text-blue-600 hover:bg-lime-200"
                                href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                                target="_blank">
                                {{ $paper->pdf_file->pagenum }}page
                            </a>
                        @else
                            No PDF
                        @endif
                    {{-- @else
                        <span class="mx-4"></span>
                        {{-- 管理できないので、表示だけ --}}
                        {{-- @if ($paper->pdf_file_id != 0)
                            {{ $paper->pdf_file->pagenum }}page
                        @else
                            No PDF
                        @endif
                    @endif --}} 
                    {{-- 著者名リスト --}}
                    <div class="w-full text-xs">
                        @foreach (explode("\n", $paper->authorlist) as $author)
                            @php
                                $aname = trim(explode('(', $author)[0]);
                            @endphp
                            @if (isset($editor_hash) && isset($editor_hash[$aname]))
                                <span
                                    class="text-left mr-2 font-bold text-red-600 bg-yellow-200 rounded px-1 py-0.5">{{ $author }} </span>
                            @else
                                <span class="text-left mr-2 text-gray-500">{{ $author }}</span>
                            @endif
                        @endforeach
                    </div>
                </td>

                <td class="p-1 text-center leading-tight text-nowrap text-{{ $size_s }}">
                    @if (auth()->user()->can('see_review', $paper->id))
                        @foreach ($paper->judge() as $num => $judge)
                            <div>【{{ mb_convert_kana($num, 'N') }}回目】 {{ $judge }}</div>
                        @endforeach
                    @else
                        <span class="text-xs text-white bg-red-400 rounded px-1 py-0.5">参照不可</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>



<!-- components.paper.psummarytable_cm end -->
