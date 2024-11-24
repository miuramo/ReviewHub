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

    /**
     * 新しいタスクを、submitに対して作成する
     */
    public static function createTasks(Submit $sub){
        $wkfls = Workflow::get();
        // $days = 0;
        $taskary = [];
        foreach($wkfls as $wkfl){
            // $days += $wkfl->num_of_days;
            $task = Task::create([
                'submit_id' => $sub->id,
                'workflow_id' => $wkfl->id,
                // 'due_date' => $wkfl->addDaysToDate($days),
                // 'subject_id' => ($wkfl->id == 1)? $wkfl->subject_id() : null,
            ]);
            $taskary[$wkfl->id] = $task;
        }
        // next, next2, next3を設定
        foreach($wkfls as $wkfl){
            $task = $taskary[$wkfl->id];
            if ($wkfl->next_workflow_id){
                $task->next = $taskary[$wkfl->next_workflow_id]->id;
            }
            if ($wkfl->next_workflow_id2){
                $task->next2 = $taskary[$wkfl->next_workflow_id2]->id;
            }
            if ($wkfl->next_workflow_id3){
                $task->next3 = $taskary[$wkfl->next_workflow_id3]->id;
            }
            $task->save();
        }
        // update due date recursively
        $now = (new DateTime())->format('Y-m-d');
        $taskary[1]->recursive_set_due_date($now);

        $firsttask = Task::where('submit_id', $sub->id)->first();
        $firsttask->subject_id = $firsttask->workflow->subject_id();
        $firsttask->save();
    }

    // 初期タスクのsubject_idを返す
    public function subject_id(){
        if ($this->subject == "ec"){
            $role = Role::findByIdOrName("ec");
            $users = $role->users();
            return $users->first()->id;
        } else {
            return null;
        }
    }

    // ワークフローを進める from TaskController.update
    public function process(Task $task, Request $req)
    {
        if ($this->task == 'assign'){
            $task->object_id = $req->object_id;
            $task->completed = 1;
            $task->completed_at = now();
            $task->logappend($req->comment, $task->subject_id, $req->object_id, 0);
            $task->save();

            // もし、approve必要なら
            if ($this->need_approve && !$req->skip_approve){
                $task->require_approve = 1; // 承認が必要
                $task->save();

                // メールを送る
                $task->sendApproveMail(0,0);//打診メールを送る。第2引数はあまり意味がないが、まだ承認されていないので0
            } else {
                // 承認不要で進める
                $this->assign_forward($task, $req->object_id); // 割り当て
                $task->sendApproveMail(0,1); // 進行メールを送る
                $this->proceed_workflow($task, $req); //ワークフローを介して、次のタスクに進む
            }
            return true;
        } else if ($this->task == 'submit'){

        } else if ($this->task == 'confirm'){

        } else if ($this->task == 'approve'){

        }
        return true;
    }
    /**
     * 割り当てた人に打診する。
     */
    public function approved(Task $task, Request $req)
    {
        $task->object_id = $req->object_id;
        $task->completed = 1;
        $task->completed_at = now();
        $task->save();
        // submitのstatusを更新
        $task->submit->updateStatus();

        // 次のタスクのsubjectを設定
        $ntask = Task::find($task->next);
        $ntask->subject_id = $task->object_id;
        $ntask->save();

        if ($task->next2){
            $ntask = Task::find($task->next2);
            $ntask->subject_id = $task->object_id;
            $ntask->save();
        }
        if ($task->next3){
            $ntask = Task::find($task->next3);
            $ntask->subject_id = $task->object_id;
            $ntask->save();
        }
        return true;
    }
    /**
     * 次のタスクに進む from Task.approve
     */
    public function proceed_workflow(Task $task, Request $req)
    {
        // 次のタスクのsubjectを設定
        $ntask = Task::find($task->next);
        $ntask->subject_id = $task->object_id;
        $ntask->save();

        if ($task->next2){
            $ntask = Task::find($task->next2);
            $ntask->subject_id = $task->object_id;
            $ntask->save();
        }
        if ($task->next3){
            $ntask = Task::find($task->next3);
            $ntask->subject_id = $task->object_id;
            $ntask->save();
        }
    }
    /**
     * 無事、承認された場合や、承認不要でプロセスが進んだ場合
     * 
     */
    public function assign_forward(Task $task, int $oid)
    {
        if ($this->object == "aec"){
            $task->submit->aec_id = $oid;
            $task->submit->save();
        } else if ($this->object == "meta"){
            $rev = $task->submit->meta();
            $rev->user_id = $oid;
            $rev->save();
        } else if ($this->object == "rev1"){
            $task->submit->rev1()->save_user_id($oid);
        } else if ($this->object == "rev2"){
            $task->submit->rev2()->save_user_id($oid);
        } else if ($this->object == "rev3"){
            $task->submit->rev3()->save_user_id($oid);
        }
        // submitのstatusを更新
        $task->submit->updateStatus();
    }
    public function assign_backward(Task $task){
        if ($this->object == "aec"){
            $task->submit->aec_id = null;
            $task->submit->save();
        } else if ($this->object == "meta"){
            $task->submit->meta()->save_user_id(null);
        } else if ($this->object == "rev1"){
            $task->submit->rev1()->save_user_id(null);
        } else if ($this->object == "rev2"){
            $task->submit->rev2()->save_user_id(null);
        } else if ($this->object == "rev3"){
            $task->submit->rev3()->save_user_id(null);
        }
        // submitのstatusを更新
        $task->submit->updateStatus();
    }

}
