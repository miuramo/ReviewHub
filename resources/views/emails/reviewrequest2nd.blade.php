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

投稿管理者です。いつもお世話になっております。

以前査読をご担当いただいた以下の論文の改訂稿が投稿されましたので、引き続き再査読{{$round}}をお願いできればと考えております。

査読期間は、承諾いただいた日から{{$review_duration}}日間です。（多少の延長は調整しますので、ご相談ください。）


お忙しいところすみませんが、引き続きご協力いただけると幸いです。


査読の可否につきましては、以下のボタンで開く投稿管理システムにて、ご回答ください。

（本メールCc: に含まれているメンバーが、本投稿の査読プロセスを管理する投稿管理者です。）

<x-mail::button :url="$replyurl" color="success">
査読の承諾（または辞退）を連絡する
</x-mail::button>


---
# PaperID：{{ $paperid }}

# タイトル：{{ $title }}

![Embedded Image](cid:firstpage.png)


---
[{{ env('MAIL_FROM_NAME') }}]({{ env('APP_URL') }})

</x-mail::message>

