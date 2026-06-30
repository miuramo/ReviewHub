@props([
    'enq' => [],
    'enqans' => [],
    'inline' => false,
])

<!-- components.enquete.view (呼び出し元は paper.show や paper.edit など) -->
@if ($inline)
<div class="inline-block align-top mr-4">
@endif
<table class="table-auto">
    <tbody>
        @forelse ($enq->items as $itm)
            @php
                $current = isset($enqans[$enq->id][$itm->id]) ? $enqans[$enq->id][$itm->id]->valuestr : null;
            @endphp
            <div class="mx-10">
                <x-enquete.itmview :itm="$itm" :current="$current" :loop="$loop">
                </x-enquete.itmview>
            </div>
        @empty
        @endforelse
    </tbody>
</table>
@if ($inline)
</div>
@endif
