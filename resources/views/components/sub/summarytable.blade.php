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
                $ths = [
                    'タスクID',
                    'タスク名',
                    '問題あり？',
                    '開始？',
                    '担当者',
                    '次の担当者',
                    '承諾・完了済み？',
                    '承諾日時',
                    '締切予定日',
                    'あと',
                    '(参考)',
                ];
            @endphp
            @foreach ($ths as $th)
                <th class="p-1 bg-slate-300">{{ $th }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($sub->tasks as $task)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td class="p-1 text-center">{{ $task->id }}</td>
                <td class="p-1 text-center">{{ $task->workflow->description }} (by {{ $task->workflow->subject }})</td>
                <td class="p-1 text-center">{{ $task->has_trouble ? '問題あり' : '正常' }}</td>
                <td class="p-1 text-center">{{ $task->started ? '開始' : 'まだ' }}</td>
                <td class="p-1 text-center">{{ $task->subject->name ?? '--' }}</td>
                <td class="p-1 text-center">{{ $task->object->name ?? '--' }}</td>
                <td class="p-1 text-center">{{ $task->approved ? '承諾または完了済み' : '◆◆まだ◆◆' }}</td>
                <td class="p-1 text-center">{{ $task->approved_at }}</td>
                <td class="p-1 text-center">{{ $task->due_date ?? '--' }}</td>
                <td class="p-1 text-center">{{ $task->dueForHumans() }}</td>
                <td class="p-1 text-center">{{ $task->workflow->num_of_days }}</td>
        @endforeach
    </tbody>
</table>
