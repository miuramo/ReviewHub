<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Workflow extends Model
{
    /** @use HasFactory<\Database\Factories\WorkflowFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'task',
        'object',
        'num_of_days',
        'next_workflow_id',
        'join'
    ];

    // protected $with = [];
    protected $attributes = [
        'subject' => 'ec',
        'task' => 'assign',
        'object' => 'meta',
        'num_of_days' => 7,
        'next_workflow_id' => '[]',
        'join' => '[]',
    ];
    protected $casts = [
        'next_workflow_id' => 'array',
        'join' => 'array',
    ];

    /**
     * WorkflowのTasksリレーション
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * 新しいタスクを、submitに対して作成する
     */
    public static function createTasks(Submit $sub): void
    {
        $wkfls = Workflow::get();
        // $days = 0;
        $taskary = [];
        foreach ($wkfls as $wkfl) {
            // $days += $wkfl->num_of_days;
            $task = Task::create([
                'submit_id' => $sub->id,
                'workflow_id' => $wkfl->id,
            ]);
            $taskary[$wkfl->id] = $task;
        }
        // next, next2, next3を設定
        foreach ($wkfls as $wkfl) {
            $task = $taskary[$wkfl->id];
            $localTNext = $task->next;
            foreach ($wkfl->next_workflow_id as $nextwfid) {
                $localTNext[] = $taskary[$nextwfid]->id;
            }
            $task->next = $localTNext;
            $task->save();
        }
        // joinを設定
        foreach ($wkfls as $wkfl) {
            $task = $taskary[$wkfl->id];
            $localJoin = $task->join;
            foreach ($wkfl->join as $joinwfid) {
                $localJoin[] = $taskary[$joinwfid]->id;
            }
            $task->join = $localJoin;
            $task->save();
        }

        // update due date recursively
        $now = (new DateTime())->format('Y-m-d');
        $taskary[1]->recursive_set_due_date($now);

        $firsttask = Task::where('submit_id', $sub->id)->first();
        // 最初のタスクのsubject_id は、タスク群を生成した作業者本人にする。
        $firsttask->subject_id = auth()->id(); // $firsttask->workflow->subject_id();
        $firsttask->started = 1;
        $firsttask->save();
    }

    // 初期タスクのsubject_idを返す
    // public function subject_id()
    // {
    //     if ($this->subject == "ec") {
    //         $role = Role::findByIdOrName("ec");
    //         $users = $role->users();
    //         return $users->first()->id;
    //     } else {
    //         return null;
    //     }
    // }

    public function obj_role_name(): string
    {
        if ($this->object == "rev1") {
            return "rev";
        } else if ($this->object == "rev2") {
            return "rev";
        } else if ($this->object == "rev3") {
            return "rev";
        } else {
            return $this->object;
        }
    }

    // ワークフローを進める from TaskController.update => Task.process => ココ
    public function process(Task $task, Request $req): bool
    {
        // TODO: joinであるとき、joinのタスクが終了していない場合は、失敗して、進めない
        if ($task->join) {
            foreach ($task->join as $jtid) {
                $jtask = Task::find($jtid);
                if (!$jtask->approved) {
                    return false;
                }
            }
        }
        if ($this->task == 'assign') {
            $task->object_id = $req->object_id;
            $task->completed = 1;
            $task->completed_at = now();
            $task->logappend($req->comment, $task->subject_id, $req->object_id, 0);
            $task->save();

            // もし、approve必要なら
            if ($this->need_approve && !$req->skip_approve) {
                $task->require_approve = 1; // 承認が必要
                $task->save();
                $this->assign_forward($task, $req->object_id); // 仮割り当て（まだスタートしない）
                // メールを送る
                $task->sendApproveMail(0, 0); //打診メールを送る。第2引数はあまり意味がないが、まだ承認されていないので0
            } else {
                // 承認不要で進める
                $task->task_approved();
                $this->assign_forward($task, $req->object_id); // 割り当て
                $task->sendApproveMail(0, 1); // 進行メールを送る
                $this->proceed_workflow($task, $req); //承認が得られたことになっているので、ワークフローを介して、次のタスクに進む
            }
            return true;
        } else if ($this->task == 'submit') {
            // 査読完了を報告する on Workflow.process

            $task->completed = 1;
            $task->completed_at = now();
            $task->logappend($req->comment, $task->subject_id, $req->object_id, 0);
            $task->save();

            $task->task_approved();
            $this->submit_forward($task); // 査読報告の次のタスクにすすむ、前準備をする
            $task->sendApproveMail(0, 1); // 進行メールを送る

            // Reviewのstatusを更新
            $review = Review::where("submit_id", $task->submit->id)->where("user_id", $task->subject_id)->first();
            if ($review) {
                $review->end_at = now();
                $review->save();
            }


            return $this->proceed_workflow($task, $req); //承認が得られたことになっているので、ワークフローを介して、次のタスクに進む
        } else if ($this->task == 'confirm') {
            $task->completed = 1;
            $task->completed_at = now();
            $task->logappend($req->comment, $task->subject_id, $req->object_id, 0);
            $task->save();

            $task->task_approved();
            $this->confirm_forward($task); // 査読報告の次のタスクにすすむ、前準備をする
            $task->sendApproveMail(0, 1); // 進行メールを送る
            $this->proceed_workflow($task, $req); //承認が得られたことになっているので、ワークフローを介して、次のタスクに進む

        } else if ($this->task == 'approve') {
            $task->completed = 1;
            $task->completed_at = now();
            $task->logappend($req->comment, $task->subject_id, $req->object_id, 0);
            $task->save();

            $task->task_approved();
            $task->sendApproveMail(0, 1); // 進行メールを送る
            // 採録・不採録なら、終了
            // それ以外なら、次のラウンドの準備をする
            $this->proceed_workflow($task, $req); // このなかで、setDecisionを呼び出す
        }
        return true;
    }
    /**
     * 承認が得られたので、次のタスクに進む from Task.approve
     */
    public function proceed_workflow(Task $task, ?Request $req = null, bool $started = true): bool
    {
        // タスク終了時に、Paperのstatusを更新
        if ($task->workflow->status_id_at_ended){
            $task->submit->paper->status_id = $task->workflow->status_id_at_ended;
            $task->submit->paper->save();
        }
        // PaperとFileをロックする
        if ($task->workflow->id == 1) {
            $task->submit->paper->lockAll(true);
        } else if ($task->workflow->id == 12) {
            $task->setDecision();
        }


        // 次のタスクのsubjectを設定
        foreach ($task->next as $nextid) {
            $ntask = Task::find($nextid);
            if (!$ntask->subject_id) {
                $ntask->subject_id = $task->object_id;
                $ntask->started = $started;
                $ntask->save();
            }

            // もし、次のタスクがsubmit or confirmタスクなら、object_id　を設定する
            if (
                $ntask->workflow->task == 'submit' || $ntask->workflow->task == 'confirm'
                || $ntask->workflow->task == 'approve'
            ) {
                if ($ntask->object_id == null) {
                    $ntask->object_id = $task->subject_id;
                    $ntask->save();
                }
            }
        }
        return true;
    }
    /**
     * 無事、承認された場合や、承認不要でプロセスが進んだ場合
     * 
     */
    public function assign_forward(Task $task, int $oid): void
    {
        // まだスタートはしないが、次のタスクのsubjectを割り当てる

        if ($this->object == "aec") {
            $task->submit->aec_id = $oid;
            $task->submit->save();
            // create review for aec
            Review::review_assign($task->submit->id, $oid,  3); // target 3はaec
            // reload task
            // $task->refresh();
            // $rev = $task->submit->aecrep();
            // $rev->user_id = $oid;
            // $rev->save();
        } else if ($this->object == "meta") {
            Review::review_assign($task->submit->id, $oid,  2); // target 2はmeta
            // $rev = $task->submit->meta();
            // $rev->user_id = $oid;
            // $rev->save();
        } else if ($this->object == "rev1" || $this->object == "rev2" || $this->object == "rev3") {
            Review::review_assign($task->submit->id, $oid,  1); // target 1はrev
            // $task->submit->rev1()->save_user_id($oid);
        }
        // // submitのstatusを更新
        // $task->submit->updateStatus();
    }
    public function assign_backward(Task $task): void
    {
        $task->started = false;
        if ($this->object == "aec") {
            $task->submit->aec_id = null;
            $task->submit->save();
        } else if ($this->object == "meta") {
            $task->submit->meta()?->save_user_id(null);
        } else if ($this->object == "rev1") {
            $task->submit->rev1()?->save_user_id(null);
        } else if ($this->object == "rev2") {
            $task->submit->rev2()?->save_user_id(null);
        } else if ($this->object == "rev3") {
            $task->submit->rev3()?->save_user_id(null);
        }
        // // submitのstatusを更新
        // $task->submit->updateStatus();
    }

    public function submit_forward(Task $task): void
    {
        // 次のタスクのsubjectを設定
    }
    public function confirm_forward(Task $task): void
    {
        // 次のタスクのsubjectを設定
    }
}

