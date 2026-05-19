<x-app-layout>
    @section('title', '受付日・採録日の確認')

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            受付日・採録日の確認
        </h2>
    </x-slot>

    <div class="px-6 py-4">

        <table>
            <tr>
                <th class="text-left px-2 py-1 border">論文ID</th>
                <th class="text-left px-2 py-1 border">タイトル</th>
                <th class="text-left px-2 py-1 border">Vol-XX</th>
                <th class="px-2 py-1 border text-center">受付日・採録日</th>
                <th class="px-2 py-1 border text-center">査読日数</th>
            </tr>
            @php
                $days = [];
            @endphp
            @foreach ($subs as $sub)
                <tr>
                    <td class="px-2 py-1 border text-center">{{ $sub->paper_id }}</td>
                    <td class="px-2 py-1 border">{{ $sub->paper->title }}</td>
                    <td class="px-2 py-1 border text-center">{{ $sub->booth }}</td>
                    <td class="px-2 py-1 border text-center">
                        {!! $sub->paper->get_important_dates_display() !!}
                    </td>
                    <td class="px-2 py-1 border text-center">
                        {{ $days[] = $sub->paper->get_review_duration() }}
                    </td>
                </tr>
            @endforeach
        </table>
        <div class="mt-4">
            <p>平均査読日数(Avg): {{ count($days) > 0 ? round(array_sum($days) / count($days), 1) : 0 }}日<br>
            標準偏差(SD): {{ count($days) > 1 ? round(sqrt(array_sum(array_map(function ($x) use ($days) {
                return pow($x - (array_sum($days) / count($days)), 2);
            }, $days)) / (count($days) - 1)), 1) : 0 }}日<br>
            採録投稿数(N): {{ count($days) }}</p>
        </div>
    </div>

</x-app-layout>
