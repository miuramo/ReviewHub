<x-app-layout>
    <!-- review.score_summary -->
    @php
        $catspans = App\Models\Category::spans();
        $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $cats = App\Models\Category::select('name', 'id')->get()->pluck('name', 'id')->toArray();
    @endphp
    @section('title', 'スコアのサマリー')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('スコアのサマリー') }} &nbsp;

        </h2>
    </x-slot>
    <div class="py-2 px-6">
        <form action="{{ route('review.score_summary') }}" method="post">
            @csrf
            <label for="vp_names">評価観点:</label>
            <select name="vp_names[]" id="vp_names" multiple class="text-xs">
                @foreach ($all_vp_names as $vp_name)
                    <option value="{{ $vp_name }}" selected>{{ $vp_name }}</option>
                @endforeach
            </select>
            <x-element.submitbutton color="lime">更新</x-element.submitbutton>
        </form>
    </div>

    <div class="py-2 px-6">
        <table class="table-auto border-collapse border border-gray-300">
            <tr>
                <th class="border border-gray-300">PaperID</th>
                <th class="border border-gray-300">観点</th>
                <th class="border border-gray-300">Sub</th>
            </tr>
            @foreach ($papers as $paper)
                @foreach ($vp_names as $vp_name)
                    <tr>
                        <td class="border border-gray-300 px-2 py-1">
                            {{ $paper->id_03d() }}
                        </td>
                        <td class="border border-gray-300 px-2 py-1">
                            {{ $vp_name }}
                        </td>
                        @foreach ($paper->submits as $sub)
                            <td class="border border-gray-300 px-2 py-1">
                                R{{ $sub->round }}
                            </td>
                            @foreach ($sub->reviews->reverse() as $rev)
                                <td class="border border-gray-300 px-2 py-1">
                                    {{-- (r {{ $rev->id }} : {{ $rev->target }}) --}}
                                    @foreach ($rev->scores_and_comments(0, 0, $sub->accept_id > 0) as $vpdesc => $valstr)
                                        @if ($vpdesc == $vp_name)
                                            {{ mb_substr($valstr, 0, 30) }}
                                        @endif
                                    @endforeach
                                </td>
                            @endforeach
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </table>
    </div>

</x-app-layout>
