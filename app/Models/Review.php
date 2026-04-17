<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Review extends MetaModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'submit_id',
        'paper_id',
        'user_id',
        'category_id',
        'target',
        'status',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }
    public function scores()
    {
        return $this->hasMany(Score::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function submit()
    {
        return $this->belongsTo(Submit::class, 'submit_id');
    }

    public function save_user_id($user_id)
    {
        $this->user_id = $user_id;
        $this->save();
    }

    public function task()
    {
        return $this->hasOne(Task::class, 'submit_id', 'submit_id')->where('subject_id', $this->user_id);
    }

    /**
     * この査読のトークンを生成（査読者同士の参照用）
     */
    public function token()
    {
        return sha1($this->id . $this->user_id . $this->paper_id . $this->category_id);
    }
    /**
     * 査読依頼→回答時のトークン
     */
    public function token_for_request()
    {
        return sha1($this->id . $this->user_id . $this->paper_id . $this->category_id . "request" . $this->status);
    }

    /**
     * メールから承諾リンクを押してくれた
     */
    public function do_accept()
    {
        // もし、すでに査読者2名開始している場合は、ペンディングにする（メールを送らない）
        $count = Review::where('paper_id', $this->paper_id)
            ->where('submit_id', $this->submit_id)
            ->where('target', $this->target)
            ->where('status', '>', 0) // 開始前ではない。
            ->count();
        if ($count >= 2) {
            return false; // すでに2名以上の査読者がいるので、何もしない
        }
        $this->status = 1; // 承諾状態にする
        $this->save();
        return true;
    }


    /**
     * 査読開始（内諾が得られた！＆まだ人数が2名未満）→ タスクを作る（念の為査読者確認フェーズにはいる）
     */
    public function do_assign()
    {
        $paper = Paper::with('currentSubmit')->find($this->paper_id);
        $revuid = $this->user_id;
        // タスクがないか確認
        $task = Task::where('submit_id', $paper->currentSubmit->id)
            ->where('subject_id', $revuid)
            ->first();
        if ($task) {
            // 既にタスクがある場合は、何もしない
            return false;
        }
        $task = Task::createReviewTask($paper->currentSubmit, $revuid);
        if ($this->target == 2) {
            $task->due_date = $task->addDaysToDate(5); // 最終判定は5日
        } else if ($this->target == 0) {
            $task->due_date = $task->addDaysToDate(24); // 通常査読は24日
        } else { // case of 1
            $task->due_date = $task->addDaysToDate(10); // 現在は使用していないが、メタの場合は、10日
        }
        $task->save();

        $conftitle = Setting::getval('CONFTITLE');
        if ($this->target == 2) {
            Bb::add_message(
                $paper->currentSubmit,
                2,
                "【{$conftitle}】最終判定のお願い",
                "{$this->user->affil}  {$this->user->name}様\n\nお忙しいところすみませんが、査読結果が揃いましたので、確認および最終判定をお願いいたします。\n\n以下のURLから、確認してください。\n" . env('APP_URL') . "/role/rev/top",
                $this->id,
            );
        } else {
            // Bb::add_message(
            //     $paper->currentSubmit,
            //     2,
            //     "【{$conftitle}】PDFのダウンロードについて",
            //     "{$this->user->affil}  {$this->user->name}様\n\n査読のご承諾ありがとうございました。\n\n". 
            //     "以下のURL（ログインが要求されます）を開いていただき、「査読を開始する」ボタンを押していただくと、PDFがダウンロードできるようになります。\n [" . 
            //     env('APP_URL') . "/role/rev/top](".env('APP_URL')."/role/rev/top)". 
            //     "\n\n\n 投稿管理者との連絡は、以下の「掲示板をひらく」ボタンをお使いください。\n\n". 
            //     "ご不明な点がありましたら、掲示板からご連絡ください。\n\n" .
            //     "よろしくお願いいたします。\n\n" ,
            //     $this->id,
            // );
        }
        return true;
    }

    public function message_accept_by_maillink()
    {
        $message = "査読をお引き受けいただき、ありがとうございます。<br>" .
            "早速ではありますが、査読を開始させていただきます。<br>" .
            "査読の案内をメールでお送りしますので、ご確認ください。<br>" .
            "（届かない場合は迷惑メールフォルダもご確認ください。）<br><br>" .
            "◆ ログイン方法について（※初回／不明な場合のみ）：<br>" .
            "以下の手順にしたがって、査読システムのパスワードを設定してください。<br>" .
            "(1) [:URL_FORGETPASS:] にて、<b>[:EMAIL:]</b> を入力してください。<br>" .
            "しばらくすると、パスワード再設定メールがとどきます。<br>" .
            "(2) パスワード再設定メールに書かれたURLから、パスワードを設定してください。<br>" .
            "<br>" .
            "◆ PDFのダウンロードと、査読の方法について：<br>" .
            "査読システム [:APP_URL:] をブラウザで開いてください。<br>" .
            "ログイン後、画面上部の「査読」をおしてください。その後、「査読を開始する」をおしてください。<br>" .
            "<br>" .
            "引き続き、どうぞよろしくお願いいたします。";
        $message = str_replace(
            ["[:URL_FORGETPASS:]", "[:EMAIL:]", "[:APP_URL:]"],
            [
                "<a class=\"underline hover:bg-lime-200 text-blue-500\" target=\"_blank\" href=\"" . route("password.request") . "\">" . route("password.request") . "</a>",
                $this->user->email,
                "<a class=\"underline hover:bg-lime-200 text-blue-500\" target=\"_blank\" href=\"" . config('app.url') . "\">" . config('app.url') . "</a>"
            ],
            $message
        );
        return $message;
    }


    /**
     * 査読割り当て
     * status 3が最終判定 2がメタ 1が通常 0以下が解除
     */
    public static function review_assign($submit_id, $user_id, $ismeta2)
    {
        $ismeta2 = intval($ismeta2);
        $submit = Submit::find($submit_id);
        if ($ismeta2 > 0) {
            DB::transaction(function () use ($submit, $user_id, $ismeta2) {
                // 既存のデータがあれば、それを読み取って修正する
                $rev = Review::where('user_id', $user_id)->where('submit_id', $submit->id)->first();
                if ($rev != null) {
                    $rev->submit_id = $submit->id;
                    $rev->category_id = $submit->category_id;
                    $rev->target = $ismeta2 - 1;
                    $rev->save();
                } else {
                    Review::firstOrCreate([
                        'submit_id' => $submit->id,
                        'paper_id' => $submit->paper->id,
                        'user_id' => $user_id,
                        'category_id' => $submit->category_id,
                        'target' => $ismeta2 - 1,
                        'status' => 0, // 開始前
                    ]);
                }
            });
            // Paperをロックする（最初に1回やればよいが、査読者割り当て時に行う。）
            $paper = Paper::find($submit->paper_id);
            $paper->lockAll(true);
        } else {
            $dat = Review::where([['user_id', $user_id], ['submit_id', $submit_id]])->get();
            foreach ($dat as $r) {
                $r->delete();
            }
        }
    }

    /**
     * 数をしらべる。( field = paper_id or user_id )
     */
    public static function revass_stat($catid, $field = "user_id")
    {
        $tmp = Review::select(DB::raw("count(id) as count, {$field}, target"))
            ->where('category_id', $catid)
            ->groupBy($field)
            ->groupBy("target")
            ->orderBy($field)
            ->get();
        $ret = [];
        foreach ($tmp as $n => $t) {
            $ret[$t->{$field}][$t->target] = $t->count;
        }
        return $ret;
    }
    public static function revass_stat_allcategory()
    {
        $field = "user_id";
        $tmp = Review::select(DB::raw("count(id) as count, {$field}, target"))
            ->groupBy($field)
            ->groupBy("target")
            ->orderBy($field)
            ->get();
        $ret = [];
        foreach ($tmp as $n => $t) {
            $ret[$t->{$field}][$t->target] = $t->count;
        }
        return $ret;
    }

    /**
     * ネストした配列で返す
     * arr[paper_id][user_id] = rev
     */
    public static function arr_pu_rev()
    {
        $ret = [];
        foreach (Review::all() as $a) {
            $ret[$a->paper_id][$a->user_id] = $a;
        }
        return $ret;
    }
    /**
     * ネストした配列で返す
     * arr[paper_id][user_id] = status(2:meta 1:normal)
     */
    public static function arr_pu_status()
    {
        $ret = [];
        foreach (Review::all() as $a) {
            $ret[$a->paper_id][$a->user_id] = $a->target + 1;
        }
        return $ret;
    }
    /**
     * ネストした配列で返す
     * arr[paper_id][user_id] = star span
     */
    public static function arr_pu_star()
    {
        $ret = [];
        $colors = ["teal", "cyan", "red"];
        foreach (Review::all() as $a) {
            $status = $a->target + 1;
            $span = "<span class=\"text-2xl text-{$colors[$status]}-500\">★</span>";
            $ret[$a->paper_id][$a->user_id] = $span;
        }
        return $ret;
    }

    /**
     * 査読者名を取得する
     * ret[paper_id][target][user_id] = name
     */
    public static function arr_pu_revname()
    {
        $ret = [];
        foreach (Review::all() as $a) {
            $ret[$a->paper_id][$a->target][$a->user_id] = $a->user->name;
        }
        return $ret;
    }

    /**
     * 査読割り当ての前に、全査読者の利害を抽出する
     */
    public static function extractAllCoAuthorRigais()
    {
        // 査読者とメタ査読者
        $roles = Role::where("name", "like", "%rev")->get();
        foreach ($roles as $role) {
            foreach ($role->users as $revu) {
                // 自著分、共著分については、さきにRevConflictを作成しておく
                $author_papers = Paper::where("owner", $revu->id)->get();
                foreach ($author_papers as $p) {
                    $revcon = RevConflict::firstOrCreate([
                        'user_id' => $revu->id,
                        'paper_id' => $p->id,
                        'bidding_id' => 1, // 1が共著者利害
                    ]);
                }
                $user = User::find($revu->id);
                foreach ($user->coauthor_papers() as $p) {
                    $revcon = RevConflict::firstOrCreate([
                        'user_id' => $revu->id,
                        'paper_id' => $p->id,
                        'bidding_id' => 1, // 1が共著者利害
                    ]);
                }
            }
        }
    }

    // status 0は未回答、1は回答中、2は完了 を更新する
    public function validateOneRev()
    {
        $finish_vpids_ary = Score::where('review_id', $this->id)->whereNotNull('valuestr')->whereHas('viewpoint', function ($query) {
            $query->where('mandatory', 1);
        })->get()->pluck('viewpoint_id')->toArray();
        $finish_vpids = count($finish_vpids_ary);
        $all_vpids = Viewpoint::where('category_id', $this->category_id)->whereRaw("target & ? != 0", [$this->target + 1])->where('mandatory', 1)->pluck('id')->toArray();
        if ($finish_vpids == 0) {
            $this->status = 0;
        } else if ($finish_vpids == count($all_vpids)) {
            // 厳密には、全ての必須項目が埋まっているかどうかをチェックするべき
            sort($finish_vpids_ary);
            sort($all_vpids);
            $answered = serialize($finish_vpids_ary);
            $expected = serialize($all_vpids);
            if ($answered == $expected) {
                $this->status = 2;
            } else {
                $this->status = 1;
            }
        } else {
            $this->status = 1;
        }
        $this->save();
    }

    /**
     * 未回答があると $rev->scores は抜けてしまうので、viewpoints をつかってKey->value として確実に配列で返す。
     * @param $only_score が 1のとき、number が含まれるものだけに限定する（通常はしないので0）
     * @param $accepted が 0のとき、doReturnAcceptOnly が 1のものは表示しない
     */
    public function scores_and_comments($only_doreturn = 1, $only_score = 0, $accepted = 1)
    {
        $aryscores = $this->scores->pluck("valuestr", "viewpoint_id")->toArray();
        // $vps = Viewpoint::where('category_id', $this->category_id)->orderBy('orderint')->get();
        $vps = Viewpoint::by_category_target($this->category_id, $this->target); //ここは単一の査読(Review)にもとづいているので、Review.targetのみでよい。（複数targetをループでまわす必要はない）
        $ret = [];
        foreach ($vps as $vp) {
            if ($only_doreturn && !$vp->doReturn) continue;
            if ($only_score && strpos($vp->content, "number") === false) continue;
            // Primaryじゃないとき(target=0)、forrev=0のときは表示しない
            // if ($this->target != $vp->target) continue; ここは関係ない。
            if (!$accepted && $vp->doReturnAcceptOnly) continue;

            $ret[$vp->desc] = (isset($aryscores[$vp->id])) ? $aryscores[$vp->id] : "(未入力)";
        }
        return $ret;
    }
    // 判定
    public function judge()
    {
        $ret = $this->scores_and_comments(1, 0, 0);
        return $ret['判定結果'] ?? $ret['措置'] ?? '??';
    }

    /**
     * txtに含まれるURLをリンクに変換する
     */
    public static function urllink($txt)
    {
        $txt = preg_replace_callback("/(<a [^>]+?>.+?<\/a>)|(https?:\/\/[a-zA-Z0-9_\.\/\~\%\:\#\?=&\;\-]+)/i", ["App\Models\Review", "urllink_callback"], $txt);
        $txt = strip_tags($txt, "<a>");
        return $txt;
    }

    public static function urllink_callback($match)
    {
        if ($match[1]) {
            // 最初から<a>タグで囲まれている場合
            if (preg_match('/<a .*?href *?= *\"(http[^\"]+?)\"[^>]*?>(.+?)<\/a>/i', $match[1], $matches)) {
                //  <a>タグの href属性が http から始まっている場合（javascript対策）
                return sprintf(
                    '<a class="text-blue-600 hover:underline" href="%1$s" target="_blank">%2$s</a>',
                    htmlspecialchars($matches[1]),
                    htmlspecialchars($matches[2]),
                );
            } else {
                //  <a>タグの href属性が http から始まっていない場合はエスケープして出力
                return htmlspecialchars($match[1]);
            }
        } elseif ($match[2]) {
            // <a>タグで囲まれていないけど http://～ から始まっている場合
            return sprintf(
                '<a class="text-blue-600 hover:underline" href="%1$s" target="_blank">%1$s</a>',
                htmlspecialchars($match[2]),
            );
        }
    }

    /**
     * すべてのstatusを更新する（査読未完了のチェックの前に実行する）
     */
    public static function validateAllRev()
    {
        $all = Review::all();
        foreach ($all as $rev) {
            $rev->validateOneRev();
        }
    }

    /**
     * 自分が入力したスコア一覧 (indexcatの下に表示するmyscoresで使用)
     * @param int $uid
     * @param int $cat_id
     * 
     * @return array
     * $ret['titles'] = $titles;
     * $ret['scores'] = $scores;
     * $ret['descs'] = $descs;
     */
    public static function my_scores($uid, $cat_id)
    {
        // review list
        $sql1 =
            'select reviews.id, paper_id, title from reviews left join papers on reviews.paper_id = papers.id where reviews.user_id = ' .
            $uid .
            " and reviews.category_id = $cat_id order by paper_id";
        $res1 = DB::select($sql1);
        $titles = [];
        foreach ($res1 as $res) {
            $titles[$res->paper_id] = $res->title;
        }
        $sql2 =
            'select paper_id, viewpoint_id, value, orderint, `desc` from scores ' .
            ' left join reviews on scores.review_id = reviews.id' .
            ' left join viewpoints on scores.viewpoint_id = viewpoints.id' .
            ' where reviews.user_id = ' .
            auth()->id() .
            " and reviews.category_id = $cat_id " .
            ' and value is not null order by paper_id, orderint';
        $res2 = DB::select($sql2);
        $scores = [];
        $descs = [];
        foreach ($res2 as $res) {
            $scores[$res->paper_id][$res->viewpoint_id] = $res->value;
            $descs[$res->viewpoint_id] = $res->desc;
        }
        $ret['titles'] = $titles;
        $ret['scores'] = $scores;
        $ret['descs'] = $descs;
        return $ret;
    }

    /**
     * あるPaperIDに対して、査読者のスコアを取得する
     * @param int $paper_id
     * @param int $cat_id
     * 
     */
    public static function get_scores($paper_id, $cat_id)
    {
        $sql1 =
            'select reviews.id, paper_id, title, name, affil, target, status from reviews ' .
            'left join papers on reviews.paper_id = papers.id ' .
            'left join users on reviews.user_id = users.id ' .
            'where reviews.paper_id = ' . $paper_id .
            " and reviews.category_id = $cat_id order by target desc, id";
        $res1 = DB::select($sql1);
        $names = [];
        $target = [];
        foreach ($res1 as $res) {
            $names[$res->id] = $res->name . " (" . $res->affil . ")";
            $target[$res->id] = $res->target;
        }
        $sql2 =
            'select scores.review_id, viewpoint_id, value, orderint, viewpoints.target, viewpoints.`desc` from scores ' .
            ' left join reviews on scores.review_id = reviews.id' .
            ' left join viewpoints on scores.viewpoint_id = viewpoints.id' .
            " where review_id in (select id from reviews where paper_id = {$paper_id}) " .
            " and reviews.category_id = $cat_id " .
            ' and value is not null order by orderint'; // ここのforrev desc で、先にforrevを表示する。
        $res2 = DB::select($sql2);
        $scores = [];
        $descs = [];
        foreach ($res2 as $res) {
            $scores[$res->review_id][$res->viewpoint_id] = $res->value;
            $descs[$res->viewpoint_id] = $res->desc;
        }
        $ret['names'] = $names;
        $ret['target'] = $target;
        $ret['scores'] = $scores;
        $ret['descs'] = $descs;
        return $ret;
    }

    public function heads()
    {
        // $fs = ['target','status','request_at','start_at','end_at','created_at','updated_at'];
        $fs = ['target', 'status', 'request_at', 'start_at', 'end_at'];
        // $fs に該当する、schema comment を取得
        $heads = [];
        $comments = $this->get_table_comments();
        foreach ($fs as $f) {
            $heads[$f] = $comments[$f] ?? $f;
        }
        return $heads;
    }
    public function deleteTask()
    {
        // この査読に関連するタスクを削除する
        $task = Task::where('submit_id', $this->submit_id)->where('subject_id', $this->user_id)->first();
        if ($task) {
            Task::destroy($task->id);
        }
    }
}
