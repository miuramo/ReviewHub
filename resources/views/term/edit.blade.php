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
        <div class="mx-6 my-4 text-sm text-green-600 bg-lime-100 py-1 px-2 rounded-md">
            注：役職名をクリックすると変更できます。
            <span class="mx-4"></span><x-element.linkbutton2 color="green" href="{{route('role.edit', ['role'=>'cm'])}}">新年度の役職を設定する（編集委員メンバーの編集）</x-element.linkbutton2>
            <span class="mx-4"></span>            <a href="{{ route('term.index_year', ['year' => $year]) }}"
                class="rounded bg-blue-500 px-3 py-1 text-sm text-white hover:bg-blue-600">
                {{$year}} 参照にもどる
            </a>
        </div>

        <div class="mx-6">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                    <tr class="border-t border-b">
                        <th scope="col" class="px-6 py-3">年度</th>
                        <th scope="col" class="px-6 py-3">役職</th>
                        <th scope="col" class="px-6 py-3">氏名（所属）</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($terms as $term)
                        <tr class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                            <td class="px-6 py-2">{{ $term->year }}</td>
                            <td class="px-6 py-2 hover:bg-lime-100 dark:hover:bg-lime-700">
                                <livewire:term-editor :term="$term" />
                            </td>
                            <td class="px-6 py-2">{{ $term->user->name }} ({{ $term->user->affil }})</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

                <div class="mx-6 my-4">
        </div>

    </div>

</x-app-layout>
