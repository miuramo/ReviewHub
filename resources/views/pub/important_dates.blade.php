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
                </tr>
                @foreach ($subs as $sub)
                    <tr>
                        <td class="px-2 py-1 border text-center">{{ $sub->paper_id }}</td>
                        <td class="px-2 py-1 border">{{ $sub->paper->title }}</td>
                        <td class="px-2 py-1 border text-center">{{ $sub->booth }}</td>
                        <td class="px-2 py-1 border text-center">
                            {!! $sub->paper->get_important_dates_display() !!}
                        </td>
                    </tr>
                @endforeach
            </table>
    </div>

</x-app-layout>