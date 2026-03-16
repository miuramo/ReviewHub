<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::create([
            'name' => '編集委員',
            'menutext' => '編集委員',
            'menulink' => '/cm',
            'rolenames' => 'cm',
        ]);
        Post::create([
            'name' => '幹事',
            'menutext' => '幹事',
            'menulink' => '/aec',
            'rolenames' => 'aec',
        ]);
        Post::create([
            'name' => '編集長',
            'menutext' => '編集長',
            'menulink' => '/ec',
            'rolenames' => 'ec',
        ]);

        //
    }
}
