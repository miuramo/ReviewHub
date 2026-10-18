<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraLink extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'label',
        'url',
        'color',
        'role_name',
        'is_header',
        'is_footer',
    ];
}
