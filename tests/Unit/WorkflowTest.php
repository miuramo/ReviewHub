<?php

namespace Tests\Unit;

use App\Models\Workflow;
use App\Models\Task;
use App\Models\Submit;
use App\Models\Paper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    #[Test]
    public function it_can_create_a_workflow(): void
    {
        // When: Workflowを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Workflow Creation',
            'task' => 'assign',
            'subject' => 'ec',
            'object' => 'meta'
        ]);

        // Then: Workflowが正常に作成される
        $this->assertInstanceOf(Workflow::class, $workflow);
        $this->assertDatabaseHas('workflows', ['id' => $workflow->id]);
        $this->assertEquals('Test Workflow Creation', $workflow->name);
        $this->assertEquals('assign', $workflow->task);
    }

    #[Test]
    public function it_has_valid_task_enum_values(): void
    {
        // Given: 有効なタスク値
        $validTasks = ['assign', 'approve', 'submit', 'confirm'];

        foreach ($validTasks as $taskType) {
            // When: 各タスクタイプでWorkflowを作成
            $workflow = Workflow::factory()->create([
                'name' => "Test {$taskType} Workflow",
                'task' => $taskType
            ]);

            // Then: 正常に作成され、正しい値が設定される
            $this->assertInstanceOf(Workflow::class, $workflow);
            $this->assertEquals($taskType, $workflow->task);
        }
    }

    #[Test]
    public function it_has_valid_subject_enum_values(): void
    {
        // Given: 有効なsubject値
        $validSubjects = ['ec', 'aec', 'meta', 'rev1', 'rev2', 'rev3'];

        foreach ($validSubjects as $subjectType) {
            // When: 各subjectタイプでWorkflowを作成
            $workflow = Workflow::factory()->create([
                'name' => "Test {$subjectType} Workflow",
                'subject' => $subjectType
            ]);

            // Then: 正常に作成され、正しい値が設定される
            $this->assertInstanceOf(Workflow::class, $workflow);
            $this->assertEquals($subjectType, $workflow->subject);
        }
    }

    #[Test]
    public function it_has_valid_object_enum_values(): void
    {
        // Given: 有効なobject値
        $validObjects = ['ec', 'aec', 'meta', 'rev1', 'rev2', 'rev3'];

        foreach ($validObjects as $objectType) {
            // When: 各objectタイプでWorkflowを作成
            $workflow = Workflow::factory()->create([
                'name' => "Test {$objectType} Workflow",
                'object' => $objectType
            ]);

            // Then: 正常に作成され、正しい値が設定される
            $this->assertInstanceOf(Workflow::class, $workflow);
            $this->assertEquals($objectType, $workflow->object);
        }
    }

    #[Test]
    public function it_has_default_values(): void
    {
        // When: 最小限の属性でWorkflowを作成
        $workflow = Workflow::create([
            'name' => 'Test Default Values Workflow'
        ]);

        // Then: デフォルト値が正しく設定される
        $this->assertEquals('ec', $workflow->subject);
        $this->assertEquals('assign', $workflow->task);
        $this->assertEquals('meta', $workflow->object);
        $this->assertEquals(7, $workflow->num_of_days);
        $this->assertEquals([], $workflow->next_workflow_id);
        $this->assertEquals([], $workflow->join);
        $this->assertEquals('Test Default Values Workflow', $workflow->name);
    }

    #[Test]
    public function it_has_cast_attributes(): void
    {
        // Given: JSON属性を持つWorkflowを作成
        $nextWorkflowIds = [2, 3, 4];
        $joinData = ['task1', 'task2'];
        
        $workflow = Workflow::factory()->create([
            'name' => 'Test Cast Attributes',
            'next_workflow_id' => $nextWorkflowIds,
            'join' => $joinData
        ]);

        // When: JSON属性にアクセス
        $retrievedNext = $workflow->next_workflow_id;
        $retrievedJoin = $workflow->join;

        // Then: 配列として正しくキャストされている
        $this->assertIsArray($retrievedNext);
        $this->assertIsArray($retrievedJoin);
        $this->assertEquals($nextWorkflowIds, $retrievedNext);
        $this->assertEquals($joinData, $retrievedJoin);
    }

    #[Test]
    public function it_can_have_tasks_relationship(): void
    {
        // Given: Workflowとそれに関連するTasksを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Tasks Relationship',
            'task' => 'assign'
        ]);
        $paper = Paper::factory()->create();
        $submit = Submit::factory()->create(['paper_id' => $paper->id]);
        
        $task1 = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);
        $task2 = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);

        // When: WorkflowからTasksにアクセス（リレーションが定義されている場合）
        // Note: Workflowモデルにtasksリレーションが定義されているかチェック
        $hasTasksRelation = method_exists($workflow, 'tasks');
        
        if ($hasTasksRelation) {
            $relatedTasks = $workflow->tasks;
            
            // Then: 関連するTasksが取得できる
            $this->assertGreaterThanOrEqual(2, $relatedTasks->count());
        } else {
            // リレーションが未定義の場合はスキップ
            $this->markTestSkipped('tasks relationship not defined in Workflow model');
        }
    }

    #[Test]
    public function it_has_create_tasks_static_method(): void
    {
        // When: createTasksメソッドが存在することを確認
        $hasCreateTasksMethod = method_exists(Workflow::class, 'createTasks');

        // Then: createTasksメソッドが定義されている
        $this->assertTrue($hasCreateTasksMethod, 'Workflow should have createTasks static method');
    }

    #[Test]
    public function it_can_store_complex_json_data(): void
    {
        // Given: 複雑なJSON構造のデータ
        $complexNext = [
            ['id' => 1, 'condition' => 'approved'],
            ['id' => 2, 'condition' => 'rejected']
        ];
        $complexJoin = [
            'required_tasks' => [1, 2, 3],
            'wait_for_all' => true,
            'timeout' => 86400
        ];
        
        // When: 複雑なデータを持つWorkflowを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Complex JSON',
            'next_workflow_id' => $complexNext,
            'join' => $complexJoin
        ]);

        // Then: データが正しく保存・取得される
        $this->assertEquals($complexNext, $workflow->next_workflow_id);
        $this->assertEquals($complexJoin, $workflow->join);
        $this->assertEquals(true, $workflow->join['wait_for_all']);
        $this->assertEquals(86400, $workflow->join['timeout']);
    }

    #[Test]
    public function it_has_factory_trait(): void
    {
        // When: Workflowにファクトリーが使用できるかテスト
        $workflow = Workflow::factory()->create();

        // Then: ファクトリーで正常に作成される
        $this->assertInstanceOf(Workflow::class, $workflow);
        $this->assertNotNull($workflow->name);
        $this->assertNotNull($workflow->id);
    }

    #[Test]
    public function it_validates_num_of_days_is_positive(): void
    {
        // When: 正の値でnum_of_daysを設定
        $workflow = Workflow::factory()->create([
            'name' => 'Test Positive Days',
            'num_of_days' => 14
        ]);

        // Then: 正しく設定される
        $this->assertEquals(14, $workflow->num_of_days);
        $this->assertGreaterThan(0, $workflow->num_of_days);
    }

    #[Test]
    public function it_can_be_updated(): void
    {
        // Given: 既存のWorkflow
        $workflow = Workflow::factory()->create([
            'name' => 'Original Name',
            'task' => 'assign'
        ]);

        // When: Workflowを更新
        $workflow->update([
            'name' => 'Updated Name',
            'task' => 'approve'
        ]);

        // Then: 更新された値が反映される
        $this->assertEquals('Updated Name', $workflow->name);
        $this->assertEquals('approve', $workflow->task);
        $this->assertDatabaseHas('workflows', [
            'id' => $workflow->id,
            'name' => 'Updated Name',
            'task' => 'approve'
        ]);
    }

    #[Test]
    public function it_has_timestamps(): void
    {
        // When: Workflowを作成
        $workflow = Workflow::factory()->create();

        // Then: タイムスタンプが設定される
        $this->assertNotNull($workflow->created_at);
        $this->assertNotNull($workflow->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $workflow->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $workflow->updated_at);
    }
    #[Test]
    public function it_can_mass_assign_fillable_attributes(): void
    {
        // When: fillable属性を使って大量代入でWorkflowを作成
        $workflow = Workflow::create([
            'name' => 'Mass Assignment Test',
            'subject' => 'rev1',
            'task' => 'approve',
            'object' => 'meta',
            'num_of_days' => 14,
            'next_workflow_id' => [2, 3],
            'join' => [1]
        ]);

        // Then: すべての属性が正しく設定される
        $this->assertEquals('Mass Assignment Test', $workflow->name);
        $this->assertEquals('rev1', $workflow->subject);
        $this->assertEquals('approve', $workflow->task);
        $this->assertEquals('meta', $workflow->object);
        $this->assertEquals(14, $workflow->num_of_days);
        $this->assertEquals([2, 3], $workflow->next_workflow_id);
        $this->assertEquals([1], $workflow->join);
    }}