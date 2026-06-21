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
        'parent_id',
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

    public function parent()
    {
        return $this->belongsTo(ForumMes::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ForumMes::class, 'parent_id')->orderBy('created_at');
    }
}
