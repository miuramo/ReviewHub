<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbMesReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bb_mes_id',
        'user_id',
        'emoji',
    ];

    public function message()
    {
        return $this->belongsTo(BbMes::class, 'bb_mes_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
