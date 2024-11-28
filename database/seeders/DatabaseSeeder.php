<?php

namespace Database\Seeders;

use App\Models\Bidding;
use App\Models\Contact;
use App\Models\Paper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as FakerFactory;

class DatabaseSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        // 日本語ロケールで Faker を初期化
        $this->faker = FakerFactory::create('ja_JP');
    }
    /**
     * Faker インスタンスを取得
     */
    public function faker()
    {
        return $this->faker;
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::count() == 0) {
            User::factory()->create([
                'name' => env('INITIAL_NAME'),   //'First User',
                'email' => env('INITIAL_EMAIL'), //'firstuser@example.com',
                'affil' => env('INITIAL_AFFIL'), //'Example',
                'password' => Hash::make(env('INITIAL_PASSWORD')),
            ]);
            User::factory()->create([
                'name' => "当麻 哲哉",   //'First User',
                'email' => "miura@moto.qee.jp", //'firstuser@example.com',
                'affil' => "慶應義塾大学", //'Example',
                'password' => Hash::make('jcs'),
            ]);
        }
        if (Role::count() == 0) {
            foreach (Role::$roles as $name => $desc) {
                $tmp = Role::create([
                    'name' => $name,
                    'desc' => $desc,
                    'abbr' => $name,
                ]);
                $tmp->users()->attach(1);
                $tmp->users()->attach(2);
            }
            $brev = Role::findByIdOrName('brev');
            $brev->navi = "x";
            $brev->save();
            $manager = Role::findByIdOrName('manager');
            $manager->navi = "x";
            $manager->save();
        }
        Paper::firstOrCreate([
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

        if (true){
            User::factory(24)->create();
            Role::findByIdOrName('admin')->users()->attach(3);
            for($i=3;$i<=4;$i++){
                Role::findByIdOrName('ec')->users()->attach($i);
            }
            for($i=5;$i<=7;$i++){
                Role::findByIdOrName('aec')->users()->attach($i);
            }
            for($i=8;$i<=12;$i++){
                Role::findByIdOrName('meta')->users()->attach($i);
            }
            for($i=13;$i<=24;$i++){
                Role::findByIdOrName('rev')->users()->attach($i);
            }

            // 論文作成
            for($i=2; $i<=12; $i++){
                Paper::factory()->cat(1)->owner($i)->create();
            }
        }
        



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
            WorkflowSeeder::class,
            FileSeeder::class,
        ]);

    }
}
