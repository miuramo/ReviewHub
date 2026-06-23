@props([
    'paper' => null,
])

<!-- components.admin.change_paper_id -->

<div class="mx-6 mt-2">
    <div class="container">
        <x-element.button class="" id="toggleButton" value="Paper ID変更画面の開閉 (ec|aec)" color='red' size='sm'
            onclick="openclose('editpaperid')">
        </x-element.button>
    </div>
    {{-- (1) paper.id の変更 --}}
    <div class="hidden-content mt-2 bg-red-100 dark:bg-red-900 dark:text-gray-300 rounded-lg p-2" id="editpaperid"
        style="display:none;">
        <x-element.h1c color="red">Paper IDの変更
            @php
                $relatedCounts = [
                    'files' => \App\Models\File::where('paper_id', $paper->id)->count(),
                    'submits' => \Illuminate\Support\Facades\DB::table('submits')
                        ->where('paper_id', $paper->id)
                        ->count(),
                    'bbs' => \Illuminate\Support\Facades\DB::table('bbs')->where('paper_id', $paper->id)->count(),
                    'reviews' => \Illuminate\Support\Facades\DB::table('reviews')
                        ->where('paper_id', $paper->id)
                        ->count(),
                    'rev_conflicts' => \Illuminate\Support\Facades\DB::table('rev_conflicts')
                        ->where('paper_id', $paper->id)
                        ->count(),
                    'paper_contact' => \Illuminate\Support\Facades\DB::table('paper_contact')
                        ->where('paper_id', $paper->id)
                        ->count(),
                    'enquete_answers' => \Illuminate\Support\Facades\DB::table('enquete_answers')
                        ->where('paper_id', $paper->id)
                        ->count(),
                    'paper_manager' => \Illuminate\Support\Facades\DB::table('paper_manager')
                        ->where('paper_id', $paper->id)
                        ->count(),
                ];
            @endphp

            <p class="text-sm mb-2">Paper IDを変更すると、関連するすべてのテーブルも同時に更新されます。<br>
                <b>この操作は元に戻せません。必ず確認してから実行してください。</b>
            </p>

            <div class="text-sm mb-3 bg-white dark:bg-gray-800 rounded p-3">
                <p class="font-semibold mb-1">現在のPaper ID: <span
                        class="text-red-600 dark:text-red-400">{{ $paper->id }}</span>（表示: {{ $paper->id_03d() }}）
                </p>
                <p class="font-semibold mb-1">関連レコード件数（変更時に同時更新されます）：</p>
                <ul class="list-disc ml-5">
                    @foreach ($relatedCounts as $tbl => $cnt)
                        <li>{{ $tbl }}: {{ $cnt }} 件</li>
                    @endforeach
                </ul>
            </div>

            <form action="{{ route('paper.change_paper_id', ['paper' => $paper->id]) }}" method="POST"
                onsubmit="return confirm('本当にPaper IDを変更しますか？この操作は元に戻せません。')">
                @csrf
                <div class="form-group m-4">
                    <label for="new_paper_id">新しいPaper ID（整数）：</label>
                    <input type="number" name="new_paper_id" id="new_paper_id" min="1" required
                        class="form-control dark:bg-gray-700 dark:text-white border rounded px-2 py-1 w-32"
                        placeholder="{{ $paper->id }}">
                </div>
                <x-element.submitbutton color="red" size="md">
                    Paper IDを変更する
                </x-element.submitbutton>
            </form>
        </x-element.h1c>
    </div>

</div>
