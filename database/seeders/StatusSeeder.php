<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "投稿準備中",
            "投稿完了",
            "メタ割り当て中",
            "査読者割り当て中",
            "査読中",
            "査読修正中",
            "査読完了",
            "編集委員会判定中",
            "査読結果通知済み",
        ];

        foreach ($data as $d) {
            \App\Models\Status::factory()->create([
                'name' => $d,
            ]);
        }
        //
    }
}
