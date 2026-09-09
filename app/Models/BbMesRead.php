<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbMesRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'bb_mes_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(BbMes::class, 'bb_mes_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 既存のメッセージに対して、既読レコードを新規作成する。
     */
    public static function init()
    {
        \App\Models\BbMes::with(['bb.paper', 'bb.review'])
            ->whereHas('bb', fn($query) => $query->whereIn('type', [1, 2]))
            ->chunkById(100, function ($messages) {
                foreach ($messages as $message) {
                    $message->createReadRecords();
                }
            });
    }

    /**
     * ログアクセスに基づいて、既読レコードを修正（更新）する。read_at フィールドを更新する。
     */
    public static function markAsRead_byLogAccess(): void
    {
        $logs = LogAccess::query()
            ->where('method', 'GET')
            ->where('uid', '>', 0)
            ->whereNotNull('created_at')
            ->where('url', 'like', '/bb/%')
            ->orderBy('created_at')
            ->get(['uid', 'url', 'created_at']);

        foreach ($logs as $log) {
            if (!preg_match('/\/bb\/(\d+)(?:\/|$)/', $log->url, $matches)) {
                continue;
            }

            $bbId = (int) $matches[1];
            $bb = Bb::with('messages')->find($bbId);
            if (!$bb || !in_array((int) $bb->type, [1, 2], true)) {
                continue;
            }

            $userId = (int) $log->uid;
            $messages = $bb->messages()
                ->where('created_at', '<=', $log->created_at)
                ->get();

            foreach ($messages as $message) {
                $readRecord = self::query()
                    ->where('bb_mes_id', $message->id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$readRecord) {
                    continue;
                }

                if ($readRecord->read_at === null || $readRecord->read_at->gt($log->created_at)) {
                    $readRecord->read_at = $log->created_at;
                    $readRecord->save();
                }
            }
        }
    }
}
