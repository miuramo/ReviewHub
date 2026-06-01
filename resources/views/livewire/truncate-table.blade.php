<div>
    
    @if($count==0)
    <span class="text-gray-400">truncate</span>
    @else
    <button 
    @click="if (confirm('本当にこのテーブル（{{$tableName ?? '不明'}}）のデータをすべて削除しますか？')) { $wire.truncate() }"
    class="bg-orange-200 hover:bg-orange-400
     rounded-lg p-1 px-2 text-sm">{{$count}} truncate
    </button> 
    @endif
</div>
