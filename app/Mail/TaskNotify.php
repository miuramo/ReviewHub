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

class TaskNotify extends RetryMailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(Task $task)
    {
        $this->mail_to_cc = $task->get_mail_to_cc();

        $this->subject = 'あなたにタスク依頼がありました';

        $this->content = new Content(
            markdown: 'emails.tasknotify',
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
