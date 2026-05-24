@props([
    'all' => [],
    'heads' => ['booth', 'count','title','authors'],
    'enqans' => [],
])

<!-- components.vote.votesumtable -->
@php
    $res = App\Models\VoteAnswer::vote_result();
    $votes = App\Models\Vote::select('name', 'id')->pluck('name', 'id')->toArray();

    $papers = App\Models\Paper::select('title', 'id')->pluck('title', 'id')->toArray();
    $subbooth2paperid = App\Models\Submit::select("paper_id", "booth")->get()->pluck("paper_id", "booth")->toArray();
    $authors = App\Models\Paper::select('authorlist', 'id')->pluck('authorlist', 'id')->toArray();

@endphp

@foreach ($votes as $vid => $vname)
        <div class="rounded-lg bg-lime-200 py-2 px-3 my-2 text-lg">{{ $vname }} の集計結果</div>
        @isset($res[$vid])
            <table class="min-w divide-y divide-gray-200 mx-6">
                <thead>
                    <tr>
                        @foreach ($heads as $h)
                            <th class="p-1 bg-slate-300">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">

                    @foreach ($res[$vid] as $booth => $count)
                        <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                            <td class="text-center">
                                {{ $booth }}
                            </td>
                            <td class="text-center">
                                {{ $count }}
                            </td>
                            <td>
                                {{ $papers[ $subbooth2paperid[$booth]]}}
                            </td>
                            <td>
                                {{ str_replace("\n", '，', trim($authors[$subbooth2paperid[$booth]])) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <span class="mx-6 text-blue-400">まだ該当する投票がありません。</span>
        @endisset
@endforeach

