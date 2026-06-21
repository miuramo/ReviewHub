<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumMes;
use App\Models\Post;
use App\Models\Term;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    /**
     * アクセス可能なフォーラム一覧を表示。
     * 役職種別（編集委員用・幹事用・編集長用）ごとにグループ化して渡す。
     */
    public function index()
    {
        $user = auth()->user();

        $forums = Forum::accessible_for($user)
            ->with(['user', 'post', 'messages'])
            ->orderByDesc('created_at')
            ->get();

        $userMaxRank     = $this->user_max_rank($user);
        $accessiblePosts = Post::where('rank', '<=', $userMaxRank)
            ->orderBy('rank')
            ->get();
        $groupedForums   = $forums->groupBy('post_id');

        return view('forum.index', compact('groupedForums', 'accessiblePosts'));
    }

    /**
     * フォーラム作成フォームを表示。
     * 現在有効な任期を持つユーザのみ作成可。
     */
    public function create()
    {
        $user = auth()->user();
        if (!$this->has_any_valid_term($user)) {
            abort(403, '有効な任期がないため、フォーラムを作成できません。');
        }

        $userMaxRank = $this->user_max_rank($user);        $posts = Post::where('rank', '<=', $userMaxRank)->orderBy('rank')->get();
        return view('forum.create', compact('posts'));
    }

    /**
     * フォーラムを保存する。
     */
    public function store(Request $req)
    {
        $user = auth()->user();
        if (!$this->has_any_valid_term($user)) {
            abort(403, '有効な任期がないため、フォーラムを作成できません。');
        }

        $req->validate([
            'title'   => 'required|string|max:255',
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        // 指定役職の rank がユーザの最大 rank 以下か確認
        $post        = Post::findOrFail($req->input('post_id'));
        $userMaxRank = $this->user_max_rank($user);
        if ($post->rank > $userMaxRank) {
            abort(403, 'この役職のフォーラムを作成する権限がありません。');
        }

        $forum = Forum::create([
            'post_id' => $req->input('post_id'),
            'user_id' => $user->id,
            'title'   => $req->input('title'),
            'isclose' => false,
        ]);

        // 最初のシステムメッセージ
        ForumMes::create([
            'forum_id' => $forum->id,
            'user_id'  => 0,
            'subject'  => 'ごあんない',
            'mes'      => "こちらは「{$forum->post->name}」のフォーラムです。\n作成年度: {$forum->fiscal_year()}年度",
        ]);

        return redirect()->route('forum.show', ['forum' => $forum->id])
            ->with('feedback.success', 'フォーラムを作成しました。');
    }

    /**
     * フォーラムを表示する。
     * Forum.created_at の年度内に任期を持つユーザのみ閲覧可。
     */
    public function show(Forum $forum)
    {
        $user = auth()->user();
        if (!$forum->can_access($user)) {
            abort(403, 'このフォーラムを閲覧する権限がありません。（任期年度が一致しません）');
        }

        $forum->load([
            'messages' => fn ($q) => $q->whereNull('parent_id')->orderBy('created_at'),
            'messages.user',
            'messages.replies.user',
            'messages.replies.replies.user',
            'messages.replies.replies.replies.user',
            'user',
            'post',
        ]);
        return view('forum.show', compact('forum'));
    }

    /**
     * フォーラムにメッセージを投稿する。
     * Forum.created_at の年度内に任期を持つユーザのみ書き込み可。
     */
    public function storeMes(Request $req, Forum $forum)
    {
        $user = auth()->user();
        if (!$forum->can_access($user)) {
            abort(403, 'このフォーラムに書き込む権限がありません。（任期年度が一致しません）');
        }

        if ($forum->isclose) {
            return redirect()->route('forum.show', ['forum' => $forum->id])
                ->with('feedback.error', 'このフォーラムは締め切られています。');
        }

        $req->validate([
            'mes'       => 'required|string|min:1',
            'parent_id' => 'nullable|integer|exists:forum_mes,id',
        ]);

        ForumMes::create([
            'forum_id'  => $forum->id,
            'parent_id' => $req->input('parent_id'),
            'user_id'   => $user->id,
            'subject'   => $req->input('sub', ''),
            'mes'       => $req->input('mes'),
        ]);

        return redirect()->route('forum.show', ['forum' => $forum->id])
            ->with('feedback.success', '書き込みました。');
    }

    /**
     * フォーラムを削除する（管理者のみ）。
     */
    public function destroy(Forum $forum)
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) {
            abort(403);
        }

        ForumMes::where('forum_id', $forum->id)->delete();
        $forum->delete();

        return redirect()->route('forum.index')
            ->with('feedback.success', 'フォーラムを削除しました。');
    }

    // ─── Private helpers ──────────────────────────────────────

    /**
     * ユーザが少なくとも1件の有効な任期を持っているか確認する。
     */
    private function has_any_valid_term(\App\Models\User $user): bool
    {
        return Term::where('user_id', $user->id)
            ->where('valid', true)
            ->exists();
    }

    /**
     * ユーザが持つ有効な任期の役職 rank の最大値を返す。
     */
    private function user_max_rank(\App\Models\User $user): int
    {
        return (int) Term::where('user_id', $user->id)
            ->where('valid', true)
            ->join('posts', 'terms.post_id', '=', 'posts.id')
            ->max('posts.rank');
    }
}
