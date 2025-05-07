<div>または、
    <input type="text" wire:model.live.debounce.500ms="search" wire:keydown.escape="resetSearch"
        placeholder="名前やメール等で検索" x-init="$el.focus()" /> して、割り当てボタンをおしてください。
    @php
        $fs = ['氏名', '所属', 'メール','割り当て'];
    @endphp
    <table>
        <thead>
            <tr>
                @foreach ($fs as $f)
                    <th class="border px-2 bg-slate-200">{{ $f }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $u)
                <tr>
                    <td class="border px-1 bg-white">
                        {{ $u->name }}
                    </td>
                    <td class="border px-1 bg-white">
                        {{ $u->affil }}
                    </td>
                    <td class="border px-1 bg-white">
                        {{ $u->email }}
                    </td>
                    <td class="border text-center bg-white">
                        <form action="{{ route('sub.review_assign', ['sub' => $submit_id]) }}" method="post">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="redirect_page" value="{{ route('paper.manage',['paper'=>$paper_id]) }}">
                            <input type="hidden" name="submit_id" value="{{ $submit_id }}">
                            <input type="hidden" name="reviewer_id" value="{{ $u->id }}">
                            <input type="hidden" name="target" value="1">
                            <x-element.submitbutton color="blue" size="sm" value="sadoku_wariate">通常査読を割り当て</x-element.submitbutton>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
