<x-mail::message>
<style>
    table.inner-body {
        width: 90%;
    }
    img {
        border: 1px solid #777;
    }
</style>

{{ $body }}


<x-mail::button :url="$replyurl" color="success">
査読の承諾（または辞退）を連絡する
</x-mail::button>



---
# PaperID：{{ $paperid }}

# タイトル：{{ $title }}

![Embedded Image]({{ $preview_image_url ?? 'cid:firstpage.png' }})


<div style="border-bottom: 2px dotted #aaa; padding: 2px; margin: 20px 0;"></div>

本投稿の査読プロセスを管理する{{ $name_of_managers }}のメンバーは、以下の通りです。

<pre style="text-align: center; border: 2px dotted #aaa; padding: 10px; margin: 10px 80px;">
@foreach ($managers as $manager)
   {{ $manager->name }} （{{ $manager->affil }}）
@endforeach
</pre>

---

[{{ env('MAIL_FROM_NAME') }}]({{ env('APP_URL') }})

</x-mail::message>

