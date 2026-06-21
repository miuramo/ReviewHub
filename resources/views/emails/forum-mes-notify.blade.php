<x-mail::message>

**{{ $postName }}** のフォーラム「**{{ $forumTitle }}**」に {{ $senderName }} さんが投稿しました。

---

@if ($mesSubject)
## {{ $mesSubject }}
@endif

<x-mail::panel>
{!! nl2br($body) !!}
</x-mail::panel>

<x-mail::button :url="$mesUrl" color="primary">
投稿を確認する
</x-mail::button>

---

[{{ env('MAIL_FROM_NAME') }}]({{ env('APP_URL') }})

（返信は、上のボタンからフォーラムを開いて行っていただけると幸いです。）

</x-mail::message>
