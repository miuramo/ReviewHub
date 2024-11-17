<?php

namespace Database\Seeders;

use App\Models\Bidding;
use App\Models\Contact;
use App\Models\Paper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (\App\Models\User::count() == 0) {
            \App\Models\User::factory()->create([
                'name' => env('INITIAL_NAME'),   //'First User',
                'email' => env('INITIAL_EMAIL'), //'firstuser@example.com',
                'affil' => env('INITIAL_AFFIL'), //'Example',
                'password' => Hash::make(env('INITIAL_PASSWORD')),
            ]);

        }
        if (\App\Models\Role::count() == 0) {
            foreach (\App\Models\Role::$roles as $name => $desc) {
                $tmp = \App\Models\Role::create([
                    'name' => $name,
                    'desc' => $desc,
                    'abbr' => $name,
                ]);
                $tmp->users()->attach(1);
            }
        }

        \App\Models\Paper::firstOrCreate([
            'category_id' => 1,
            'owner' => 1,
            'contactemails' => "miura@istlab.info",
            'title' => "サンプル論文",
            'etitle' => "Sample Paper",
            'abst' => "これはサンプル論文です。",
            'keyword' => "サンプル, 論文",
            'authorlist' => "創造 太郎 (創造大)",
            'eauthorlist' => "Sozo, Taro (Sozo University)",
        ], [
        ]);

        $this->call([
            EnqueteSeeder::class,
            EnqueteConfigSeeder::class,
            EnqueteItemSeeder::class,
            EnqueteAnswerSeeder::class,
            BiddingSeeder::class,
            ViewpointSeeder::class,
            CategorySeeder::class,
            ConfirmSeeder::class,
            AcceptSeeder::class,
            SettingSeeder::class,
            MailTemplateSeeder::class,
            EventConfigSeeder::class,
            FiletypeSeeder::class,
            StatusSeeder::class,
        ]);

    }
}
