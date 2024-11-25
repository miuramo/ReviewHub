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

    // protected $with = [];
    protected $attributes = [
        'next_workflow_id' => '[]',
        'join' => '[]',
    ];
    protected $casts = [
        'next_workflow_id' => 'array',
        'join' => 'array',
    ];

    /**
     * 新しいタスクを、submitに対して作成する
     */
    public static function createTasks(Submit $sub)
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
        $firsttask->subject_id = $firsttask->workflow->subject_id();
        $firsttask->started = 1;
        $firsttask->save();
    }

    // 初期タスクのsubject_idを返す
    public function subject_id()
    {
        if ($this->subject == "ec") {
            $role = Role::findByIdOrName("ec");
            $users = $role->users();
            return $users->first()->id;
        } else {
            return null;
        }
    }

    // ワークフローを進める from TaskController.update => Task.process => ココ
    public function process(Task $task, Request $req)
    {
        // TODO: joinでがあるとき、joinのタスクが終了していない場合は、失敗して、進めない
        if ($task->join) {
            foreach ($task->join as $jtid) {
                $jtask = Task::find($jtid);
                if (!$jtask->completed) {
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

            $task->completed = 1;
            $task->completed_at = now();
            $task->logappend($req->comment, $task->subject_id, $req->object_id, 0);
            $task->save();

            $task->task_approved();
            $this->submit_forward($task); // 査読報告の次のタスクにすすむ、前準備をする
            $task->sendApproveMail(0, 1); // 進行メールを送る
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

            // Submit::factory()->create([
            //     'paper_id' => $task->submit->paper->id,
            //     'category_id' => $task->submit->category_id,
            //     'round' => $task->submit->round + 1,
            //     'resubmit_until' => date('Y-m-d', strtotime('+40 days')),
            // ])->init_reviews();
            $task->submit->paper->status_id = 9; //査読結果通知済み
            $task->submit->paper->save();
        }
        return true;
    }
    /**
     * 承認が得られたので、次のタスクに進む from Task.approve
     */
    public function proceed_workflow(Task $task, Request $req, bool $started = true)
    {
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
    }
    /**
     * 無事、承認された場合や、承認不要でプロセスが進んだ場合
     * 
     */
    public function assign_forward(Task $task, int $oid)
    {
        // まだスタートはしないが、次のタスクのsubjectを割り当てる

        if ($this->object == "aec") {
            $task->submit->aec_id = $oid;
            $task->submit->save();
            $rev = $task->submit->aecrep();
            $rev->user_id = $oid;
            $rev->save();
        } else if ($this->object == "meta") {
            $rev = $task->submit->meta();
            $rev->user_id = $oid;
            $rev->save();
        } else if ($this->object == "rev1") {
            $task->submit->rev1()->save_user_id($oid);
        } else if ($this->object == "rev2") {
            $task->submit->rev2()->save_user_id($oid);
        } else if ($this->object == "rev3") {
            $task->submit->rev3()->save_user_id($oid);
        }
        // submitのstatusを更新
        $task->submit->updateStatus();
    }
    public function assign_backward(Task $task)
    {
        $task->started = false;
        if ($this->object == "aec") {
            $task->submit->aec_id = null;
            $task->submit->save();
        } else if ($this->object == "meta") {
            $task->submit->meta()->save_user_id(null);
        } else if ($this->object == "rev1") {
            $task->submit->rev1()->save_user_id(null);
        } else if ($this->object == "rev2") {
            $task->submit->rev2()->save_user_id(null);
        } else if ($this->object == "rev3") {
            $task->submit->rev3()->save_user_id(null);
        }
        // submitのstatusを更新
        $task->submit->updateStatus();
    }

    public function submit_forward(Task $task)
    {
        // 次のタスクのsubjectを設定
    }
    public function confirm_forward(Task $task)
    {
        // 次のタスクのsubjectを設定
    }
}

    // /**
    //  * 割り当てた人に打診する。
    //  */
    // public function approved(Task $task, Request $req)
    // {
    //     $task->object_id = $req->object_id;
    //     $task->completed = 1;
    //     $task->completed_at = now();
    //     $task->save();
    //     // submitのstatusを更新
    //     $task->submit->updateStatus();

    //     // 次のタスクのsubjectを設定
    //     $ntask = Task::find($task->next);
    //     $ntask->subject_id = $task->object_id;
    //     $ntask->save();

    //     if ($task->next2) {
    //         $ntask = Task::find($task->next2);
    //         $ntask->subject_id = $task->object_id;
    //         $ntask->save();
    //     }
    //     if ($task->next3) {
    //         $ntask = Task::find($task->next3);
    //         $ntask->subject_id = $task->object_id;
    //         $ntask->save();
    //     }
    //     return true;
    // }
