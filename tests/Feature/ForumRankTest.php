<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\Post;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 役職 rank に基づくフォーラムアクセス制御のテスト。
 *
 * 権限階層: 編集委員(rank=1) < 幹事(rank=2) < 編集長(rank=3)
 * - rank N のユーザは rank <= N のフォーラムのみ作成・閲覧・書き込み可。
 */
class ForumRankTest extends TestCase
{
    // ─── ヘルパー ──────────────────────────────────────────────────────────────

    /** 指定役職名・任期年度でユーザを作成して返す。 */
    private function userWithPost(string $postName, int $year = 2026): User
    {
        $user = User::factory()->create();
        $post = Post::where('name', $postName)->firstOrFail();
        Term::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'year'    => $year,
            'valid'   => true,
        ]);
        return $user;
    }

    /** 指定役職名・日付でフォーラムを作成して返す。 */
    private function forumForPost(string $postName, string $createdAt = '2026-06-20'): Forum
    {
        $post  = Post::where('name', $postName)->firstOrFail();
        $owner = User::factory()->create();
        return Forum::factory()->createdAt($createdAt)->create([
            'post_id' => $post->id,
            'user_id' => $owner->id,
        ]);
    }

    // ─── Forum::can_access() の rank テスト ───────────────────────────────────

    /**
     * 編集委員(rank=1)は編集委員用フォーラム(rank=1)にアクセスできる。
     */
    public function test_cm_can_access_cm_forum(): void
    {
        $forum = $this->forumForPost('編集委員');
        $user  = $this->userWithPost('編集委員');
        $this->assertTrue($forum->can_access($user));
    }

    /**
     * 編集委員(rank=1)は幹事用フォーラム(rank=2)にアクセスできない。
     */
    public function test_cm_cannot_access_aec_forum(): void
    {
        $forum = $this->forumForPost('幹事');
        $user  = $this->userWithPost('編集委員');
        $this->assertFalse($forum->can_access($user));
    }

    /**
     * 編集委員(rank=1)は編集長用フォーラム(rank=3)にアクセスできない。
     */
    public function test_cm_cannot_access_ec_forum(): void
    {
        $forum = $this->forumForPost('編集長');
        $user  = $this->userWithPost('編集委員');
        $this->assertFalse($forum->can_access($user));
    }

    /**
     * 幹事(rank=2)は編集委員用フォーラム(rank=1)にアクセスできる。
     */
    public function test_aec_can_access_cm_forum(): void
    {
        $forum = $this->forumForPost('編集委員');
        $user  = $this->userWithPost('幹事');
        $this->assertTrue($forum->can_access($user));
    }

    /**
     * 幹事(rank=2)は幹事用フォーラム(rank=2)にアクセスできる。
     */
    public function test_aec_can_access_aec_forum(): void
    {
        $forum = $this->forumForPost('幹事');
        $user  = $this->userWithPost('幹事');
        $this->assertTrue($forum->can_access($user));
    }

    /**
     * 幹事(rank=2)は編集長用フォーラム(rank=3)にアクセスできない。
     */
    public function test_aec_cannot_access_ec_forum(): void
    {
        $forum = $this->forumForPost('編集長');
        $user  = $this->userWithPost('幹事');
        $this->assertFalse($forum->can_access($user));
    }

    /**
     * 編集長(rank=3)はすべてのフォーラムにアクセスできる。
     */
    public function test_ec_can_access_all_forums(): void
    {
        $user = $this->userWithPost('編集長');
        $this->assertTrue($this->forumForPost('編集委員')->can_access($user));
        $this->assertTrue($this->forumForPost('幹事')->can_access($user));
        $this->assertTrue($this->forumForPost('編集長')->can_access($user));
    }

    // ─── Forum::accessible_for() の rank テスト ──────────────────────────────

    /**
     * 編集委員は accessible_for で編集委員用フォーラムのみ取得できる。
     */
    public function test_accessible_for_returns_only_cm_forums_for_cm_user(): void
    {
        $user     = $this->userWithPost('編集委員');
        $cmForum  = $this->forumForPost('編集委員');
        $aecForum = $this->forumForPost('幹事');
        $ecForum  = $this->forumForPost('編集長');

        $ids = Forum::accessible_for($user)->pluck('id');

        $this->assertContains($cmForum->id, $ids);
        $this->assertNotContains($aecForum->id, $ids);
        $this->assertNotContains($ecForum->id, $ids);
    }

    /**
     * 幹事は accessible_for で編集委員用・幹事用フォーラムを取得できる。
     */
    public function test_accessible_for_returns_cm_and_aec_forums_for_aec_user(): void
    {
        $user     = $this->userWithPost('幹事');
        $cmForum  = $this->forumForPost('編集委員');
        $aecForum = $this->forumForPost('幹事');
        $ecForum  = $this->forumForPost('編集長');

        $ids = Forum::accessible_for($user)->pluck('id');

        $this->assertContains($cmForum->id, $ids);
        $this->assertContains($aecForum->id, $ids);
        $this->assertNotContains($ecForum->id, $ids);
    }

    /**
     * 編集長は accessible_for ですべてのフォーラムを取得できる。
     */
    public function test_accessible_for_returns_all_forums_for_ec_user(): void
    {
        $user     = $this->userWithPost('編集長');
        $cmForum  = $this->forumForPost('編集委員');
        $aecForum = $this->forumForPost('幹事');
        $ecForum  = $this->forumForPost('編集長');

        $ids = Forum::accessible_for($user)->pluck('id');

        $this->assertContains($cmForum->id, $ids);
        $this->assertContains($aecForum->id, $ids);
        $this->assertContains($ecForum->id, $ids);
    }

    // ─── HTTP アクセス制御テスト ─────────────────────────────────────────────

    /**
     * 編集委員は幹事用フォーラムを GET すると 403。
     */
    public function test_cm_user_gets_403_on_aec_forum_show(): void
    {
        $forum = $this->forumForPost('幹事');
        $user  = $this->userWithPost('編集委員');

        $this->actingAs($user)->get(route('forum.show', $forum))->assertStatus(403);
    }

    /**
     * 幹事は編集委員用フォーラムを閲覧できる（200）。
     */
    public function test_aec_user_can_view_cm_forum(): void
    {
        $forum = $this->forumForPost('編集委員');
        $user  = $this->userWithPost('幹事');

        $this->actingAs($user)->get(route('forum.show', $forum))->assertStatus(200);
    }

    /**
     * 編集委員は幹事用フォーラムにメッセージを投稿できず 403。
     */
    public function test_cm_user_cannot_post_to_aec_forum(): void
    {
        $forum = $this->forumForPost('幹事');
        $user  = $this->userWithPost('編集委員');

        $this->actingAs($user)
            ->post(route('forum.mes.store', $forum), ['mes' => 'テスト'])
            ->assertStatus(403);
    }

    // ─── フォーラム作成の rank 制限テスト ────────────────────────────────────

    /**
     * 編集委員は幹事用フォーラムを作成できず 403。
     */
    public function test_cm_user_cannot_create_aec_forum(): void
    {
        $user    = $this->userWithPost('編集委員');
        $aecPost = Post::where('name', '幹事')->first();

        $this->actingAs($user)
            ->post(route('forum.store'), ['title' => 'テスト', 'post_id' => $aecPost->id])
            ->assertStatus(403);
    }

    /**
     * 幹事は編集委員用フォーラムを作成できる。
     */
    public function test_aec_user_can_create_cm_forum(): void
    {
        $user   = $this->userWithPost('幹事');
        $cmPost = Post::where('name', '編集委員')->first();

        $this->actingAs($user)
            ->post(route('forum.store'), ['title' => '幹事が作る編集委員フォーラム', 'post_id' => $cmPost->id])
            ->assertStatus(302)
            ->assertSessionHas('feedback.success');
    }

    /**
     * 幹事は編集長用フォーラムを作成できず 403。
     */
    public function test_aec_user_cannot_create_ec_forum(): void
    {
        $user   = $this->userWithPost('幹事');
        $ecPost = Post::where('name', '編集長')->first();

        $this->actingAs($user)
            ->post(route('forum.store'), ['title' => 'テスト', 'post_id' => $ecPost->id])
            ->assertStatus(403);
    }

    // ─── index のグループ表示テスト ───────────────────────────────────────────

    /**
     * index 画面で編集委員は編集委員用フォーラムのみ表示される。
     */
    public function test_index_shows_only_cm_section_for_cm_user(): void
    {
        $user     = $this->userWithPost('編集委員');
        $cmForum  = $this->forumForPost('編集委員');
        $aecForum = $this->forumForPost('幹事');

        $response = $this->actingAs($user)->get(route('forum.index'));
        $response->assertStatus(200);
        $response->assertSee($cmForum->title);
        $response->assertDontSee($aecForum->title);
    }

    /**
     * index 画面で幹事は編集委員・幹事の両セクションが表示され、編集長用は表示されない。
     */
    public function test_index_shows_cm_and_aec_sections_for_aec_user(): void
    {
        $user     = $this->userWithPost('幹事');
        $cmForum  = $this->forumForPost('編集委員');
        $aecForum = $this->forumForPost('幹事');
        $ecForum  = $this->forumForPost('編集長');

        $response = $this->actingAs($user)->get(route('forum.index'));
        $response->assertStatus(200);
        $response->assertSee($cmForum->title);
        $response->assertSee($aecForum->title);
        $response->assertDontSee($ecForum->title);
    }

    /**
     * index 画面で編集長はすべての役職セクション見出しが表示される。
     */
    public function test_index_shows_all_sections_for_ec_user(): void
    {
        $user = $this->userWithPost('編集長');

        $response = $this->actingAs($user)->get(route('forum.index'));
        $response->assertStatus(200);
        $response->assertSee('編集委員 のフォーラム');
        $response->assertSee('幹事 のフォーラム');
        $response->assertSee('編集長 のフォーラム');
    }
}
