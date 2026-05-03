<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'viewpoint_id',
        'user_id',
        'value',
        'valuestr',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
    public function viewpoint()
    {
        return $this->belongsTo(Viewpoint::class);
    }

    public function submit_score_update(): void
    {
        // 点数に関係なければ終了
        if ($this->viewpoint->weight < 1) {
            $this->review->validateOneRev();
            return;
        }
        if ($this->review == null) return;
        // 対応するSubmitは、review_id -> Review
        $sub_id = $this->review->submit_id;
        if ($sub_id == null) return;
        $sub = Submit::find($sub_id);
        $sub->updateScoreStat();
    }

    /**
     * 問題ありそうだったので、Submitで作成した。
     */
    public static function updateAllScoreStat(): void
    {
        Submit::updateAllScoreStat();

        // $all = Score::whereHas('viewpoint', function ($query) {
        //     $query->where('weight', 1);
        // })->get();
        // foreach ($all as $sc) {
        //     $sc->submit_score_update();
        // }
    }
}
