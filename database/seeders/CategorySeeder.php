<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            1 => "論文誌",
        ];
        $bg = [
            1 => "teal",
            2 => "lime",
            3 => "yellow",
        ];
        $fg = [
            1 => "blue",
            2 => "green",
            3 => "orange",
        ];
        $oe = [
            1 => "12-31",
            2 => "12-31",
            3 => "12-31",
        ];
        foreach ($data as $n => $d) {
            \App\Models\Category::factory()->create([
                'name' => $d,
                'bgcolor' => $bg[$n],
                'color' => $fg[$n],
                'openend' => $oe[$n],
            ]);
        }
    }
}
