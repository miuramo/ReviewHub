<x-mail::message>

警告：{{ $count }} 件のジョブが失敗しています。


---
[{{ env('MAIL_FROM_NAME') }}]({{ env('APP_URL') }})


</x-mail::message>


