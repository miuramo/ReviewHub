<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-200 ">
            {{-- {{ __('掲示板やりとりまとめ') }} --}}
            {{-- <span class="mx-6"></span> --}}
            <x-element.paperid size=2 :paper_id="$paper->id"></x-element.paperid>
            <span class="mx-2"></span>
            {{ $paper->title }}
        </h2>
    </x-slot>
    <!-- paper.bb_summary -->
    @section('title', 'P' . $paper->id . ' ' . $paper->title)

    @php
        $name_of_managers = \App\Models\Setting::getValue("NAME_OF_MANAGERS");
    @endphp
    <style>
        .bb-summary-date {
            background-color: hsl(var(--bb-hue), 60%, 88%);
        }

        .dark .bb-summary-date {
            background-color: hsl(var(--bb-hue), 35%, 25%);
        }

        .bb-summary-date:hover {
            background-color: hsl(var(--bb-hue), 70%, 75%);
        }

        .dark .bb-summary-date:hover {
            background-color: hsl(var(--bb-hue), 45%, 35%);
        }
    </style>
    <div class="py-2">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif

        @if ($errors->any())
            <x-alert.error>入力エラーがあります。ご確認ください。</x-alert.error>
        @endif
    </div>

    {{-- // カラムを5つに分け、左から「著者との掲示板」「査読者との掲示板」「全査読者との掲示板」「投稿管理者同士の掲示板」とする。 --}}
    <div>
        <table class="table-auto w-full border-collapse border border-gray-300 dark:border-slate-600">
            <thead>
                <tr>
                    <th class="border border-gray-300 dark:border-slate-600 bg-slate-300 dark:bg-slate-600 px-2 py-2">日時</th>
                    <th class="border border-gray-300 dark:border-slate-600 bg-slate-300 dark:bg-slate-600 px-2 py-2">著者</th>
                    <th class="border border-gray-300 dark:border-slate-600 bg-slate-300 dark:bg-slate-600 px-2 py-2">査読者</th>
                    <th class="border border-gray-300 dark:border-slate-600 bg-slate-300 dark:bg-slate-600 px-2 py-2"></th>
                    <th class="border border-gray-300 dark:border-slate-600 bg-slate-300 dark:bg-slate-600 px-2 py-2">{{ $name_of_managers }}同士</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bbmessages as $mes)
                    <tr class="group">
                        @php
                            $month = (int) substr($mes->created_at, 5, 2);
                            $hue = ($month - 1) * 30; // 1月=0°, 12月=330°
                        @endphp
                        <td class="bb-summary-date border border-gray-300 dark:border-slate-600 px-1 py-1 text-xs text-center dark:text-slate-100"
                            style="--bb-hue: {{ $hue }}">
                            {{ substr($mes->created_at, 0, 16) }}
                        </td>
                        @php
                            // $bb->type -1 回だけ、<td></td>を出力
                        @endphp
                        @for ($i = 1; $i < $mes->bb->type; $i++)
                            <td class="bg-slate-300 dark:bg-slate-700 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-900"></td>
                        @endfor
                        <td class="border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 dark:text-slate-100 group-hover:bg-yellow-100 dark:group-hover:bg-yellow-900 px-1 py-1 text-xs">
                            {{ $mes?->user?->name }}→ @if($mes->bb->type == 2 && $mes->bb->review && $mes->bb->review->user_id != $mes->user_id) {{$mes->bb->review->user->name}} @endif <br>
                            {{ $mes->subject }}
                            <div class="max-h-0 opacity-0 overflow-hidden transition-all duration-500 ease-in-out group-hover:max-h-96 group-hover:opacity-100 group-hover:delay-300 mt-1 text-gray-600 dark:text-slate-300 whitespace-pre-wrap">{{ trim($mes->mes) }}</div>
                        </td>
                        @for ($i = $mes->bb->type; $i < 4; $i++)
                            <td class="bg-slate-300 dark:bg-slate-700 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-900"></td>
                        @endfor

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


</x-app-layout>
