<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbMes extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bb_id',
        'subject',
        'mes',
    ];

    public function bb()
    {
        return $this->belongsTo(Bb::class, 'bb_id');
    }
    public function files()
    {
        return $this->hasMany(File::class, 'bb_mes_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reads()
    {
        return $this->hasMany(BbMesRead::class, 'bb_mes_id');
    }

    public function recipient_id(): ?int
    {
        $bb = $this->bb()->with(['paper', 'review'])->first();
        if (!$bb || !in_array((int) $bb->type, [1, 2], true)) {
            return null;
        }
        // 基本の受信者を取得する
        $recipientId = $bb->recipient_id();
        if ($recipientId === auth()->id()) { //自分自身が書いたメッセージなら
            $recipientId = $bb->paper?->aec_id || 1; // 受信者をaec_idに変更
        }
        if ($recipientId === null) {
            $recipientId = 1;
        }
        return $recipientId;
    }

    protected static function booted(): void
    {
        static::created(function (BbMes $message) {
            broadcast(new \App\Events\BbMesPosted($message->bb_id));
        });
    }

    public function createReadRecords(): void
    {
        $bb = $this->bb()->with(['paper', 'review'])->first();
        if (!$bb || !in_array((int) $bb->type, [1, 2], true)) {
            return;
        }

        $userIds = collect([(int) $this->user_id])->filter(fn ($id) => $id > 0);
        // $userIds = collect();
        if ($bb->type === 1) {
            $userIds = $userIds
                ->merge([$bb->paper?->owner])
                ->merge([$bb->paper?->aec_id]);
        } else {
            $userIds = $userIds->merge([$bb->review?->user_id])
                ->merge([$bb->paper?->aec_id || 1]);
        }

        foreach ($userIds->filter()->unique() as $userId) {
            BbMesRead::firstOrCreate([
                'bb_mes_id' => $this->id,
                'user_id' => $userId,
            ]);
        }
    }

    public function markReadBy(int $userId): int
    {
        return $this->reads()->where('user_id', $userId)->whereNull('read_at')->update([
            'read_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
