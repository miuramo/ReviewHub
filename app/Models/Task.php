<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

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
    public static function createReviewTask(Submit $sub, int $revuid){
        // ここに柔軟な査読者の割り当てと査読タスク生成の処理を書く
        $task = Task::create([
            'submit_id' => $sub->id,
            'workflow_id' => 4,
            'subject_id' => $revuid,
            'object_id' => auth()->user()->id,
        ]);
        $task->due_date = $task->addDaysToDate(24);
        $task->save();
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
                return $prefix . $difference->days+1 . '日';
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
    public function task_approved()
    {
        $this->approved = 1;
        $this->approved_at = now();
        $this->save();
    }

    /**
     * タスクの承認または辞退
     */
    public function approve(Request $req, bool $approved)
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
    public function logappend($comment, $subject_id, $object_id, $approved)
    {
        $localLog = $this->log ?? [];
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $newMes = ['workflow_id' => $this->workflow->id, 'subject_id' => $subject_id, 'object_id' => $object_id, 'comment' => $comment, 'approved' => $approved, 'datetime' => $now];
        $localLog[] = $newMes;
        $this->log = $localLog;
        $this->save();
    }

    // next, next2をみながら、
    public function recursive_set_due_date(string $ymd)
    {
        $this->due_date = $this->addDaysToDate($this->workflow->num_of_days, $ymd);
        $this->save();
        foreach ($this->next as $nextid) {
            Task::find($nextid)->recursive_set_due_date($this->due_date);
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

        $ary = $this->log;
        // 配列の最後の要素を取得
        return $ary[count($ary) - 1]['comment'];
    }

    public function random_proceed()
    {
        // いい感じに進める
        // もし割り当てタスクなら
        if ($this->workflow->task == "assign"){
            $rolename = $this->workflow->obj_role_name();
            while(true){
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
        } else if ($this->workflow->task == "submit"){
            $this->completed = 1;
            $this->completed_at = $now = (new DateTime())->format('Y-m-d H:i:s');
            $this->require_approve = 0;
            $this->save();
            $this->task_approved();
            $this->refresh();
            $this->workflow->assign_forward($this, $this->object_id); // 割り当て
            $this->workflow->proceed_workflow($this); //承認が得られたので、ワークフローを介して、次のタスクに進む
        } else if ($this->workflow->task == "confirm"){
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

    public function setDecision()
    {
        // 自動タスク進行で、もし幹事の措置が空の場合、または、条件付きの場合、査読結果を条件付きにする
        $result = $this->submit->getReviewResult("result");
        if ($result == null || strpos($result,"条件付き") !== false){
            $this->submit->accept_id = 2; //条件付き
        } elseif (strpos($result,"採") === 0){
            $this->submit->accept_id = 1; //採録
        } elseif (strpos($result,"不") === 0){
            $this->submit->accept_id = 6; //不採録
        }
        $this->submit->save();

        $this->submit->paper->status_id = 9; //査読結果通知済み
        $this->submit->paper->save();
    }

}
