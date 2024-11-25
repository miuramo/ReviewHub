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
        $task = Task::where('submit_id', $this->id)->where('workflow_id', $workflow->id)->where('approved',1)->first();
        // Taskが存在しない場合は、割り当てられていない
        if ($task == null) return false;
        // Taskが存在する場合は、object_idが設定されているかどうかを返す
        return true;
    }

    public function updateStatus()
    {
        if ($this->rev1()->user_id != null && $this->rev2()->user_id != null) {
            $this->paper->status_id = 5;
            $this->paper->save();
        } else if ($this->meta()->user_id != null) {
            $this->paper->status_id = 4;
            $this->paper->save();
        }
    }

    public function init_reviews()
    {
        $revs = Review::factory(1)->create([
            'submit_id' => $this->id,
            'category_id' => $this->category_id,
            'paper_id' => $this->paper_id,
            'target' => 2,
        ]);
        $revs = Review::factory(1)->create([
            'submit_id' => $this->id,
            'category_id' => $this->category_id,
            'paper_id' => $this->paper_id,
            'target' => 1,
        ]);
        $revs = Review::factory(2)->create([
            'submit_id' => $this->id,
            'category_id' => $this->category_id,
            'paper_id' => $this->paper_id,
            'target' => 0,
        ]);
    }

    /**
     * デフォルトのワークフローから、タスク群を生成する
     */
    public function newTasks()
    {
        $numtasks = Task::where('submit_id', $this->id)->count();
        if ($numtasks == 0) Workflow::createTasks($this);
    }

    public function heads(){
        $fs = ['resubmit_until', 'submitted_at', 'review_until', 'ec_decision_at', 'notify_at'];
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
        return sha1($this->id . $this->paper_id . $this->category_id. $this->created_at);
    }

    public static function subs_accepted(int $cat_id, string $ord = "orderint")
    {
        $subs = Submit::with('paper')->where("category_id", $cat_id)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->orderBy($ord)->get();
        return $subs;
    }
    public static function subs_all(int $cat_id, string $ord = "orderint")
    {
        $subs = Submit::with('paper')->where("category_id", $cat_id)->whereNot("paper_id",0)->orderBy($ord)->get();
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
}
