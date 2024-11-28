<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Queue\InteractsWithQueue;

class BlockSpecificDomainListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(MessageSending $event)
    {
        $blockedDomains = ['example.net', 'test.com', 'googlegroups.com'];

        // 宛先のメールアドレスを取得
        $toAddresses = $event->message->getTo(); // Symfony\Component\Mime\Address オブジェクトの配列

        foreach ($toAddresses as $address) {
            $email = $address->getAddress(); // アドレス文字列を取得
            $domain = substr(strrchr($email, "@"), 1);
            if (in_array($domain, $blockedDomains)) {
                \Log::info("Blocked email to: $email");
                return false; // 送信をキャンセル
            }
        }
        $ccAddresses = $event->message->getCc(); // Symfony\Component\Mime\Address オブジェクトの配列
        foreach ($ccAddresses as $address) {
            $email = $address->getAddress(); // アドレス文字列を取得
            $domain = substr(strrchr($email, "@"), 1);
            if (in_array($domain, $blockedDomains)) {
                \Log::info("Blocked email cc: $email");
                return false; // 送信をキャンセル
            }
        }
        $bccAddresses = $event->message->getBcc(); // Symfony\Component\Mime\Address オブジェクトの配列
        foreach ($bccAddresses as $address) {
            $email = $address->getAddress(); // アドレス文字列を取得
            $domain = substr(strrchr($email, "@"), 1);
            if (in_array($domain, $blockedDomains)) {
                \Log::info("Blocked email bcc: $email");
                return false; // 送信をキャンセル
            }
        }
        return true;
    }
}
