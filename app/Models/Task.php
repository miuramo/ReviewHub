<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $with = ['subject', 'object', 'submit', 'workflow', 'tnext', 'tnext2', 'tnext3'];

    protected $fillable = [
        'submit_id',
        'workflow_id',
        'due_date',
        'next',
        'next2',
        'next3',
        'due_date',
        'completed',
        'completed_at',
        'require_approve',
        'approved',
        'approved_at',
        'subject_id',
        'object_id',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
    public function tnext()
    {
        return $this->belongsTo(Task::class, 'next');
    }
    public function tnext2()
    {
        return $this->belongsTo(Task::class, 'next2');
    }
    public function tnext3()
    {
        return $this->belongsTo(Task::class, 'next3');
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

    public function dueForHumans($prefix = 'あと', $postfix = '超過')
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
            if ($difference->days >= 0) {
                return $prefix . $difference->days . '日';
            } else {
                return $difference->days . '日' . $postfix;
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
    public function sendApproveMail(bool $isApprove, bool $approved)
    {
        // ここにメール送信処理を書く TODO:
    }

    /**
     * タスクの承認または辞退
     */
    public function approve(Request $req, bool $approved)
    {
        $this->logappend($req->comment, $this->subject_id, $this->object_id, $req->approve);
        if ($approved) {
            $this->approved = 1;
            $this->approved_at = now();
            $this->save();
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
    public function logappend($comment, $subject_id, $object_id, $approved)
    {
        $curlog = json_decode($this->log, true);
        $newMes = ['workflow_id' => $this->workflow->id, 'subject_id' => $subject_id, 'object_id' => $object_id, 'comment' => $comment, 'approved' => $approved, 'datetime' => now()];
        $curlog[] = $newMes;
        $this->log = $curlog;
        $this->save();
    }

    // next, next2をみながら、
    public function recursive_set_due_date(string $ymd)
    {
        $this->due_date = $this->addDaysToDate($this->workflow->num_of_days, $ymd);
        $this->save();
        if ($this->workflow->next_workflow_id) {
            $this->tnext->recursive_set_due_date($this->due_date);
        }
        if ($this->workflow->next_workflow_id2) {
            $this->tnext2->recursive_set_due_date($this->due_date);
        }
        if ($this->workflow->next_workflow_id3) {
            $this->tnext3->recursive_set_due_date($this->due_date);
        }
    }
    public function addDaysToDate(int $days, string $currentDate = null)
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
    public function process(Request $req)
    {
        return $this->workflow->process($this, $req);
    }

    public function log_comment_last()
    {
        $ary = json_decode($this->log, true);
        // 配列の最後の要素を取得
        return $ary[count($ary) - 1]['comment'];
    }
}
