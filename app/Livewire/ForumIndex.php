<?php

namespace App\Livewire;

use App\Models\Forum;
use App\Models\Post;
use App\Models\Term;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

class ForumIndex extends Component
{
    public string $keyword = '';

    /** 役職ごとの現在ページ番号。post_id => int */
    public array $pageNumbers = [];

    protected int $perPage = 5;

    public function updatingKeyword(): void
    {
        $this->pageNumbers = [];
    }

    public function gotoPage(int $postId, int $page): void
    {
        $this->pageNumbers[$postId] = max(1, $page);
    }

    private function buildQuery(\App\Models\User $user): \Illuminate\Database\Eloquent\Builder
    {
        $query = Forum::accessible_for($user)
            ->with(['user', 'post', 'messages'])
            ->orderByDesc('created_at');

        $kw = trim($this->keyword);
        if ($kw !== '') {
            $query->where(function ($q) use ($kw) {
                $q->where('title', 'like', '%' . $kw . '%')
                  ->orWhereHas('messages', function ($mq) use ($kw) {
                      $mq->where('mes', 'like', '%' . $kw . '%')
                         ->orWhere('subject', 'like', '%' . $kw . '%');
                  });
            });
        }

        return $query;
    }

    public function render(): \Illuminate\View\View
    {
        $user = auth()->user();

        $userMaxRank = (int) Term::where('user_id', $user->id)
            ->where('valid', true)
            ->join('posts', 'terms.post_id', '=', 'posts.id')
            ->max('posts.rank');

        $accessiblePosts = Post::where('rank', '<=', $userMaxRank)
            ->orderBy('rank')
            ->get();

        /** @var array<int, LengthAwarePaginator> $paginatedByPost */
        $paginatedByPost = [];
        foreach ($accessiblePosts as $post) {
            $page = $this->pageNumbers[$post->id] ?? 1;
            $paginatedByPost[$post->id] = $this->buildQuery($user)
                ->where('post_id', $post->id)
                ->paginate($this->perPage, ['*'], "post_{$post->id}", $page);
        }

        return view('livewire.forum-index', [
            'accessiblePosts' => $accessiblePosts,
            'paginatedByPost' => $paginatedByPost,
        ]);
    }
}
