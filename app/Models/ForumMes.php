<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumMes extends Model
{
    use HasFactory;

    protected $table = 'forum_mes';

    protected $fillable = [
        'forum_id',
        'user_id',
        'subject',
        'mes',
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class, 'forum_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
