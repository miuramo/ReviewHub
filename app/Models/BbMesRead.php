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
}
