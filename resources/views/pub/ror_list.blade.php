<x-app-layout>
    @section('title', 'RORの確認')

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            RORの確認
        </h2>
    </x-slot>

    <div class="px-6 py-4">

        <table>
            <tr>
                <th class="text-center px-2 py-1 border">投稿管理番号</th>
                <th class="px-2 py-1 border text-center">投稿者</th>
                <th class="px-2 py-1 border text-center">ROR</th>
                <th class="px-2 py-1 border text-center">URL(ROR)</th>
            </tr>
            @foreach ($subs as $sub)
                @php
                    $authors = $sub->paper->authorlist_ary('authorlist', true);
                    $rors = App\Models\Ror::affil2ror();
                @endphp
                @foreach ($authors as $n => $author)
                    @if (strpos($author[1], '/') !== false)
                        @php
                            $affils = explode('/', $author[1]);
                        @endphp
                        @foreach ($affils as $a)
                            <tr>
                                <td class="px-2 py-1 border text-center">{{ $sub->booth }}</td>
                                <td class="px-2 py-1 border text-center">{{ $author[0] }}</td>
                                <td class="px-2 py-1 border text-center">{{ isset($rors[$a]) ? '◯' : '×' }}</td>
                                <td class="px-2 py-1 border text-center">
                                    {{ isset($rors[$a]) ? $rors[$a] : '---' }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="px-2 py-1 border text-center">{{ $sub->booth }}</td>
                            <td class="px-2 py-1 border text-center">{{ $author[0] }}</td>
                            <td class="px-2 py-1 border text-center">{{ isset($rors[$author[1]]) ? '◯' : '×' }}</td>
                            <td class="px-2 py-1 border text-center">
                                {{ isset($rors[$author[1]]) ? $rors[$author[1]] : '---' }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </table>
    </div>

</x-app-layout>
