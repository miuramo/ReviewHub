<?php

namespace App\Mail;

use App\Models\File;
use App\Models\Paper;
use App\Models\Review;
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

class ReviewRequest extends RetryMailable
{
    public Paper $paper;
    public User $reviewer;
    public Review $rev;

    /**
     * Create a new message instance.
     */
    public function __construct(Paper $_paper, User $_reviewer, Review $_rev)
    {
        $this->paper = $_paper;
        $this->reviewer = $_reviewer;
        $this->rev = $_rev;
        $this->mail_to_cc['to'][] = $this->reviewer->email;
        // 編集長をCCに追加
        $ec_users = $this->paper->managers;
        // 以下をつかうと、利害のある編集長にもメールが飛んでしまう
        // $ec_role = \App\Models\Role::findByIdOrName('ec');
        // $ec_users = $ec_role->users;
        $this->mail_to_cc['cc'][] = auth()->user()->email; // 操作者をCCに追加
        foreach ($ec_users as $u) {
            if ($u->email == auth()->user()->email) continue; // 操作者以外をBCCに追加する。（メタ査読者も投稿管理者や幹事団に含める可能性があるので、全員をCCには入れないことにした。）
            $this->mail_to_cc['bcc'][] = $u->email;
        }
        $managers_without_meta = $this->paper->managers_without_meta()->get();

        $organization = env('MAIL_ORGANIZATION', '日本創造学会 論文編集委員会'); // 環境変数から組織名を取得
        $conftitle = \App\Models\Setting::getval('CONFTITLE');
        // 1回目？2回目
        $revobj = \App\Models\Review::find($this->rev->id);
        $submit = \App\Models\Submit::find($revobj->submit_id);
        $review_duration = \App\Models\Setting::getary('REVIEW_DURATION_DAYS')[$this->rev->target];
        $review_type_name = $this->rev->review_type_name();
        if ($submit->round > 1) {
            $round = "（{$submit->round}回目）";
            $this->subject = "改訂稿が投稿されましたので、{$review_type_name}{$round}をお願いしたいです (ID : " . $this->paper->id_03d() . ')';
            $this->content = new Content(
                markdown: 'emails.reviewrequest2nd',
                with: [
                    'title' => $this->paper->title,
                    'paperid' => $this->paper->id_03d(),
                    'conftitle' => $conftitle,
                    'organization' => $organization,
                    'reviewer' => $this->reviewer,
                    'replyurl' => route('review.req_confirm', ['review' => $this->rev, 'token' => $this->rev->token_for_request()]),
                    'review_type_name' => $review_type_name,
                    'round' => $round,
                    'review_duration' => $review_duration,
                    'operator' => auth()->user()->name,
                    'name_of_manager' => \App\Models\Setting::getval('NAME_OF_MANAGER'),
                    'name_of_managers' => \App\Models\Setting::getval('NAME_OF_MANAGERS'),
                    'managers' => $managers_without_meta,
                ],
            );
        } else {
            $round = '';
            $this->subject = "【{$organization}より】" . $this->reviewer->name . "さまに{$review_type_name}{$round}をお願いしたいです (ID : " . $this->paper->id_03d() . ')';

            $this->content = new Content(
                markdown: 'emails.reviewrequest',
                with: [
                    'title' => $this->paper->title,
                    'paperid' => $this->paper->id_03d(),
                    'conftitle' => $conftitle,
                    'organization' => $organization,
                    'reviewer' => $this->reviewer,
                    'replyurl' => route('review.req_confirm', ['review' => $this->rev, 'token' => $this->rev->token_for_request()]),
                    'review_type_name' => $review_type_name,
                    'round' => $round,
                    'review_duration' => $review_duration,
                    'operator' => auth()->user()->name,
                    'name_of_manager' => \App\Models\Setting::getval('NAME_OF_MANAGER'),
                    'name_of_managers' => \App\Models\Setting::getval('NAME_OF_MANAGERS'),
                    'managers' => $managers_without_meta,
                ],
            );
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdffile = File::find($this->paper->pdf_file_id);
        $imagePath = $pdffile->getPdfThumbPath(1);
        return [
            Attachment::fromPath($imagePath)->as("firstpage.png"),
            // ->withMime('image/png'),
        ];
    }
}
