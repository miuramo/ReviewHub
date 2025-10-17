@props([
    'paper_id' => null,
])
@php
$files = \App\Models\File::where('paper_id', $paper_id)
    ->orderBy('created_at', 'desc')
    ->get();
@endphp

<!-- components.review.myscores 自分が入力したスコア一覧 -->
    <table class="text-sm border-collapse border border-slate-400">
        <thead>
            <tr>
                <th class="border border-slate-300 px-2 py-1 bg-slate-200">アップロード日時</th>
                <th class="border border-slate-300 px-2 py-1 bg-slate-200">ファイル名</th>
                <th class="border border-slate-300 px-2 py-1 bg-slate-200">ページ数</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($files as $file)
                <tr
                    class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-slate-50 dark:bg-slate-600' }} hover:bg-cyan-50">
                    <td class="border border-slate-300 px-2 py-1">{{ $file->created_at }}</td>
                    <td class="border border-slate-300 px-2 py-1">
                        <a class="underline text-blue-600 hover:bg-lime-200"
                            href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 10)]) }}"
                            target="_blank"> {{ $file->origname }} </a>
                    </td>
                    <td class="border border-slate-300 px-2 py-1">{{ $file->pagenum }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{-- @foreach ($files as $file)
        <a class="underline text-blue-600 hover:bg-lime-200"
            href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 10)]) }}"
            target="_blank"> {{ $file->origname }} </a> {{ $file->created_at }}
        <span class="mx-4"></span>
    @endforeach --}}
