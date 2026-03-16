<x-app-layout>
    <!-- sub.show -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            <span class="mx-2"></span>
            {{ __('任期役職管理') }}
        </h2>
    </x-slot>
    @section('title', '任期役職管理')

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="mx-6">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">年度</th>
                    <th scope="col" class="px-6 py-3">役職</th>
                    <th scope="col" class="px-6 py-3">氏名（所属）</th>
                    <th scope="col" class="px-6 py-3">よみがな</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($terms as $term)
                    <tr class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                        <td class="px-6 py-4">{{ $term->year }}</td>
                        <td class="px-6 py-4">{{ $term->post->name }}</td>
                        <td class="px-6 py-4">{{ $term->user->name }} ({{ $term->user->affil }})</td>
                        <td class="px-6 py-4">{{ $term->user->yomi }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


</x-app-layout>
