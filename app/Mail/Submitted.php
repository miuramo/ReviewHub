<?php

namespace App\Mail;

use App\Models\File;
use App\Models\Paper;
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

class Submitted extends RetryMailable
{
    public Paper $paper;
    
    /**
     * Create a new message instance.
     */
    public function __construct($_paper)
    {
        $this->paper = $_paper;
        $this->mail_to_cc = $_paper->get_mail_to_cc();
        // 編集長をCCに追加
        $ec_role = \App\Models\Role::findByIdOrName('ec');
        $ec_users = $ec_role->users;
        foreach($ec_users as $u){
            $this->mail_to_cc['cc'][] = $u->email;
        }
        $this->subject = '投稿完了通知メール PaperID : '.$this->paper->id_03d();
        $pdffile = File::find($this->paper->pdf_file_id);
        $owner = User::find($this->paper->owner);
        $imagePath = $pdffile->getPdfHeadPath();
        while (!file_exists($imagePath)) {
            sleep(2);
        }
        // $imagePath = storage_path('app/public/files/nofile.png'); //public_path('files/nofile.png');
        // $imageDataURI = $this->convertImageToDataURI($imagePath);
        $this->content = new Content(
            markdown: 'emails.submitted',
            with: [
                'title' => $this->paper->title,
                'paperid' => $this->paper->id_03d(),
                'owner' => $owner,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdffile = File::find($this->paper->pdf_file_id);
        $imagePath = $pdffile->getPdfHeadPath();
        return [
            Attachment::fromPath($imagePath)->as("titleimage.png"),
            // ->withMime('image/png'),
        ];
    }
}
