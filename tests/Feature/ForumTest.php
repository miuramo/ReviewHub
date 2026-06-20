<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\ForumMes;
use App\Models\Post;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ForumController + Forum モデルのテスト。
 * 主に「Forum.created_at の年度内に有効な任期(Term)を持つユーザのみ閲覧・書き込み可」という
 * アクセス制御の確認を行う。
 */
class ForumTest extends TestCase
{
    // ─── ヘルパー ──────────────────────────────────────────────────────────────

    /**
     * ユーザと任期（Term）をセットで作成して返す。
     *
     * @param int $year 任期年度
     * @return User
     */
    private function userWithTerm(int $year): User
    {
        $user = User::factory()->create();
        $post = Post::first(); // DatabaseSeeder が作成する役職を利用
        Term::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'year'    => $year,
            'valid'   => true,
        ]);
        return $user;
    }

    /**
     * 任期を持たないユーザを作成して返す。
     */
    private function userWithoutTerm(): User
    {
        return User::factory()->create();
    }

    /**
     * 指定した日付で作成されたフォーラムを返す。
     *
     * @param string $createdAt 'YYYY-MM-DD' 形式
     */
    private function forumCreatedAt(string $createdAt): Forum
    {
        $post = Post::first();
        $owner = User::factory()->create();
        /** @var Forum $forum */
        $forum = Forum::factory()->createdAt($createdAt)->create([
            'post_id' => $post->id,
            'user_id' => $owner->id,
            'title'   => "テストフォーラム ({$createdAt})",
        ]);
        return $forum;
    }

    // ─── 年度計算のユニットテスト ──────────────────────────────────────────────

    /**
     * 4月〜12月の Forum は同じ年の年度として扱われる。
     */
    public function test_fiscal_year_is_same_year_for_april_to_december(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20');
        $this->assertSame(2026, $forum->fiscal_year());

        $forum2 = $this->forumCreatedAt('2026-04-01');
        $this->assertSame(2026, $forum2->fiscal_year());

        $forum3 = $this->forumCreatedAt('2026-12-31');
        $this->assertSame(2026, $forum3->fiscal_year());
    }

    /**
     * 1月〜3月の Forum は前の年の年度として扱われる。
     */
    public function test_fiscal_year_is_previous_year_for_january_to_march(): void
    {
        $forum = $this->forumCreatedAt('2026-01-15');
        $this->assertSame(2025, $forum->fiscal_year());

        $forum2 = $this->forumCreatedAt('2026-03-31');
        $this->assertSame(2025, $forum2->fiscal_year());
    }

    // ─── can_access() のユニットテスト ────────────────────────────────────────

    /**
     * Forum.created_at の年度に一致する任期を持つユーザは can_access == true。
     */
    public function test_can_access_returns_true_for_user_with_matching_term(): void
    {
        // 2026-06-20 → fiscal year 2026
        $forum = $this->forumCreatedAt('2026-06-20');
        $user  = $this->userWithTerm(2026);

        $this->assertTrue($forum->can_access($user));
    }

    /**
     * 任期を持たないユーザは can_access == false。
     */
    public function test_can_access_returns_false_for_user_without_term(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20');
        $user  = $this->userWithoutTerm();

        $this->assertFalse($forum->can_access($user));
    }

    /**
     * 異なる年度の任期しか持たないユーザは can_access == false。
     */
    public function test_can_access_returns_false_for_user_with_different_year_term(): void
    {
        // フォーラムは 2026 年度
        $forum = $this->forumCreatedAt('2026-06-20');
        // ユーザの任期は 2025 年度
        $user  = $this->userWithTerm(2025);

        $this->assertFalse($forum->can_access($user));
    }

    /**
     * valid=false の任期は権限なし扱い。
     */
    public function test_can_access_returns_false_for_invalid_term(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20');
        $user  = User::factory()->create();
        $post  = Post::first();

        // Term::$fillable に valid が含まれないため直接プロパティ代入で保存する
        $term = new Term();
        $term->user_id = $user->id;
        $term->post_id = $post->id;
        $term->year    = 2026;
        $term->valid   = false; // 無効な任期
        $term->save();

        $this->assertFalse($forum->can_access($user));
    }

    /**
     * 1月のフォーラム(前年度扱い)に対して、前年度の任期で can_access == true になる。
     */
    public function test_can_access_for_january_forum_uses_previous_fiscal_year(): void
    {
        // 2026-02-10 → fiscal year 2025
        $forum = $this->forumCreatedAt('2026-02-10');
        $user  = $this->userWithTerm(2025); // 2025年度の任期

        $this->assertTrue($forum->can_access($user));
    }

    // ─── HTTP ルートのアクセス制御テスト ─────────────────────────────────────

    /**
     * 認証されていないユーザはログイン画面にリダイレクトされる。
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20');

        $this->get(route('forum.index'))->assertRedirect(route('login'));
        $this->get(route('forum.show', $forum))->assertRedirect(route('login'));
    }

    /**
     * 任期年度が一致するユーザはフォーラムを閲覧できる（200）。
     */
    public function test_user_with_matching_term_can_view_forum(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20');
        $user  = $this->userWithTerm(2026);

        $response = $this->actingAs($user)->get(route('forum.show', $forum));
        $response->assertStatus(200);
        $response->assertSee($forum->title);
    }

    /**
     * 任期を持たないユーザはフォーラム閲覧で 403 になる。
     */
    public function test_user_without_term_cannot_view_forum(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20');
        $user  = $this->userWithoutTerm();

        $response = $this->actingAs($user)->get(route('forum.show', $forum));
        $response->assertStatus(403);
    }

    /**
     * 年度が異なる任期しか持たないユーザはフォーラム閲覧で 403 になる。
     */
    public function test_user_with_different_year_term_cannot_view_forum(): void
    {
        // フォーラム: 2026年度
        $forum = $this->forumCreatedAt('2026-06-20');
        // ユーザ: 2025年度の任期しか持たない
        $user  = $this->userWithTerm(2025);

        $response = $this->actingAs($user)->get(route('forum.show', $forum));
        $response->assertStatus(403);
    }

    /**
     * index() は自分がアクセス可能なフォーラムだけを返す。
     */
    public function test_index_shows_only_accessible_forums(): void
    {
        // ユーザは 2026 年度の任期
        $user = $this->userWithTerm(2026);

        $forum2026 = $this->forumCreatedAt('2026-06-20'); // 2026年度 → アクセス可
        $forum2025 = $this->forumCreatedAt('2025-09-01'); // 2025年度 → アクセス不可

        $response = $this->actingAs($user)->get(route('forum.index'));
        $response->assertStatus(200);
        $response->assertSee($forum2026->title);
        $response->assertDontSee($forum2025->title);
    }

    // ─── メッセージ投稿のテスト ────────────────────────────────────────────────

    /**
     * 任期年度が一致するユーザはメッセージを投稿できる。
     */
    public function test_user_with_matching_term_can_post_message(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20');
        $user  = $this->userWithTerm(2026);

        $response = $this->actingAs($user)->post(
            route('forum.mes.store', $forum),
            ['sub' => 'テスト件名', 'mes' => 'テストメッセージ本文']
        );

        $response->assertRedirect(route('forum.show', $forum));
        $response->assertSessionHas('feedback.success');

        $this->assertDatabaseHas('forum_mes', [
            'forum_id' => $forum->id,
            'user_id'  => $user->id,
            'subject'  => 'テスト件名',
            'mes'      => 'テストメッセージ本文',
        ]);
    }

    /**
     * 任期がないユーザはメッセージを投稿できず 403。
     */
    public function test_user_without_term_cannot_post_message(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20');
        $user  = $this->userWithoutTerm();

        $response = $this->actingAs($user)->post(
            route('forum.mes.store', $forum),
            ['sub' => '件名', 'mes' => 'メッセージ']
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('forum_mes', [
            'forum_id' => $forum->id,
            'user_id'  => $user->id,
        ]);
    }

    /**
     * 年度が異なるユーザはメッセージを投稿できず 403。
     */
    public function test_user_with_different_year_cannot_post_message(): void
    {
        $forum = $this->forumCreatedAt('2026-06-20'); // 2026年度
        $user  = $this->userWithTerm(2025);           // 2025年度の任期

        $response = $this->actingAs($user)->post(
            route('forum.mes.store', $forum),
            ['sub' => '件名', 'mes' => 'メッセージ']
        );

        $response->assertStatus(403);
    }

    /**
     * 締め切り済みのフォーラムにはメッセージを投稿できない。
     */
    public function test_cannot_post_to_closed_forum(): void
    {
        $post  = Post::first();
        $owner = User::factory()->create();
        $forum = Forum::factory()->createdAt('2026-06-20')->create([
            'post_id' => $post->id,
            'user_id' => $owner->id,
            'isclose' => true,
        ]);

        $user = $this->userWithTerm(2026);

        $response = $this->actingAs($user)->post(
            route('forum.mes.store', $forum),
            ['sub' => '件名', 'mes' => 'メッセージ']
        );

        $response->assertRedirect(route('forum.show', $forum));
        $response->assertSessionHas('feedback.error');
        $this->assertDatabaseMissing('forum_mes', [
            'forum_id' => $forum->id,
            'user_id'  => $user->id,
        ]);
    }

    // ─── フォーラム作成のテスト ────────────────────────────────────────────────

    /**
     * 有効な任期を持つユーザはフォーラムを作成できる。
     */
    public function test_user_with_valid_term_can_create_forum(): void
    {
        $user = $this->userWithTerm(2026);
        $post = Post::first();

        $response = $this->actingAs($user)->post(
            route('forum.store'),
            ['title' => '新しいフォーラム', 'post_id' => $post->id]
        );

        $response->assertStatus(302);
        $response->assertSessionHas('feedback.success');

        $this->assertDatabaseHas('forums', [
            'user_id' => $user->id,
            'title'   => '新しいフォーラム',
            'post_id' => $post->id,
        ]);
    }

    /**
     * 任期を持たないユーザはフォーラムを作成できず 403。
     */
    public function test_user_without_term_cannot_create_forum(): void
    {
        $user = $this->userWithoutTerm();
        $post = Post::first();

        $response = $this->actingAs($user)->post(
            route('forum.store'),
            ['title' => '新しいフォーラム', 'post_id' => $post->id]
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('forums', ['user_id' => $user->id]);
    }

    // ─── 削除のテスト ─────────────────────────────────────────────────────────

    /**
     * 管理者はフォーラムを削除できる。
     */
    public function test_admin_can_delete_forum(): void
    {
        // DatabaseSeeder が作成した最初のユーザは admin ロールを持つ
        $admin = User::find(1);
        $forum = $this->forumCreatedAt('2026-06-20');

        $response = $this->actingAs($admin)->delete(route('forum.destroy', $forum));

        $response->assertRedirect(route('forum.index'));
        $response->assertSessionHas('feedback.success');

        $this->assertDatabaseMissing('forums', ['id' => $forum->id]);
    }

    /**
     * 管理者でないユーザはフォーラムを削除できず 403。
     */
    public function test_non_admin_cannot_delete_forum(): void
    {
        $user  = $this->userWithTerm(2026); // 任期はあるが管理者でない
        $forum = $this->forumCreatedAt('2026-06-20');

        $response = $this->actingAs($user)->delete(route('forum.destroy', $forum));

        $response->assertStatus(403);
        $this->assertDatabaseHas('forums', ['id' => $forum->id]);
    }
}
