@props([
    'mes' => [],
    'readStatus' => null,
    'readStatusUrl' => null,
])
<!-- components.bb.mes  -->
@php
    $mes->mes = App\Models\Review::urllink(htmlspecialchars($mes->mes, ENT_QUOTES, 'UTF-8'));
    $mes->mes = strip_tags($mes->mes, '<a>');
    $file_desc = App\Models\Setting::getval('FILE_DESCRIPTIONS');
    $file_desc = json_decode($file_desc);
@endphp

@if ($mes->user_id == auth()->id())
    <div class="text-right message-div"
        @if (auth()->user()?->can('role', 'admin'))
            x-data="{ menuOpen: false, menuX: 0, menuY: 0 }"
            @contextmenu.prevent.stop="menuOpen = true; menuX = Math.min($event.clientX, window.innerWidth - 180); menuY = Math.min($event.clientY, window.innerHeight - 48)"
            @click.outside="menuOpen = false" @keydown.escape.window="menuOpen = false"
        @endif>
        <div class="inline-block w-3/4 bg-green-300 p-2 rounded-lg px-2 py-1 my-1 dark:bg-green-700 dark:text-gray-200">
            <div class="flex justify-between">
                <div class="mx-2">{{ $mes->subject }}</div>
                <div class="text-right text-gray-500 text-sm mr-2">{{ $mes->created_at }}</div>
            </div>
            <div class="bg-green-100 px-2 py-1 mb-1 rounded-md text-left dark:bg-green-800 dark:text-gray-200">{!! nl2br($mes->mes) !!}</div>

            <livewire:bb-mes-reaction-editor :bb-mes-id="$mes->id" :user-id="auth()->id()" :key="'bb-mes-reaction-' . $mes->id" />
            <x-bb.read-status :status="$readStatus" :url="$readStatusUrl" :message-id="$mes->id" :paper_id="$mes->bb->paper_id" />

            @if ($mes->files->count() > 0)
                <div class="text-left">
                    @foreach ($mes->files as $file)
                        <a class="underline text-blue-600 hover:bg-lime-200 p-2 dark:text-blue-300"
                            href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                            target="_blank">
                            {{ $file->origname }}
                        </a>
                        @if (
                            $mes->bb->paper->pdf_file_id == $file->id ||
                                $mes->bb->paper->img_file_id == $file->id ||
                                $mes->bb->paper->video_file_id == $file->id ||
                                $mes->bb->paper->altpdf_file_id == $file->id)
                            <span class="mx-1 text-sm text-blue-400">(採用済み)</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        @if (auth()->user()?->can('role', 'admin'))
            <div x-show="menuOpen" x-cloak @click.stop @mouseleave="menuOpen = false"
                class="z-50 min-w-40 rounded-md border border-gray-300 bg-white py-1 shadow-lg dark:border-gray-600 dark:bg-gray-800"
                :style="'position: fixed; left: ' + menuX + 'px; top: ' + menuY + 'px'">
                <form action="{{ route('bbmes.hide', ['bbMes' => $mes->id]) }}" method="post">
                    @csrf
                    <button type="submit" onclick="return confirm('本当に「{{ $mes->subject }}」（{{ $mes->created_at }}）を非表示にしてよいですか？')"
                        class="w-full px-4 py-2 text-left text-sm text-gray-800 hover:bg-yellow-100 dark:text-gray-200 dark:hover:bg-yellow-800">
                        このメッセージを非表示にする（管理者のみ）
                    </button>
                </form>
            </div>
        @endif
    </div>
