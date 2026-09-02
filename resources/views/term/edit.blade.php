<x-app-layout>
    <!-- sub.show -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            <span class="mx-2"></span>
            {{ __('役職・任期の編集') }} {{ $year}}
        </h2>
    </x-slot>
    @section('title', '役職・任期の編集')

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    <div class="mx-6 my-4">

        <div class="mx-6">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">年度</th>
                        <th scope="col" class="px-6 py-3">役職</th>
                        <th scope="col" class="px-6 py-3">氏名（所属）</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($terms as $term)
                        <tr class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                            <td class="px-6 py-2">{{ $term->year }}</td>
                            <td class="px-6 py-2">
                                <livewire:term-editor :term="$term" />
                            </td>
                            <td class="px-6 py-2">{{ $term->user->name }} ({{ $term->user->affil }})</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
