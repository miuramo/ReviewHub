<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    /** @use HasFactory<\Database\Factories\WorkflowFactory> */
    use HasFactory;

    public static function createTasks(Submit $sub){
        $wkfls = Workflow::get();
        $days = 0;
        foreach($wkfls as $wkfl){
            $days += $wkfl->num_of_days;
            $task = Task::create([
                'submit_id' => $sub->id,
                'workflow_id' => $wkfl->id,
                'due_date' => $wkfl->addDaysToDate($days),
                // 'subject_id' => ($wkfl->id == 1)? $wkfl->subject_id() : null,
            ]);
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
}
