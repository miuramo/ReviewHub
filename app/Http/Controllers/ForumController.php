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
     * 自分の任期年度内に作成されたフォーラムのみ表示する。
     */
    public function index()
    {
        $user = auth()->user();

        // 任期を1件以上持つことを前提に、アクセス可能フォーラムを取得
        $forums = Forum::accessible_for($user)
            ->with(['user', 'post'])
            ->orderByDesc('created_at')
            ->get();

        return view('forum.index', compact('forums'));
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

        $posts = Post::orderBy('id')->get();
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

        $forum->load(['messages.user', 'user', 'post']);
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
            'mes' => 'required|string|min:1',
        ]);

        ForumMes::create([
            'forum_id' => $forum->id,
            'user_id'  => $user->id,
            'subject'  => $req->input('sub', ''),
            'mes'      => $req->input('mes'),
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
}
