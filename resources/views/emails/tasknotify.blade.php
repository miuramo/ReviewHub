<x-mail::message>

あなたにタスク依頼があります

# {{$bbsub}}

<x-mail::panel>
{!! nl2br($mes) !!}
</x-mail::panel>

<x-mail::button :url="$bburl" color="success">
ログインして確認する
</x-mail::button>

---

[{{ config('app.name') }}]({{ env('APP_URL') }})


</x-mail::message>

