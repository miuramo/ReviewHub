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

    public static function createTasks(Submit $sub){
        $wkfls = Workflow::get();
        $days = 0;
        $taskary = [];
        foreach($wkfls as $wkfl){
            $days += $wkfl->num_of_days;
            $task = Task::create([
                'submit_id' => $sub->id,
                'workflow_id' => $wkfl->id,
                'due_date' => $wkfl->addDaysToDate($days),
                // 'subject_id' => ($wkfl->id == 1)? $wkfl->subject_id() : null,
            ]);
            $taskary[$wkfl->id] = $task;
        }
        // next, next2を設定
        foreach($wkfls as $wkfl){
            $task = $taskary[$wkfl->id];
            if ($wkfl->next_workflow_id){
                $task->next = $taskary[$wkfl->next_workflow_id]->id;
            }
            if ($wkfl->next_workflow_id2){
                $task->next2 = $taskary[$wkfl->next_workflow_id2]->id;
            }
            $task->save();
        }


        $firsttask = Task::where('submit_id', $sub->id)->first();
        $firsttask->subject_id = $firsttask->workflow->subject_id();
        $firsttask->save();
    }
    public function addDaysToDate($days) {
        // 現在の日付を取得
        $currentDate = new DateTime();
        // 日数を加算
        $currentDate->modify("+$days days");
        // フォーマットして返す
        return $currentDate->format('Y-m-d');
    }
    public function subject_id(){
        if ($this->subject == "ec"){
            $role = Role::findByIdOrName("ec");
            $users = $role->users();
            return $users->first()->id;
        } else {
            return null;
        }
    }

    public function process(Task $task, Request $req)
    {
        if ($this->task == 'assign'){
            $task->object_id = $req->object_id;
            $task->completed = 1;
            $task->completed_at = now();
            $task->save();
            if ($this->object == "aec"){
                $task->submit->aec_id = $req->object_id;
                $task->submit->save();
            } else if ($this->object == "meta"){
                $rev = $task->submit->meta();
                $rev->user_id = $req->object_id;
                $rev->save();
            } else if ($this->object == "rev1"){
                $task->submit->rev1()->save_user_id($req->object_id);
            } else if ($this->object == "rev2"){
                $task->submit->rev2()->save_user_id($req->object_id);
            } else if ($this->object == "rev3"){
                $task->submit->rev3()->save_user_id($req->object_id);
            }
            // submitのstatusを更新
            $task->submit->updateStatus();

            // もし、approve必要なら
            if ($this->need_approve){
                // 次のタスクのsubjectを設定
                $ntask = Task::find($task->next);
                $ntask->subject_id = $task->object_id;
                $ntask->save();

                if ($task->next2){
                    $ntask = Task::find($task->next2);
                    $ntask->subject_id = $task->object_id;
                    $ntask->save();
                }
            } else {
                // 次のタスクのsubjectを設定
                $ntask = Task::find($task->next);
                $ntask->subject_id = $task->object_id;
                $ntask->save();

                if ($task->next2){
                    $ntask = Task::find($task->next2);
                    $ntask->subject_id = $task->object_id;
                    $ntask->save();
                }
            }
            return true;
        } else if ($this->task == 'submit'){

        } else if ($this->task == 'confirm'){

        } else if ($this->task == 'approve'){

        }
        return true;
    }
}
