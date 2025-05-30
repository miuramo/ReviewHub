<x-mail::message>

{{$name}}掲示板 ({{$pid03d}}) に投稿がありました。

# {{$bbsub}}

<x-mail::panel>
{!! nl2br($mes) !!}
</x-mail::panel>

<x-mail::button :url="$bburl" color="success">
掲示板をひらく
</x-mail::button>

---

[{{ env('MAIL_FROM_NAME') }}]({{ env('APP_URL') }})

（返信は、上の緑のボタンを押して「掲示板」から行っていただけると幸いです。ご協力よろしくおねがいいたします。）

</x-mail::message>

