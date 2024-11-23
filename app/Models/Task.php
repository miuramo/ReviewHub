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

    protected $fillable = [
        'submit_id',
        'workflow_id',
        'due_date',
        'next',
        'next2',
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
    public function next()
    {
        return $this->belongsTo(Task::class, 'next');
    }
    public function next2()
    {
        return $this->belongsTo(Task::class, 'next2');
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
     */
    public function sendApproveMail()
    {
        // ここにメール送信処理を書く TODO:
    }

    /**
     * タスクの承認または辞退
     */
    public function approve(Request $req, bool $approved,)
    {
        //     '_method' => 'PUT',
        //     'task' => '1',
        //     'redirect_role' => 'aec',
        //     'approve' => '1',
        //     'comment' => 'aa',
        //     'action' => 'approve',
        $this->logappend($req->comment, $this->object_id, $req->approve);
        if ($approved) {
            $this->approved = 1;
            $this->approved_at = now();
            $this->save();
            $this->workflow->proceed_workflow($this, $req); //承認が得られたので、ワークフローを介して、次のタスクに進む
            // 承認メールを送る・進行メールを送る
            $this->sendApproveMail();
        } else {
            // 承認されなかったので、差し戻す
            $this->require_approve = 0;
            $this->object_id = null;
            $this->completed = 0;
            $this->completed_at = null;
            $this->save();
            $this->workflow->assign_backward($this);
            // 不承認メールを送る
            $this->sendApproveMail();
        }
    }
    public function logappend($comment, $object_id, $approved)
    {
        $curlog = $this->log;
        $newMes = ['object_id' => $object_id, 'comment' => $comment, 'approved' => $approved, 'datetime' => now()];
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
            Task::find($this->next)->recursive_set_due_date($this->due_date);
        }
        if ($this->workflow->next_workflow_id2) {
            Task::find($this->next2)->recursive_set_due_date($this->due_date);
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

    public function process(Request $req)
    {
        return $this->workflow->process($this, $req);
    }
}
