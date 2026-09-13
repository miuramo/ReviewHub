<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumMesReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_mes_id',
        'user_id',
        'emoji',
    ];

    public function message()
    {
        return $this->belongsTo(ForumMes::class, 'forum_mes_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
