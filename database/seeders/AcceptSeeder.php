<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcceptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "採択" => 10,
            "条件付き採択" => 0,
            "---" => 0,
            "不採択" => -1,
            "発表取り下げ" => -2,
            "投稿不備" => -3
        ];
        foreach ($data as $d => $n) {
            \App\Models\Accept::factory()->create([
                'name' => $d,
                'judge' => $n,
                'bgcolor' => ($n > 0) ? "orange" : "gray",
                'color' => ($n > 0) ? "red" : "black",
            ]);
        }
        //
    }
}
