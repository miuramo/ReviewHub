<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'title',
        'isclose',
    ];

    protected $casts = [
        'isclose' => 'boolean',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function messages()
    {
        return $this->hasMany(ForumMes::class, 'forum_id');
    }

    // ─── 年度ヘルパー ──────────────────────────────────────────

    /**
     * 日本の年度（4月始まり）で Forum の作成年度を返す。
     * created_at が 4月〜12月 → その年、1月〜3月 → 前年。
     */
    public function fiscal_year(): int
    {
        $month = $this->created_at->month;
        $year  = $this->created_at->year;
        return $month >= 4 ? $year : $year - 1;
    }

    /**
     * 指定ユーザが「Forum.created_at の年度内に有効な任期を持ち、かつ
     * フォーラムの post.rank 以上の rank の任期を持つか」を判定する。
     */
    public function can_access(User $user): bool
    {
        $fy        = $this->fiscal_year();
        $forumRank = $this->post->rank ?? 0;

        return Term::where('user_id', $user->id)
            ->where('year', $fy)
            ->where('valid', true)
            ->whereHas('post', fn ($q) => $q->where('rank', '>=', $forumRank))
            ->exists();
    }

    /**
     * 認証中のユーザが閲覧・書き込み可能な Forum のクエリスコープ。
     * 「任期年度が一致する AND ユーザの任期 rank >= フォーラムの post.rank」の条件を満たすものを返す。
     */
    public static function accessible_for(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $terms = Term::where('user_id', $user->id)
            ->where('valid', true)
            ->with('post')
            ->get();

        if ($terms->isEmpty()) {
            return static::query()->whereRaw('0 = 1');
        }

        return static::query()->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $fy   = $term->year;
                $rank = $term->post->rank ?? 0;
                $q->orWhere(function ($inner) use ($fy, $rank) {
                    $inner->whereBetween('created_at', [
                        "{$fy}-04-01 00:00:00",
                        ($fy + 1) . "-03-31 23:59:59",
                    ])->whereHas('post', fn ($pq) => $pq->where('rank', '<=', $rank));
                });
            }
        });
    }

    /**
     * フォーラムの URL を返す。
     */
    public function url(): string
    {
        return route('forum.show', ['forum' => $this->id]);
    }
}
