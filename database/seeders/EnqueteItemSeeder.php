<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnqueteItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 1,
            'orderint' => 1,
            'name' => 'furigana',
            'desc' => '氏名ふりがな',
            'content' => "連絡著者の氏名ふりがなを入力してください。\n;text ; 60 ; (例)そうぞう たろう",
            'contentafter' => '',
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 1,
            'orderint' => 2,
            'name' => 'tel',
            'desc' => '連絡先電話番号',
            'content' => "連絡先電話番号を入力してください。\n;text ; 30 ; (例)03-1234-5678",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 1,
            'orderint' => 3,
            'name' => 'addr',
            'desc' => '連絡先住所',
            'content' => "連絡先住所を入力してください。\n;textarea ; 50 ; 3 ; (例)111-2222 東京都千代田区1-2-3",
            'contentafter' => '',
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 1,
            'orderint' => 4,
            'name' => 'jisuu',
            'desc' => '論文字数',
            'content' => "論文字数を整数で入力してください。\n; number ; 0 ; 99999 ",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 1,
            'orderint' => 5,
            'name' => 'zuhyo',
            'desc' => '図表枚数',
            'content' => "図表枚数を整数で入力してください。\n; number ; 0 ; 99 ",
        ]);

    }
}
