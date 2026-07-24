<?php

namespace App\Mail;

use App\Models\Forum;
use App\Models\ForumMes;
use App\Models\Term;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class ForumMesNotify extends RetryMailable
{
    public Forum $forum;
    public ForumMes $forumMes;

    /** 個別送信用の受信者メールアドレス一覧 */
    private array $recipientEmails = [];

    public function __construct(Forum $forum, ForumMes $forumMes, User $sender)
    {
        $organization = env('MAIL_ORGANIZATION', '論文編集委員会');

        $this->forum    = $forum;
        $this->forumMes = $forumMes;

        // フォーラムの年度・役職 rank に合致する有効な任期を持つユーザを取得
        $fy        = $forum->fiscal_year();
        $forumRank = $forum->post->rank ?? 0;

        $recipientIds = Term::where('valid', true)
            ->where('year', $fy)
            ->whereHas('post', fn ($q) => $q->where('rank', '>=', $forumRank))
            ->pluck('user_id')
            ->unique();

        $this->recipientEmails = User::whereIn('id', $recipientIds)
            ->pluck('email')
            ->all();

        // process_send() に必要なため初期値を設定（sendIfRecipients 内で上書きする）
        $this->mail_to_cc = ['to' => '', 'cc' => []];

        // $this->subject = "【{$organization}】フォーラム「{$forum->title}」に投稿がありました";
        $this->subject = $forumMes->subject . " / {$forum->title} / ".($forum->post->name ?? '') . "フォーラム";

        // メッセージ本文中のURLをアンカーに変換
        $body = htmlspecialchars($forumMes->mes ?? '', ENT_QUOTES, 'UTF-8');
        $body = preg_replace(
            '/(https?:\/\/[^\s<>"\']+)/u',
            '<a href="$1">$1</a>',
            $body
        );

        $mesUrl = route('forum.show', ['forum' => $forum->id]) . '#mes-' . $forumMes->id;

        $this->content = new Content(
            markdown: 'emails.forum-mes-notify',
            with: [
                'forumTitle' => $forum->title,
                'postName'   => $forum->post->name ?? '',
                'senderName' => $sender->name,
                'mesSubject' => $forumMes->subject,
                'body'       => $body,
                'mesUrl'     => $mesUrl,
            ],
        );
    }

    /**
     * 受信者一人ひとりに個別メールを送信する。
     */
    public function sendIfRecipients(): void
    {
        foreach ($this->recipientEmails as $email) {
            $copy = clone $this;
            $copy->mail_to_cc = ['to' => $email, 'cc' => []];
            $copy->process_send();
        }
    }
}
