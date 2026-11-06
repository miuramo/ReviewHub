<?php

namespace App\Models;

use App\Mail\Reminder;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $with = ['subject', 'object', 'submit', 'workflow'];

    protected $fillable = [
        'submit_id',
        'workflow_id',
        'started',
        'has_trouble',
        'issues',
        'due_date',
        'next',
        'join',
        'due_date',
        'completed',
        'completed_at',
        'require_approve',
        'approved',
        'approved_at',
        'subject_id',
        'object_id',
    ];

    protected $casts = [
        'issues' => 'array',
        'log' => 'array',
        'next' => 'array',
        'join' => 'array',
    ];
    protected $attributes = [
        'issues' => '[]',
        'log' => '[]',
        'next' => '[]',
        'join' => '[]',
    ];

    /**
     * 柔軟な査読者の割り当てと査読タスク生成
     */
    public static function createReviewTask(Submit $sub, int $revuid): Task
    {
        // ここに柔軟な査読者の割り当てと査読タスク生成の処理を書く
        if (auth()->user()) {
            $obj_id = auth()->user()->id;
        } else {
            // ログインしていない場合は、デフォルトのユーザーIDを使用
            $obj_id = 1; // ここは適切なデフォルト値に変更してください  
        }
        $task = Task::create([
            'submit_id' => $sub->id,
            'workflow_id' => 4,
            'subject_id' => $revuid,
            'object_id' => $obj_id,
        ]);
        return $task;
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function subject()
    {
        return $this->belongsTo(User::class, 'subject_id');
    }
    public function object()
    {
        return $this->belongsTo(User::class, 'object_id');
    }
    public function submit()
    {
        return $this->belongsTo(Submit::class);
    }
    public function review()
    {
        if ($this->workflow_id == 4 && $this->review_id === null) {
            // 探す
            $review = Review::where('submit_id', $this->submit_id)->where('user_id', $this->subject_id)->first();
            if ($review) {
                $this->review_id = $review->id;
                $this->save();
                // 自分自身を再読み込み
                $this->refresh();
            }
        }
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function dueForHumans(string $prefix = 'あと', string $postfix = '超過'): string
    {
        $date = $this->due_date;
        try {
            // 現在の日付を取得
            $currentDate = new DateTime();
            // 指定された日付を DateTime オブジェクトに変換
            $givenDate = new DateTime($date);
            // 差分を計算
            $difference = $currentDate->diff($givenDate);
            // 差分の日数を返す（符号を考慮）
            if ($difference->invert === 1) {
                // 過去の日付（期限超過）
                return $difference->days . '日' . $postfix;
            } else {
                // 未来の日付（残り日数）
                return $prefix . ($difference->days + 1) . '日';
            }
        } catch (Exception $e) {
            // 不正な日付形式の場合はエラーメッセージを返す
            return "Invalid date format: $date";
        }
    }

    /**
     * 承認要求メールを送信する
     * @param bool $isApprove 要求=0 か承認返信=1か
     */
    public function sendApproveMail(bool $isApprove, bool $approved): void
    {
        // ここにメール送信処理を書く TODO:
    }
    public function task_approved(): void
    {
        $this->approved = 1;
        $this->approved_at = now();
        $this->save();
    }

    /**
     * タスクの承認または辞退
     */
    public function approve(Request $req, bool $approved): void
    {
        $this->logappend($req->comment, $this->subject_id, $this->object_id, $req->approve);
        if ($approved) {
            $this->task_approved();
            $this->workflow->assign_forward($this, $this->object_id); // 割り当て（暫定）
            $this->workflow->proceed_workflow($this, $req); //承認が得られたので、ワークフローを介して、次のタスクに進む
            // 承認メールを送る・進行メールを送る
            $this->sendApproveMail(1, 1);
        } else {
            // 承認されなかったので、差し戻す
            $this->require_approve = 0;
            $this->object_id = null;
            $this->completed = 0;
            $this->completed_at = null;
            $this->save();
            $this->workflow->assign_backward($this);
            // 不承認メールを送る
            $this->sendApproveMail(1, 0);
        }
    }
    public function logappend(?string $comment, ?int $subject_id = null, ?int $object_id = null, ?int $approved = null): void
    {
        $localLog = $this->log ?? [];
        $subject_id = $subject_id ?? $this->subject_id;
        $object_id = $object_id ?? $this->object_id;
        $approved = $approved ?? $this->approved;
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $newMes = ['workflow_id' => $this->workflow->id, 'subject_id' => $subject_id, 'object_id' => $object_id, 'comment' => $comment, 'approved' => $approved, 'datetime' => $now];
        $localLog[] = $newMes;
        $this->log = $localLog;
        $this->save();
    }

    // next, next2をみながら、
    public function recursive_set_due_date(string $ymd): void
    {
        $this->due_date = $this->addDaysToDate($this->workflow->num_of_days, $ymd);
        $this->save();
        foreach ($this->next as $nextid) {
            Task::find($nextid)->recursive_set_due_date($this->due_date);
        }
    }
    public function addDaysToDate(int $days, ?string $currentDate = null): string
    {
        if ($currentDate == null) {
            // 現在の日付を取得
            $currentDate = new DateTime();
        } else {
            // 指定された日付を DateTime オブジェクトに変換
            $currentDate = new DateTime($currentDate);
        }
        // 日数を加算
        $currentDate->modify("+{$days} days");
        // フォーマットして返す
        return $currentDate->format('Y-m-d');
    }

    /**
     * TaskController.update から呼ばれる。ワークフローを進める。
     */
    public function process(Request $req): bool
    {
        return $this->workflow->process($this, $req);
    }

    public function log_comment_last(): string
    {

        $ary = $this->log;
        // 配列の最後の要素を取得
        return $ary[count($ary) - 1]['comment'];
    }

    public function random_proceed(): void
    {
        // いい感じに進める
        // もし割り当てタスクなら
        if ($this->workflow->task == "assign") {
            $rolename = $this->workflow->obj_role_name();
            while (true) {
                $u_random = Role::findByIdOrName($rolename)->users()->get()->random();
                if ($u_random->id > 2) break;
            }
            $this->object_id = $u_random->id;
            $this->completed = 1;
            $this->completed_at = $now = (new DateTime())->format('Y-m-d H:i:s');
            $this->require_approve = 0;
            $this->save();
            $this->task_approved();
            $this->refresh();
            $this->workflow->assign_forward($this, $this->object_id); // 割り当て
            $this->workflow->proceed_workflow($this); //承認が得られたので、ワークフローを介して、次のタスクに進む
        } else if ($this->workflow->task == "submit") {
            $this->completed = 1;
            $this->completed_at = $now = (new DateTime())->format('Y-m-d H:i:s');
            $this->require_approve = 0;
            $this->save();
            $this->task_approved();
            $this->refresh();
            $this->workflow->assign_forward($this, $this->object_id); // 割り当て
            $this->workflow->proceed_workflow($this); //承認が得られたので、ワークフローを介して、次のタスクに進む
        } else if ($this->workflow->task == "confirm") {
            $this->completed = 1;
            $this->completed_at = $now = (new DateTime())->format('Y-m-d H:i:s');
            $this->require_approve = 0;
            $this->save();
            $this->task_approved();
            $this->refresh();
            $this->workflow->assign_forward($this, $this->object_id); // 割り当て
            $this->workflow->proceed_workflow($this); //承認が得られたので、ワークフローを介して、次のタスクに進む
        } else { // approved 最後のタスク
            $this->completed = 1;
            $this->completed_at = $now = (new DateTime())->format('Y-m-d H:i:s');
            $this->require_approve = 0;
            $this->approved_at = $now;
            $this->approved = 1;
            $this->save();
            $this->refresh();
            $this->submit->ec_decision_at = (new DateTime())->format('Y-m-d');
            $this->submit->save();
            $this->workflow->proceed_workflow($this); //承認が得られたので、ワークフローを介して、次のタスクに進む ここで submit->paper->status_id = 9 にする。
        }
    }

    /**
     * Submitに移動したほうがよい
     */
    public function setDecision(): void
    {
        $this->submit->setDecision();
    }

    /**
     * 一旦完了したタスクを、未完了に戻す
     */
    public function revert(): void
    {
        $this->completed = 0;
        $this->completed_at = null;
        $this->approved = 0;
        $this->approved_at = null;
        $this->save();

        // reviewの終了日時も nullにする
        if ($this->review) {
            $this->review->end_at = null;
            $this->review->locked = false;
            $this->review->save();
        }
    }

    /**
     * 
     */
    public function updateReviewDownloadedAt(): ?string
    {
        if (!$this->started) {
            if ($this->review->start_at) {
                $this->started = true;
                $this->save();
            }
        }
        if ($this->review) {
            $this->review->updateReviewDownloadedAt();
            // $this->review を再読み込み
            $this->review->refresh();
        }
        return $this->review ? $this->review->downloaded_at : null;
    }

    public function updateReviewEditStartedAt(): ?string
    {
        if ($this->review) {
            $this->review->updateReviewEditStartedAt();
            // $this->review を再読み込み
            $this->review->refresh();
        }
        return $this->review ? $this->review->review_edit_started_at : null;
    }


    /**
     * リマインダメール送信
     */
    public function sendReminderEmail(): void
    {
        // 完了してたらリマインダメールを送らない
        if ($this->completed) return;
        // 査読かどうか？
        if ($this->workflow_id != 4) return;

        // メタかどうか？
        $is_meta = $this->review->target == 1;
        // 通常査読かどうか?
        // $is_regular = $this->review->target == 0;

        // もしメタの場合、ほかの通常査読が終わっているか？揃っていなければ締め切りを変更する
        if ($is_meta) {
            $this->extend_due_if_needed();
            $this->refresh();
        }

        // due_date の確認
        $seven_days_from_now = now()->addDays(8);
        if ($this->due_date && $this->due_date < $seven_days_from_now) {
            // 締め切りが7日以内の場合の処理
            $mes = $this->get_description();
            //メール通知
            (new Reminder($this, $mes))->process_send();
        } else {
            // Log::channel('single')->info('Due date', ['task_id' => $this->id, 'due_at' => $this->due_date]);
        }
        // 実際のメール送信処理をここに記述
    }

    /**
     * ここを定期的に呼び出すことで、リマインダメールを送信する
     */
    public static function sendReminderEmails(): void
    {
        $tasks = self::where('completed', 0)->get();
        foreach ($tasks as $task) {
            $task->sendReminderEmail();
        }
    }

    public function get_mail_to_cc(): array
    {
        $tolist = [];
        $cclist = [];
        $bcclist = [];

        $revuser = $this->subject;
        if ($revuser && $revuser->email) {
            $tolist[] = $revuser->email;
        }
        $paper = Paper::find($this->submit->paper_id);
        $aec = User::find($paper->aec_id);
        if ($aec && $aec->email) {
            // Log::channel('single')->info('AEC email', ['aec_id' => $aec->id, 'email' => $aec->email]);
            $cclist[] = $aec->email;
        }
        $bcclist[] = env('MAIL_BCC_ADDRESS', 'noreply@example.com');
        // Log::channel('single')->info('cclist', ['cclist' => $cclist, 'bcc' => $bcclist]);

        return ["to" => $tolist, "cc" => $cclist, "bcc" => $bcclist];
    }

    // メタの締め切りを延長する必要がある場合の処理
    public function extend_due_if_needed(): void
    {
        // ここに締め切りを延長する処理を記述
        $count_reviewtasks = Task::where("submit_id", $this->submit_id)
            ->where("workflow_id", 4)
            ->whereHas("review", function ($query) {
                $query->where("target", 0);
            })
            ->count();
        if ($count_reviewtasks == 2) {
            $reviewtasks = Task::where("submit_id", $this->submit_id)
                ->where("workflow_id", 4)
                ->whereHas("review", function ($query) {
                    $query->where("target", 0);
                })
                ->get();
            $tmp_due_date = $this->due_date;
            foreach ($reviewtasks as $reviewtask) {
                if ($reviewtask->due_date && $reviewtask->due_date > $tmp_due_date) {
                    $tmp_due_date = $reviewtask->due_date;
                }
            }
            $review_duration = \App\Models\Setting::getary('REVIEW_DURATION_DAYS')[1];
            $new_due_date = date('Y-m-d', strtotime($tmp_due_date . ' + ' . $review_duration . ' days'));
            if ($new_due_date != $this->due_date) {
                $this->due_date = $new_due_date;
                $this->save();
            }
        }
    }

    public function get_description(): ?string
    {
        $mes = "";
        if ($this->review->target == 1) {
            $count_reviewtasks = Task::where("submit_id", $this->submit_id)
                ->where("workflow_id", 4)
                ->whereHas("review", function ($query) {
                    $query->where("target", 0);
                })
                ->count();
            if ($count_reviewtasks < 2) {
                $mes .= "査読者2名の選定がまだおわっていません。";
                return $mes;
            }
        }
        if (!$this->review->updateReviewDownloadedAt()) {
            $mes .= "論文がまだダウンロードされていません。";
        } else if (!$this->review->updateReviewEditStartedAt()) {
            $mes .= "査読報告の編集がまだ開始されていません。";
        } else if (!$this->review->updateReviewEnteredAt()) {
            $mes .= "査読報告の入力がまだ完了していません。";
        }
        return $mes;
    }
}
