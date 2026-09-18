<div>
    @foreach ($messages as $mes)
        <x-forum.mes :mes="$mes" :depth="0" :is-close="$isClose" />
    @endforeach
</div>
