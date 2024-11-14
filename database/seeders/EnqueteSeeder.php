<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnqueteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Enquete::factory()->create([
            'name' => '投稿申請',
            'showonpaperindex' => true,
            'showonreviewerindex' => true,
        ]);

    }
}
