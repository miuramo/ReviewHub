<x-app-layout
>

    <x-slot name="header">
        <div class="mb-4">
            {{-- <x-element.linkbutton href="{{ route('role.top', ['role' => 'ec']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton> --}}
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('統計情報') }}
        </h2>
    </x-slot>

    <div class="py-2 px-6">
        <table class="border-collapse border border-gray-300">
            <thead>
                <tr class="bg-slate-300">
                    <th class="px-2 py-1">種別</th>
                    <th class="px-2 py-1">氏名</th>
                    <th class="px-2 py-1">所属</th>
                    <th class="px-2 py-1">査読担当数（ラウンド累計）</th>
                </tr>
            </thead>
            <tbody>
                @foreach($review_stats as $stat)
                    <tr class="hover:bg-yellow-50 {{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-slate-50 dark:bg-slate-600' }}">
                        <td class="px-2 py-1 text-center">
                            {{ $stat['target']==2 ? 'メタ' : '一般' }}
                        </td>
                        <td class="px-2 py-1 text-center">
                            {{ $users[$stat['user_id']]->name }}
                        </td>
                        <td class="px-2 py-1 text-center">
                            {{ $users[$stat['user_id']]->affil }}
                        </td>
                        <td class="px-2 py-1 text-center">
                            {{ $stat['review_count'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mx-10 py-4">
    </div>

</x-app-layout>
