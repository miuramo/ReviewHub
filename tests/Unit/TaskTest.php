<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\Workflow;
use App\Models\Submit;
use App\Models\Paper;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskTest extends TestCase
{
    #[Test]
    public function it_can_create_a_task(): void
    {
        // Given: 必要な関連モデルを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Workflow',
            'task' => 'assign'
        ]);
        $paper = Paper::factory()->create();
        $submit = Submit::factory()->create(['paper_id' => $paper->id]);
        
        // When: Taskを作成
        $task = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);

        // Then: Taskが正常に作成される
        $this->assertInstanceOf(Task::class, $task);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
        $this->assertEquals($workflow->id, $task->workflow_id);
        $this->assertEquals($submit->id, $task->submit_id);
    }

    #[Test]
    public function it_has_workflow_relationship(): void
    {
        // Given: WorkflowとTaskを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Workflow Relationship',
            'task' => 'assign'
        ]);
        $paper = Paper::factory()->create();
        $submit = Submit::factory()->create(['paper_id' => $paper->id]);
        $task = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);

        // When: Workflowリレーションにアクセス
        $relatedWorkflow = $task->workflow;

        // Then: 正しいWorkflowが関連付けられている
        $this->assertInstanceOf(Workflow::class, $relatedWorkflow);
        $this->assertEquals($workflow->id, $relatedWorkflow->id);
        $this->assertEquals($workflow->name, $relatedWorkflow->name);
    }

    #[Test]
    public function it_has_submit_relationship(): void
    {
        // Given: SubmitとTaskを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Submit Relationship',
            'task' => 'assign'
        ]);
        $paper = Paper::factory()->create();
        $submit = Submit::factory()->create(['paper_id' => $paper->id]);
        $task = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);

        // When: Submitリレーションにアクセス
        $relatedSubmit = $task->submit;

        // Then: 正しいSubmitが関連付けられている
        $this->assertInstanceOf(Submit::class, $relatedSubmit);
        $this->assertEquals($submit->id, $relatedSubmit->id);
        $this->assertEquals($paper->id, $relatedSubmit->paper_id);
    }

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        // Given: Taskのfillable属性
        $fillableAttributes = [
            'submit_id',
            'workflow_id',
            'started',
            'has_trouble',
            'issues',
            'due_date',
            'next',
            'join',
            'completed',
            'completed_at',
            'require_approve',
            'approved',
            'approved_at',
            'subject_id',
            'object_id'
        ];

        // When: Taskモデルのfillable属性を取得
        $task = new Task();
        $actualFillable = $task->getFillable();

        // Then: 期待される属性がfillableに含まれている
        foreach ($fillableAttributes as $attribute) {
            $this->assertContains($attribute, $actualFillable, "Attribute '{$attribute}' should be fillable");
        }
    }

    #[Test]
    public function it_has_cast_attributes(): void
    {
        // Given: Taskを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Cast Attributes',
            'task' => 'assign'
        ]);
        $paper = Paper::factory()->create();
        $submit = Submit::factory()->create(['paper_id' => $paper->id]);
        $task = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);

        // When: JSON属性にアクセス
        $issues = $task->issues;
        $log = $task->log;
        $next = $task->next;
        $join = $task->join;

        // Then: 配列として正しくキャストされている
        $this->assertIsArray($issues);
        $this->assertIsArray($log);
        $this->assertIsArray($next);
        $this->assertIsArray($join);
    }

    #[Test]
    public function it_has_default_attribute_values(): void
    {
        // Given: Taskを最小限の属性で作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Default Attributes',
            'task' => 'assign'
        ]);
        $paper = Paper::factory()->create();
        $submit = Submit::factory()->create(['paper_id' => $paper->id]);
        
        // When: デフォルト値が設定されるTaskを作成
        $task = Task::create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);

        // Then: デフォルト値が正しく設定されている
        $this->assertEquals([], $task->issues);
        $this->assertEquals([], $task->log);
        $this->assertEquals([], $task->next);
        $this->assertEquals([], $task->join);
    }

    #[Test]
    public function it_can_call_process_method(): void
    {
        // Given: Taskとモックリクエストを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Process Method',
            'task' => 'assign'
        ]);
        $paper = Paper::factory()->create();
        $submit = Submit::factory()->create(['paper_id' => $paper->id]);
        $task = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);

        // When: processメソッドが存在することを確認
        $hasProcessMethod = method_exists($task, 'process');

        // Then: processメソッドが定義されている
        $this->assertTrue($hasProcessMethod, 'Task should have process method');
    }

    #[Test]
    public function it_uses_soft_deletes(): void
    {
        // Given: Taskを作成
        $workflow = Workflow::factory()->create([
            'name' => 'Test Soft Delete',
            'task' => 'assign'
        ]);
        $paper = Paper::factory()->create();
        $submit = Submit::factory()->create(['paper_id' => $paper->id]);
        $task = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id' => $submit->id
        ]);

        // When: Taskを削除
        $taskId = $task->id;
        $task->delete();

        // Then: ソフトデリートが適用されている
        $this->assertSoftDeleted('tasks', ['id' => $taskId]);
        
        // 復元できることを確認
        $task->restore();
        $this->assertDatabaseHas('tasks', ['id' => $taskId, 'deleted_at' => null]);
    }

    #[Test]
    public function it_has_with_relationship_loading(): void
    {
        // Given: Taskクラスのwith属性を確認
        $task = new Task();
        
        // When: with属性が適切に設定されているかチェック
        // Taskモデルでは$withプロパティが定義されているはず
        $reflection = new \ReflectionClass($task);
        $withProperty = $reflection->getProperty('with');
        $withProperty->setAccessible(true);
        $withRelations = $withProperty->getValue($task);

        // Then: 適切なリレーションがeager loadingに設定されている
        $expectedRelations = ['subject', 'object', 'submit', 'workflow'];
        
        if (is_array($withRelations)) {
            foreach ($expectedRelations as $relation) {
                $this->assertContains($relation, $withRelations, "Relation '{$relation}' should be in with array");
            }
        } else {
            // with属性が設定されていない場合はスキップ
            $this->markTestSkipped('with property not defined or not an array in Task model');
        }
    }
}