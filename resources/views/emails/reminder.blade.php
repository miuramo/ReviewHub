<x-mail::message>

{{ $affil_name ?? $email }} 様

査読に関するリマインダメールです。

<x-mail::panel>
状況： {{$description}}

予定締切日： {{$due_date ?? '未設定'}}  （{{ $ato }}）

PaperID： {{ $pid03d }}

タイトル： {{$title}}

</x-mail::panel>

お忙しいところすみませんが、ご対応のほど、よろしくお願いいたします。


---

[{{ env('MAIL_FROM_NAME') }}]({{ env('APP_URL') }})

このリマインダメールは担当幹事と査読者（または担当編集委員）に送信されています。

</x-mail::message>

