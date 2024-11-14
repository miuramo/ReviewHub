<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FiletypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\FileType::factory()->create([
            'name' => '論文',
        ]);
        \App\Models\FileType::factory()->create([
            'name' => '回答書',
        ]);
        \App\Models\FileType::factory()->create([
            'name' => '対照表',
        ]);
        \App\Models\FileType::factory()->create([
            'name' => 'その他',
        ]);
    }
}
