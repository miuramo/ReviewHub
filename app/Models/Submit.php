<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submit extends MetaModel
{
    use HasFactory;

    protected $fillable = [
        'psessionid',
        'aec_id',
        'canceled',
        'orderint',
        'accept_id',
        'category_id',
        'paper_id',
    ];

    protected $with = ['aec'];

    public function accept()
    {
        return $this->belongsTo(Accept::class); //逆はbelongsTo
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, "submit_id")->orderBy('target', 'desc');
    }
    public function rejected_reviews()
    {
        $rev = Review::withTrashed()->where('submit_id', $this->id)->whereNotNull('deleted_at')->get();
        return $rev;
    }
    public function aec()
    {
        return $this->belongsTo(User::class, 'aec_id');
    }
    public function aecrep()
    {
        return $this->reviews()->where('target', 2)->first();
    }
    public function meta()
    {
        return $this->reviews()->where('target', 1)->first();
    }
    public function rev1()
    {
        return $this->reviews()->where('target', 0)->first();
    }
    public function rev2()
    {
        return $this->reviews()->where('target', 0)->skip(1)->first();
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function isAssigned(string $rollname)
    {
        // まずは、該当するWorkflowを取得
        $workflow = Workflow::where('object', $rollname)->first();
        // 次に、そのWorkflowに対応するTaskを取得
        $task = Task::where('submit_id', $this->id)->where('workflow_id', $workflow->id)->where('approved', 1)->first();
        // Taskが存在しない場合は、割り当てられていない
        if ($task == null) return false;
        // Taskが存在する場合は、object_idが設定されているかどうかを返す
        return true;
    }

    /**
     * 最終判定のresultを取得する
     */
    public function getReviewResult($key)
    {
        $vpid = Viewpoint::where('name', $key)->first()->id;
        $revids = $this->reviews->pluck('id');
        $score = Score::where('viewpoint_id', $vpid)->whereIn('review_id', $revids)->first();
        if ($score == null) return null;
        return $score->valuestr;
    }

    /**
     * 著者に帰る査読結果のURLを生成する
     * @return string
     */
    public function url_reviewresult_for_author()
    {
        return route('paper.review', ['sub' => $this->id, 'token' => $this->paper->token()]);
    }

    // public function updateStatus()
    // {
    // if ($this->rev1()->user_id != null && $this->rev2()->user_id != null) {
    //     $this->paper->status_id = 5;
    //     $this->paper->save();
    // } else if ($this->meta()->user_id != null) {
    //     $this->paper->status_id = 4;
    //     $this->paper->save();
    // }
    // }

    public function init_reviews()
    {
        // $revs = Review::factory(1)->create([
        //     'submit_id' => $this->id,
        //     'category_id' => $this->category_id,
        //     'paper_id' => $this->paper_id,
        //     'target' => 2,
        // ]);
        // $revs = Review::factory(1)->create([
        //     'submit_id' => $this->id,
        //     'category_id' => $this->category_id,
        //     'paper_id' => $this->paper_id,
        //     'target' => 1,
        // ]);
        // $revs = Review::factory(2)->create([
        //     'submit_id' => $this->id,
        //     'category_id' => $this->category_id,
        //     'paper_id' => $this->paper_id,
        //     'target' => 0,
        // ]);
    }

    /**
     * デフォルトのワークフローから、タスク群を生成する
     */
    public function newTasks()
    {
        $numtasks = Task::where('submit_id', $this->id)->count();
        if ($numtasks == 0) Workflow::createTasks($this);
    }

    public function heads()
    {
        $fs = ['resubmit_until', 'submitted_at', 'receiptsent_at', 'accept_id', 'ec_decision_at', 'notify_at'];
        // $fs に該当する、schema comment を取得
        $heads = [];
        $comments = $this->get_table_comments();
        foreach ($fs as $f) {
            $heads[$f] = $comments[$f] ?? $f;
        }
        return $heads;
    }
    /**
     * この査読のトークンを生成（査読者同士の参照用）
     */
    public function token()
    {
        return sha1($this->id . $this->paper_id . $this->category_id . $this->created_at);
    }

    public static function subs_accepted(int $cat_id, string $ord = "orderint")
    {
        $subs = Submit::with('paper')->where("category_id", $cat_id)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->orderBy($ord)->get();
        return $subs;
    }
    public static function subs_accepted_notpublished(array $cat_ids, bool $published = false, string $ord = "orderint")
    {
        $subs = Submit::with('paper')->whereIn("category_id", $cat_ids)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->whereHas("paper", function ($query) use ($published) {
            $query->where("published", $published);
        })->orderBy($ord)->get();
        return $subs;
    }
    public static function subs_all(int $cat_id, string $ord = "orderint")
    {
        $subs = Submit::with('paper')->where("category_id", $cat_id)->whereNot("paper_id", 0)->orderBy($ord)->get();
        return $subs;
    }

    /**
     * このSubmitに関連するReviewの点数を更新する
     */
    public function updateScoreStat()
    {
        // まず、このSubmitに関連するReviewを取得
        $scores = Score::whereHas('viewpoint', function ($query) {
            $query->where('weight', 1);
        })->whereIn('review_id', $this->reviews->pluck('id'))->pluck('value')->toArray();
        $sum = array_sum($scores);
        if (count($scores) > 0) {
            $mean = $sum / count($scores);
            $this->score = $mean;
            $this->stddevscore = sqrt(array_sum(array_map(function ($value) use ($mean) {
                return pow($value - $mean, 2);
            }, $scores)) / count($scores));
        } else {
            $this->score = null;
            $this->stddevscore = null;
        }
        $this->save();
    }

    /**
     * すべてのSubmitの点数統計(score, stddevscore)を更新する
     */
    public static function updateAllScoreStat()
    {
        $subs = Submit::all();
        foreach ($subs as $sub) {
            $sub->updateScoreStat();
        }
    }

    public function updateCurrentDecision()
    {
        $result = $this->getReviewResult("result");
        if (strpos($result, "条件付き") !== false) {
            $this->accept_id = 2; //条件付き
        } elseif (strpos($result, "採") === 0) {
            $this->accept_id = 1; //採録
        } elseif (strpos($result, "不") === 0) {
            $this->accept_id = 6; //不採録
        } elseif (strpos($result, "取り下げ") === 0) {
            $this->accept_id = 7; //取り下げ（勝手に増やさない。不採録+1にしている理由は、statusの11不採録,12取り下げに合わせるため）
        }
        $this->save();
    }

    public function setDecision()
    {
        define('STATUS_ACCEPTED', 10); // TODO: 定数を適切な場所に移動、設定を読み込むかDBで「採録」がある行から取得する

        $this->updateCurrentDecision();
        $this->paper->lockAll(true); // これまでのファイルはロックする。
        $this->paper->archiveAll(true);
        if ($this->accept_id == 2) { // 条件付きの場合
            // そのうえで、新しいファイルをアップロード可能にする(false=Paperロック解除)
            $this->paper->lockMe(false);
            $this->paper->status_id = 9; //査読結果通知済み
            $this->paper->save();
        } else if ($this->accept_id == 1) { // 採録の場合
            // そのうえで、新しいファイルをアップロード可能にする(false=Paperロック解除)
            $this->paper->lockMe(false);
            $this->paper->status_id = STATUS_ACCEPTED; //採録決定
            $this->paper->save();
        } else { // 不採録の場合
            $this->paper->lockMe(true);
            $this->paper->status_id = 9; //査読結果通知済み
            $this->paper->save();
        }
        $this->ec_decision_at = now();
        $this->save();
    }
}
