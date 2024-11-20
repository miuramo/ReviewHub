<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'submit_id',
        'workflow_id',
        'due_date',
        'subject_id',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function subject()
    {
        return $this->belongsTo(User::class, 'subject_id');
    }
    public function submit()
    {
        return $this->belongsTo(Submit::class);
    }

    public function dueForHumans($prefix = 'あと', $postfix = '超過')
    {
        $date = $this->due_date; 
        try {
            // 現在の日付を取得
            $currentDate = new DateTime();
            // 指定された日付を DateTime オブジェクトに変換
            $givenDate = new DateTime($date);
            // 差分を計算
            $difference = $currentDate->diff($givenDate);
            // 差分の日数を返す（符号を考慮）
            if ($difference->days >= 0) {
                return $prefix.$difference->days . '日';
            } else {
                return $difference->days . '日'.$postfix;
            }
        } catch (Exception $e) {
            // 不正な日付形式の場合はエラーメッセージを返す
            return "Invalid date format: $date";
        }
    }
}
