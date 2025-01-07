<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnqueteAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('APP_DEBUG')) {
            \App\Models\EnqueteAnswer::factory()->create([
                'enquete_id' => 1,
                'enquete_item_id' => 1,
                'user_id' => 1,
                'paper_id' => 1,
                'value' => null,
                'valuestr' => "そうぞう たろう",
            ]);

            \App\Models\EnqueteAnswer::factory()->create([
                'enquete_id' => 1,
                'enquete_item_id' => 2,
                'user_id' => 1,
                'paper_id' => 1,
                'value' => null,
                'valuestr' => "03-1234-5678",
            ]);

            \App\Models\EnqueteAnswer::factory()->create([
                'enquete_id' => 1,
                'enquete_item_id' => 3,
                'user_id' => 1,
                'paper_id' => 1,
                'value' => null,
                'valuestr' => "111-2222 東京都千代田区1-2",
            ]);

            \App\Models\EnqueteAnswer::factory()->create([
                'enquete_id' => 1,
                'enquete_item_id' => 4,
                'user_id' => 1,
                'paper_id' => 1,
                'value' => 800,
                'valuestr' => "800",
            ]);

            \App\Models\EnqueteAnswer::factory()->create([
                'enquete_id' => 1,
                'enquete_item_id' => 5,
                'user_id' => 1,
                'paper_id' => 1,
                'value' => 8,
                'valuestr' => "8",
            ]);
        }

        //
    }
}
