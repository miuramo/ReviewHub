<?php

namespace Tests\Unit;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Post モデルの rank フィールドに関するユニットテスト。
 */
class PostRankTest extends TestCase
{
    // ─── rank フィールドの基本テスト ──────────────────────────────────────────

    /**
     * PostSeeder が作成した3役職に正しい rank が設定されている。
     */
    public function test_seeded_posts_have_correct_ranks(): void
    {
        $cm  = Post::where('name', '編集委員')->first();
        $aec = Post::where('name', '幹事')->first();
        $ec  = Post::where('name', '編集長')->first();

        $this->assertNotNull($cm);
        $this->assertNotNull($aec);
        $this->assertNotNull($ec);

        $this->assertSame(1, $cm->rank);
        $this->assertSame(2, $aec->rank);
        $this->assertSame(3, $ec->rank);
    }

    /**
     * rank は 編集委員 < 幹事 < 編集長 の順序関係を持つ。
     */
    public function test_rank_ordering_is_correct(): void
    {
        $cm  = Post::where('name', '編集委員')->first();
        $aec = Post::where('name', '幹事')->first();
        $ec  = Post::where('name', '編集長')->first();

        $this->assertLessThan($aec->rank, $cm->rank);
        $this->assertLessThan($ec->rank, $aec->rank);
    }

    /**
     * rank が fillable に含まれており mass assignment で保存できる。
     */
    public function test_rank_is_fillable(): void
    {
        $post = Post::create([
            'name'  => 'テスト役職',
            'rank'  => 5,
        ]);

        $this->assertDatabaseHas('posts', ['name' => 'テスト役職', 'rank' => 5]);
    }

    /**
     * rank が未指定の場合はデフォルト値 0 になる。
     */
    public function test_rank_defaults_to_zero(): void
    {
        $post = Post::create(['name' => 'ランクなし役職']);

        $this->assertSame(0, (int) $post->fresh()->rank);
    }
}
