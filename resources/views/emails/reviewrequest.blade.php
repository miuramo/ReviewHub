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

{{ $name_of_manager }} の {{ $operator }} と申します。

{{$organization}} における検討の結果、
{{ $reviewer->name }} さまに

{{$conftitle}} に投稿された
以下の論文の査読{{$round}}をお願いできればと考えております。

査読期間は、承諾いただいた日から{{$review_duration}}日間です。

（多少の延長は調整しますので、ご相談ください。）


お忙しいところすみませんが、ご協力いただけると幸いです。


査読をお願いしたい論文は以下の通りです。著者名・所属・概要につきましては、以下に添付した論文の1ページ目画像を参照してください。

査読の可否につきまして、数日中に本メール返信、または以下のボタンにて、{{ $name_of_manager }}までお知らせいただければ幸いです。

<x-mail::button :url="$replyurl" color="success">
査読の承諾（または辞退）を連絡する
</x-mail::button>


本投稿の査読プロセスを管理する{{ $name_of_managers }}メンバーは、以下の通りです。

<pre style="text-align: center; border: 2px dotted #aaa; padding: 10px; margin: 10px 40px;">
@foreach ($managers as $manager)
   {{ $manager->name }} （{{ $manager->affil }}）
@endforeach
</pre>


---
# PaperID：{{ $paperid }}

# タイトル：{{ $title }}

![Embedded Image](cid:firstpage.png)


---
[{{ env('MAIL_FROM_NAME') }}]({{ env('APP_URL') }})

</x-mail::message>

