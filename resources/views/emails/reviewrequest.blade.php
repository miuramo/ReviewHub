<x-mail::message>
<style>
    table.inner-body {
        width: 90%;
    }
    img {
        border: 1px solid #777;
    }
</style>

{{ $reviewer->affil }} {{ $reviewer->name }} さま


{{$organization}} における検討の結果、
{{ $reviewer->name }} さまに

{{$conftitle}} に投稿された
以下の論文の査読をお願いできればと考えております。

査読期間は、承諾いただいた日から24日間です。

（多少の延長は調整しますので、ご相談ください。）


お忙しいところすみませんが、ご協力いただけると幸いです。


査読をお願いしたい論文は以下の通りです。著者名・所属・概要につきましては、以下に添付した論文の1ページ目画像を参照してください。

査読の可否につきまして、数日中に本メール返信、または以下のボタンにて、投稿管理者までお知らせいただければ幸いです。

（本メールCc: に含まれているメンバーが、本投稿の査読プロセスを管理する投稿管理者です。）

<x-mail::button :url="$replyurl" color="success">
査読の承諾（または辞退）を連絡する
</x-mail::button>


---
# PaperID：{{ $paperid }}

# タイトル：{{ $title }}

![Embedded Image](cid:firstpage.png)


---
[{{ config('app.name') }}]({{ env('APP_URL') }})


</x-mail::message>

