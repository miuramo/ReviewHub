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
        $mailfrom = "jcs-editorial@googlegroups.com"; // "toukouadmin@interaction-ipsj.org"
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

        // Setting seeder
        Setting::seeder();
    }
}
