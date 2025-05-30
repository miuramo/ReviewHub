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


{!! $message !!}

---
# 連絡事項

{{ $comment }}



---
[{{ env('MAIL_FROM_NAME') }}]({{ env('APP_URL') }})


</x-mail::message>