@else
    <div class="bg-slate-300 rounded-lg w-3/4 px-2 py-1 my-1 dark:bg-slate-700 dark:text-gray-200 message-div"
        @if (auth()->user()?->can('role', 'admin'))
            x-data="{ menuOpen: false, menuX: 0, menuY: 0 }"
            @contextmenu.prevent.stop="menuOpen = true; menuX = Math.min($event.clientX, window.innerWidth - 180); menuY = Math.min($event.clientY, window.innerHeight - 48)"
            @click.outside="menuOpen = false" @keydown.escape.window="menuOpen = false"
        @endif>
        <div class="flex justify-between">
            <div class="mx-2">{{ $mes->subject }}</div>
            <div class="text-right text-gray-500 text-sm mr-2">{{ $mes->created_at }}</div>
        </div>
        <div class="bg-slate-100 px-2 py-1 mb-1 rounded-md dark:bg-slate-600">{!! nl2br($mes->mes) !!}</div>
        <livewire:bb-mes-reaction-editor :bb-mes-id="$mes->id" :user-id="auth()->id()" :key="'bb-mes-reaction-' . $mes->id" />
        <x-bb.read-status :status="$readStatus" :url="$readStatusUrl" :message-id="$mes->id" :paper_id="$mes->bb->paper_id" />

        @if ($mes->files->count() > 0)
            <div class="text-left">
                @foreach ($mes->files as $file)
                    <a class="underline text-blue-600 hover:bg-lime-200 p-2 dark:text-blue-300"
                        href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        target="_blank">
                        {{ $file->origname }}
                    </a>
                    @if (
                        $mes->bb->paper->pdf_file_id == $file->id ||
                            $mes->bb->paper->img_file_id == $file->id ||
                            $mes->bb->paper->video_file_id == $file->id ||
                            $mes->bb->paper->altpdf_file_id == $file->id)
                        <span class="mx-1 text-sm text-blue-400 dark:text-blue-200">(採用済み)</span>
                    @else
                        @if ($file->paper_id > 0)
                            @if ($file->valid)
                                <form action="{{ route('bb.adopt', ['bb' => $mes->bb->id, 'key' => $mes->bb->key]) }}"
                                    method="post" class="inline">
                                    @csrf
                                    @method('post')
                                    <input type="hidden" name="mes_id" value="{{ $mes->id }}">
                                    <input type="hidden" name="file_id" value="{{ $file->id }}">

                                    →→措置→→
                                    <select name="ftype" id="filetype"
                                        class="bg-yellow-100 px-2 py-1 rounded-lg dark:text-gray-500">
                                        @foreach ($file_desc as $name => $desc)
                                            <option value="{{ $name }}">{{ $desc }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                        class="bg-yellow-300 hover:bg-orange-300 px-1 py-1 rounded-lg dark:text-gray-500"
                                        onclick="return confirm('本当に採用しますか？（ファイル種別がただしいか、再度確認してください）')">←として採用し、本掲示板にその旨を通知する。</button>
                                    または <x-element.submitbutton2 value="reject" size="xs" color="red"
                                        id="bb_submit"
                                        confirm="採用せず処置済みにすることで、よろしければ、OKを押してください。">採用せずに処置済みにする（ファイルをinvalid&deletedにし、本掲示板に通知する）</x-element.submitbutton2>
                                </form>

                                <div class="mx-8 text-gray-600 dark:text-gray-50">参考：
                                    現在のファイル
                                    @if ($mes->bb->paper->pdf_file_id != 0)
                                        <a class="underline text-blue-600 hover:bg-lime-200 p-2 dark:text-blue-300"
                                            href="{{ route('file.showhash', ['file' => $mes->bb->paper->pdf_file_id, 'hash' => substr($mes->bb->paper->pdf_file->key, 0, 8)]) }}"
                                            target="_blank">
                                            PDF ({{ $mes->bb->paper->pdf_file->pagenum }}pages)
                                        </a>
                                    @endif
                                    @if ($mes->bb->paper->img_file_id != 0)
                                        <a class="underline text-blue-600 hover:bg-lime-200 p-2"
                                            href="{{ route('file.showhash', ['file' => $mes->bb->paper->img_file_id, 'hash' => substr($mes->bb->paper->img_file->key, 0, 8)]) }}"
                                            target="_blank">
                                            IMG
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-500">→措置済み（未採用）</span>
                            @endif
                        @endif
                    @endif
                @endforeach
            </div>
        @endif

        @if (auth()->user()?->can('role', 'admin'))
            <div x-show="menuOpen" x-cloak @click.stop @mouseleave="menuOpen = false"
                class="z-50 min-w-40 rounded-md border border-gray-300 bg-white py-1 shadow-lg dark:border-gray-600 dark:bg-gray-800"
                :style="'position: fixed; left: ' + menuX + 'px; top: ' + menuY + 'px'">
                <form action="{{ route('bbmes.hide', ['bbMes' => $mes->id]) }}" method="post">
                    @csrf
                    <button type="submit" onclick="return confirm('本当に「{{ $mes->subject }}」（{{$mes->created_at}}）を非表示にしてよいですか？')"
                        class="w-full px-4 py-2 text-left text-sm text-gray-800 hover:bg-yellow-100 dark:text-gray-200 dark:hover:bg-yellow-800">
                        このメッセージを非表示にする（管理者のみ）
                    </button>
                </form>
            </div>
        @endif

    </div>
@endif
