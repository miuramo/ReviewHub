<?php

namespace App\Mail;

use App\Models\Paper;

use App\Models\Task;
use App\Models\Setting;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\Log;

class Reminder extends RetryMailable
{
    public Task $task;
    public string $description;
    /**
     * Create a new message instance.
     */
    public function __construct(Task $_task, string $_description)
    {
        $this->task = $_task;
        $this->description = $_description;

        $paper = Paper::find($this->task->submit->paper_id);

        // $organization = env('MAIL_ORGANIZATION', '論文編集委員会'); // 環境変数から組織名を取得
        $abb = strtoupper(Setting::getValue("CONFTITLE_ABB"));
        $name = $this->task->subject->name ?? '';
        $this->subject = "〈" . $abb .'-'. $paper->id_03d() . "〉" . "リマインダ【" . $name . "様へ：" . $this->description . "】";

        // 本番は査読者宛、それ以外はテスト用アドレス（MAIL_TEST_TO_ADDRESS）宛
        $this->mail_to_cc = $this->task->get_mail_to_cc();

        $this->content = new Content(
            markdown: 'emails.reminder',
            with: [
                'pid03d' => $paper->id_03d(),
                'task_id' => $this->task->id,
                'review_id' => $this->task->review->id,
                'reviewer_name' => $this->task->review->reviewer->name ?? '未設定',
                'description' => $this->description,
                'email' => $this->task->subject->email ?? '',
                'affil_name' => $this->task->subject->affil . " " . $this->task->subject->name ?? '',
                'due_date' => $this->task->due_date ?? '',
                'title' => $paper->title ?? '',
                'ato' => $this->task->dueForHumans() ?? '',
            ],
        );
    }
}
