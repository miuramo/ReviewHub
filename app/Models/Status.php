<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    /** @use HasFactory<\Database\Factories\StatusFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * 著者にみえる論文のステータス
     * 1: 投稿準備中
     * 2: 投稿完了
     * (3: メタ割り当て中)
     * 4: 査読者割り当て中
     * 5: 査読中
     * (6: 査読修正中)
     * (7: 査読完了)
     * 8: 編集委員会判定中
     * 9: 査読結果通知済み
     * このうち、
     * 現状が2で、査読者が1名以上割り当てられていたら、4にする
     * 現状が4で、一人でも査読者が査読を開始していたら、5にする
     * 現状が5で、2名以上の査読者が査読を完了していたら、8にする
     * 
     * 呼び出す場所 PaperController@index, PaperController@manage
     */
    public static function updatePaperStatus(Submit $sub): void
    {
        $paper = $sub->paper;
        if ($paper->status_id === 2) {
            if ($sub->reviews->count() > 0) {
                $paper->status_id = 4;
                $paper->save();
            }
        } elseif ($paper->status_id === 4) {
            if ($sub->tasks->count() > 0) {
                $paper->status_id = 5;
                $paper->save();
            }
        } elseif ($paper->status_id === 5) {
            $count = 0;
            foreach ($sub->reviews as $review) {
                if ($review->status === 2) {
                    $count++;
                }
            }
            if ($count >= 2) {
                $paper->status_id = 8;
                // info('status_idを8に変更しました');
                $paper->save();
            }
        } else {
            // info('status_idを変更しませんでした');
        }
    }
}
