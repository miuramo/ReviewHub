<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filetype extends Model
{
    /** @use HasFactory<\Database\Factories\FiletypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
