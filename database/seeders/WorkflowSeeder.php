<?php

namespace Database\Seeders;

use App\Models\Workflow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Workflow::create([ //1
            'name' => 'Assign Associate Editor in Chief',
            'subject' => 'ec',
            'task' => 'assign',
            'object' => 'aec',
            'description' => '幹事・副編集長を割り当てる',
            'num_of_days' => 2,
            'next_workflow_id' => [2,11],
        ]);
        Workflow::create([ //2
            'name' => 'Assign Meta Reviewer',
            'subject' => 'aec',
            'task' => 'assign',
            'object' => 'meta',
            'description' => 'メタ査読者を割り当てる',
            'num_of_days' => 3,
            'next_workflow_id' => [3,6,9],
        ]);
        Workflow::create([ //3 
            'name' => 'Assign Reviewer',
            'subject' => 'meta',
            'task' => 'assign',
            'object' => 'rev1',
            'description' => '査読者1を割り当てる',
            'num_of_days' => 7,
            'next_workflow_id' => [4],
        ]);
        Workflow::create([ //4
            'name' => 'Report Review1',
            'subject' => 'rev1',
            'task' => 'submit',
            'object' => 'meta',
            'need_approve' => false,
            'description' => '査読報告1を提出する',
            'num_of_days' => 24,
            'next_workflow_id' => [5],
        ]);
        Workflow::create([ //5
            'name' => 'Confirm Review1',
            'subject' => 'meta',
            'task' => 'confirm',
            'object' => 'rev1',
            'need_approve' => false,
            'description' => '査読報告1を確認する',
            'num_of_days' => 3,
            'next_workflow_id' => [9],
        ]);
        Workflow::create([ //6
            'name' => 'Assign Reviewer',
            'subject' => 'meta',
            'task' => 'assign',
            'object' => 'rev2',
            'description' => '査読者2を割り当てる',
            'num_of_days' => 7,
            'next_workflow_id' => [7],
        ]);
        Workflow::create([ //7
            'name' => 'Report Review2',
            'subject' => 'rev2',
            'task' => 'submit',
            'object' => 'meta',
            'need_approve' => false,
            'description' => '査読報告2を提出する',
            'num_of_days' => 24,
            'next_workflow_id' => [8],
        ]);
        Workflow::create([ //8
            'name' => 'Confirm Review2',
            'subject' => 'meta',
            'task' => 'confirm',
            'object' => 'rev2',
            'need_approve' => false,
            'description' => '査読報告2を確認する',
            'num_of_days' => 3,
            'next_workflow_id' => [9],
        ]);
        Workflow::create([ //9
            'name' => 'Report Meta Review',
            'subject' => 'meta',
            'task' => 'submit',
            'object' => 'aec',
            'need_approve' => false,
            'description' => 'メタ査読を提出する',
            'num_of_days' => 44,
            'join' => [2,5,8],
            'next_workflow_id' => [10],
        ]);
        Workflow::create([ //10
            'name' => 'Confirm Meta Review',
            'subject' => 'aec',
            'task' => 'confirm',
            'object' => 'meta',
            'need_approve' => false,
            'description' => 'メタ査読を確認する',
            'num_of_days' => 2,
            'next_workflow_id' => [11],
        ]);
        Workflow::create([ //11
            'name' => 'Submit Report',
            'subject' => 'aec',
            'task' => 'submit',
            'object' => 'ec',
            'need_approve' => false,
            'description' => '総合判定結果を提出する',
            'num_of_days' => 51,
            'next_workflow_id' => [12],
            'join' => [1,10],
        ]);
        Workflow::create([ //12
            'name' => 'Approve Report',
            'subject' => 'ec',
            'task' => 'approve',
            'object' => 'aec',
            'need_approve' => false,
            'description' => '総合判定結果を承認する',
            'num_of_days' => 3,
            // 'next_workflow_id' => ,
        ]);
        //
    }
}
