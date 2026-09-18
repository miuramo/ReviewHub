<div>
    @foreach ($bb->messages as $mes)
        <x-bb.mes :mes="$mes" :read-status="$readStatuses[$mes->id]" :read-status-url="$readStatusUrl" />
    @endforeach
</div>
