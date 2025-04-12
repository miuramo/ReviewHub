<?php

namespace App\Mail;

use App\Models\Bb;
use App\Models\BbMes;
use App\Models\File;
use App\Models\Paper;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BbNotify extends RetryMailable
{
    public Bb $bb;
    public BbMes $bbmes;
    public Paper $paper;
    public string $name; // 〜〜掲示板
    /**
     * Create a new message instance.
     */
    public function __construct($_bb, $_bbmes)
    {
        $names = [1=>"著者との", 2=>"査読者との", 3=>"全査読者との", 4=>"投稿管理者同士の"];
        $this->bb = $_bb;
        $this->bbmes = $_bbmes;
        $this->paper = $_bb->paper;
        $this->mail_to_cc = $_bb->get_mail_to_cc();
        $this->name = $names[$_bb->type];

        $organization = env('MAIL_ORGANIZATION', '日本創造学会 論文誌編集委員会'); // 環境変数から組織名を取得
        $this->subject = "【{$organization}】" . $this->name . '掲示板に投稿がありました : ' . $this->paper->id_03d();

        $this->content = new Content(
            markdown: 'emails.bbnotify',
            with: [
                'bbsub' => $this->bbmes->subject,
                'mes' => $this->bbmes->mes,
                'bburl' => $this->bb->url(),
                'name' => $this->name,
                'pid03d' => $this->paper->id_03d(),
            ],
        );

    }
}
