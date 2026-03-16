<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $confname = "日本創造学会論文誌";
        $confabb = "JCS";
        $mailfrom = "jcs-editorial@istlab.info"; // "toukouadmin@interaction-ipsj.org"
        Setting::factory()->create([
            'name' => "CONFTITLE",
            'value' => $confname,
        ]);
        Setting::factory()->create([
            'name' => "CONFTITLE_BASE",
            'value' => $confname,
        ]);
        Setting::factory()->create([
            'name' => "CONFTITLE_ABB",
            'value' => $confabb,
        ]);
        Setting::factory()->create([
            'name' => "MAILFROM",
            'value' => $mailfrom,
        ]);
        Setting::factory()->create([
            'name' => "CONF_URL",
            'value' => "http://www.japancreativity.jp/member_2.html",
        ]);
        Setting::factory()->create([
            'name' => "PSEUDOTESTSITE",
            'value' => "false",
            'isnumber' => false,
            'isbool' => true,
        ]);
        Setting::factory()->create([
            'name' => "FILEPUT_DIR",
            'value' => "z" . $confabb,
            'isnumber' => false,
            'isbool' => false,
        ]);
        # 投稿案内・マニュアルURL
        Setting::factory()->create([
            'name' => "CFP_URL",
            'value' => "https://scrapbox.io/reviewhub/%E6%8A%95%E7%A8%BF%E3%83%9E%E3%83%8B%E3%83%A5%E3%82%A2%E3%83%AB",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::factory()->create([
            'name' => "CFP_LINKTEXT",
            'value' => "投稿案内・マニュアル",
            'isnumber' => false,
            'isbool' => false,
        ]);

        // Setting seeder
        Setting::seeder();
    }
}
