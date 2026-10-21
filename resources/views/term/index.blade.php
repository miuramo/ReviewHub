<x-app-layout>
    <!-- sub.show -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            <span class="mx-2"></span>
            {{ __('役職・任期の確認') }}
        </h2>
    </x-slot>
    @section('title', '役職・任期の確認')

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="mx-6 my-4">
        @foreach ($years as $year)
            <a href="{{ route('term.index_year', ['year' => $year]) }}"
                class="mx-1 rounded px-3 py-1 text-sm {{ $selectedYear == $year ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600">
                {{ $year }}
            </a>
        @endforeach

        @can('role_any', 'ec|cm|manager')
            <span class="mx-4"></span>
            <a href="{{ route('term.edit_year', ['year' => $selectedYear]) }}"
                class="rounded bg-green-500 px-3 py-1 text-sm text-white hover:bg-green-600">
                {{ $selectedYear }}を編集
            </a>
        @endcan

    </div>

    <div class="mx-6">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
            <thead
                class="border-t border-b bg-gray-100 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">年度</th>
                    <th scope="col" class="px-6 py-3">役職</th>
                    <th scope="col" class="px-6 py-3">氏名（所属）</th>
                    {{-- <th scope="col" class="px-6 py-3">よみがな</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($terms as $term)
                    <tr
                        class="border-b bg-white hover:bg-lime-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-lime-700">
                        <td class="px-6 py-2">{{ $term->year }}</td>
                        <td class="px-6 py-2">{{ $term->post->name }}</td>
                        <td class="px-6 py-2">{{ $term->user->name }} ({{ $term->user->affil }})</td>
                        {{-- <td class="px-6 py-2">{{ $term->user->yomi }}</td> --}}

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="my-10 bg-gray-100 dark:bg-gray-800">
        &nbsp;
    </div>


</x-app-layout>
