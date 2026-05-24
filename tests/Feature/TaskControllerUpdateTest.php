<?php

namespace Tests\Feature;

use App\Models\Paper;
use App\Models\Review;
use App\Models\Submit;
use App\Models\Task;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TaskController::update のフィーチャーテスト
 *
 * テスト対象のロジック:
 *  - $task->process($req) を呼び、ワークフローを進める
 *  - workflow->task == "submit" のとき掲示板(Bb)にメッセージを投稿してリダイレクト
 *  - それ以外のワークフローのとき 'Task completed successfully' でリダイレクト
 *  - process() が false を返したとき 'タスク処理に失敗しました' でリダイレクト
 *  - redirect_role から数字 1, 2, 3 を除去してリダイレクト先ロール名とする
 */
class TaskControllerUpdateTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // メールのキュー送信を防ぐ
        Queue::fake();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // ---------------------------------------------------------------
    // ヘルパー: submit ワークフロー用のデータをまとめて生成する
    // ---------------------------------------------------------------
    private function makeSubmitWorkflowData(): array
    {
        $workflow = Workflow::factory()->create(['task' => 'submit', 'object' => 'ec']);
        $paper    = Paper::factory()->create();
        $submit   = Submit::factory()->create(['paper_id' => $paper->id]);
        $task     = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id'   => $submit->id,
            'subject_id'  => $this->user->id,
            'object_id'   => $this->user->id,
            'join'        => [],
            'next'        => [],
        ]);
        // Bb::add_message() 内で Review を参照するため、対応する査読レコードを作成
        $review = Review::create([
            'submit_id'   => $submit->id,
            'paper_id'    => $paper->id,
            'user_id'     => $this->user->id,
            'category_id' => 1,
            'target'      => 0,
        ]);

        return compact('workflow', 'paper', 'submit', 'task', 'review');
    }

    // ---------------------------------------------------------------
    // テスト: submit ワークフロー成功 → 成功フラッシュでリダイレクト
    // ---------------------------------------------------------------

    #[Test]
    public function update_with_submit_workflow_redirects_with_review_thanks_message(): void
    {
        ['task' => $task, 'review' => $review] = $this->makeSubmitWorkflowData();

        $response = $this->put(route('task.update', $task), [
            'redirect_role' => 'rev',
            'rev_id'        => $review->id,
        ]);

        $response->assertRedirect(route('role.top', ['role' => 'rev']));
        $response->assertSessionHas('feedback.success', '査読へのご協力ありがとうございました。');
    }

    #[Test]
    public function update_with_submit_workflow_marks_task_completed_in_db(): void
    {
        ['task' => $task, 'review' => $review] = $this->makeSubmitWorkflowData();

        $this->put(route('task.update', $task), [
            'redirect_role' => 'rev',
            'rev_id'        => $review->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'completed' => 1,
        ]);
    }

    #[Test]
    public function update_with_submit_workflow_creates_bb_message(): void
    {
        ['submit' => $submit, 'task' => $task, 'review' => $review] = $this->makeSubmitWorkflowData();

        $this->put(route('task.update', $task), [
            'redirect_role' => 'rev',
            'rev_id'        => $review->id,
        ]);

        // Bb::add_message() が呼ばれ、掲示板メッセージが作成されること
        $this->assertDatabaseHas('bbs', [
            'submit_id' => $submit->id,
            'type'      => 2,
            'rev_id'    => $review->id,
        ]);
        $this->assertDatabaseHas('bb_mes', [
            'subject' => '査読完了の報告',
        ]);
    }

    // ---------------------------------------------------------------
    // テスト: 非 submit ワークフロー（assign）成功 → 'Task completed successfully'
    // ---------------------------------------------------------------

    #[Test]
    public function update_with_assign_workflow_redirects_with_task_completed_message(): void
    {
        // object='ec' にすることで assign_forward() が何もしない状態にする
        $workflow = Workflow::factory()->create(['task' => 'assign', 'object' => 'ec']);
        $paper    = Paper::factory()->create();
        $submit   = Submit::factory()->create(['paper_id' => $paper->id]);
        $task     = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id'   => $submit->id,
            'subject_id'  => $this->user->id,
            'join'        => [],
            'next'        => [],
        ]);

        $response = $this->put(route('task.update', $task), [
            'redirect_role' => 'admin',
            'object_id'     => $this->user->id,
        ]);

        $response->assertRedirect(route('role.top', ['role' => 'admin']));
        $response->assertSessionHas('feedback.success', 'Task completed successfully');
    }

    #[Test]
    public function update_with_assign_workflow_marks_task_completed_in_db(): void
    {
        $workflow = Workflow::factory()->create(['task' => 'assign', 'object' => 'ec']);
        $paper    = Paper::factory()->create();
        $submit   = Submit::factory()->create(['paper_id' => $paper->id]);
        $task     = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id'   => $submit->id,
            'subject_id'  => $this->user->id,
            'join'        => [],
            'next'        => [],
        ]);

        $this->put(route('task.update', $task), [
            'redirect_role' => 'admin',
            'object_id'     => $this->user->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'completed' => 1,
        ]);
    }

    // ---------------------------------------------------------------
    // テスト: join タスクが未承認 → process() false → エラーリダイレクト
    // ---------------------------------------------------------------

    #[Test]
    public function update_fails_and_redirects_with_error_when_join_task_not_approved(): void
    {
        $workflow = Workflow::factory()->create(['task' => 'assign', 'object' => 'ec']);
        $paper    = Paper::factory()->create();
        $submit   = Submit::factory()->create(['paper_id' => $paper->id]);

        // 未承認の join タスク
        $joinTask = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id'   => $submit->id,
            'approved'    => 0,
        ]);

        // join に未承認タスクを含むメインタスク
        $task = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id'   => $submit->id,
            'subject_id'  => $this->user->id,
            'join'        => [$joinTask->id],
            'next'        => [],
        ]);

        $response = $this->put(route('task.update', $task), [
            'redirect_role' => 'admin',
        ]);

        $response->assertRedirect(route('role.top', ['role' => 'admin']));
        $response->assertSessionHas('feedback.error', 'タスク処理に失敗しました');
    }

    #[Test]
    public function update_does_not_complete_task_when_join_not_approved(): void
    {
        $workflow = Workflow::factory()->create(['task' => 'assign', 'object' => 'ec']);
        $paper    = Paper::factory()->create();
        $submit   = Submit::factory()->create(['paper_id' => $paper->id]);

        $joinTask = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id'   => $submit->id,
            'approved'    => 0,
        ]);
        $task = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id'   => $submit->id,
            'subject_id'  => $this->user->id,
            'join'        => [$joinTask->id],
            'next'        => [],
        ]);

        $this->put(route('task.update', $task), [
            'redirect_role' => 'admin',
        ]);

        // 失敗時は completed が変わらないこと
        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'completed' => 0,
        ]);
    }

    // ---------------------------------------------------------------
    // テスト: redirect_role の数字 1, 2, 3 が除去される
    // ---------------------------------------------------------------

    #[Test]
    public function update_strips_digits_1_2_3_from_redirect_role(): void
    {
        ['task' => $task, 'review' => $review] = $this->makeSubmitWorkflowData();

        // 'rev1' → '1' が除去されて 'rev' になる
        $response = $this->put(route('task.update', $task), [
            'redirect_role' => 'rev1',
            'rev_id'        => $review->id,
        ]);

        $response->assertRedirect(route('role.top', ['role' => 'rev']));
    }

    #[Test]
    public function update_strips_multiple_digits_from_redirect_role(): void
    {
        // assign ワークフローで redirect_role のみ確認（Bb不要）
        $workflow = Workflow::factory()->create(['task' => 'assign', 'object' => 'ec']);
        $paper    = Paper::factory()->create();
        $submit   = Submit::factory()->create(['paper_id' => $paper->id]);
        $task     = Task::factory()->create([
            'workflow_id' => $workflow->id,
            'submit_id'   => $submit->id,
            'subject_id'  => $this->user->id,
            'join'        => [],
            'next'        => [],
        ]);

        // 'admin123' → 1,2,3 が除去されて 'admin' になる
        $response = $this->put(route('task.update', $task), [
            'redirect_role' => 'admin123',
            'object_id'     => $this->user->id,
        ]);

        $response->assertRedirect(route('role.top', ['role' => 'admin']));
    }
}
