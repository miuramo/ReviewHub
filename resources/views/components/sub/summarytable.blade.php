@props([
    'all' => [],
    'heads' => ['項目', '値'],
    'enqans' => [],
    'sub' => null,
])
<!-- components.paper.summarytable -->
@php
    $papers = App\Models\Paper::get();

    $tasks = App\Models\Task::where('submit_');
@endphp

<table class="min-w divide-y divide-gray-200">
    <thead>
        <tr>
            <th class="p-1 bg-slate-300">ラウンド</th>
            <th class="p-1 bg-slate-300">{{ $sub->round }}</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($sub->heads() as $h => $hc)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td class="p-1 text-center">{{ $hc }}</td>
                <td class="p-1 text-center">{{ $sub->{$h} ?? '--' }}</td>
        @endforeach
    </tbody>
</table>

<table class="min-w divide-y divide-gray-200">
    <thead>
        <tr>
            @php
                $ths = ['タスク名', '担当者', '予定日'];
            @endphp
            @foreach ($ths as $th)
                <th class="p-1 bg-slate-300">{{ $th }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($sub->tasks as $task)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td class="p-1 text-center">{{ $task->workflow->description }}</td>
                <td class="p-1 text-center">{{ $task->subject->name ?? '--' }}</td>
                <td class="p-1 text-center">{{ $task->due_date ?? '--' }}</td>
        @endforeach
    </tbody>
</table>
