<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'isclose',
    ];

    public function items()
    {
        return $this->hasMany(VoteItem::class, 'vote_id');
    }


    public static function init($isclose = 0): void
    {
        Vote::firstOrCreate(
            [
                'name' => '優秀論文賞 2026 (29巻)',
            ],
            [
                'isclose' => $isclose,
            ]
        );
    }
}
