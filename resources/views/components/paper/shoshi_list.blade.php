@props([
    'paper' => null,
    'expand_items' => [],
])
<script>
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        var textArea = document.createElement("textarea");
        textArea.value = copyText.innerText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand("Copy");
        document.body.removeChild(textArea);
        alert("コピーしました。");
    }
</script>
<!-- components.paper.authorlist -->
@php
    $koumoku = App\Models\Paper::including_optional_bibs(); //必須書誌情報

    $expand_items = array_flip($expand_items);
@endphp

<table class="border-cyan-500 border-2 dark:border-slate-500 dark:text-slate-100">
    @foreach ($koumoku as $k => $v)
        <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50 dark:bg-slate-700' : 'bg-white dark:bg-slate-800' }}">
            <td class="px-2 py-1 whitespace-nowrap">{{ $v }}</td>
            @if (strlen($paper->{$k}) < 2)
                <td class="px-2 py-1 text-red-600 dark:text-red-300 font-bold" id="confirm_{{ $k }}">（未設定）</td>
            @else
                @if (isset($expand_items[$k]))
                    <td class="px-2 py-1" id="confirm_{{ $k }}">
                        <x-element.button id="toggleButton" value="表示／非表示" color="cyan" size="xs"
                            onclick="openclose('div_{{ $k }}')">
                        </x-element.button>
                        <div id="div_{{ $k }}" style="display:none; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            {!! nl2br($paper->{$k}) !!}
                        </div>
                    </td>
                @else
                    <td class="px-2 py-1" id="confirm_{{ $k }}">
                        {!! str_replace("\n", " , ", htmlspecialchars($paper->{$k}, ENT_QUOTES, 'UTF-8')) !!}
                    </td>
                @endif
            @endif
        </tr>
    @endforeach
</table>
<div>
<x-element.button id="toggleButton" value="テキスト形式の書誌情報を表示／非表示" color="cyan" size="xs" onclick="openclose('div_textbibinfo')">
                        </x-element.button>
<div id="div_textbibinfo" style="display:none; padding-top: 0.5rem; padding-bottom: 0.5rem;">
    <button onclick="copyToClipboard('pre_textbibinfo')" class="px-2 py-1 bg-blue-500 text-white rounded text-sm">クリップボードにコピー</button>
<pre id="pre_textbibinfo" class="text-xs p-2 bg-white dark:bg-slate-800 text-wrap" >
PaperID :  {{ $paper->id_03d() }}
@foreach($koumoku as $k => $v)
{{ $v }} :  {{ str_replace("\r\n", " , ", $paper->{$k})}}
@endforeach
</pre>
</div>
</div>