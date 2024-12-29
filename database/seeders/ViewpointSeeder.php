<?php

namespace Database\Seeders;

use App\Models\Viewpoint;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViewpointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach([1] as $cat){
            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 1,
                'name' => 'score',
                'desc' => '総合点',
                'content' => "5：採択、4：採択に近い、3：中立、2：不採択に近い、1：不採択\n; number ; 1 ; 5 ",
                'weight' => 1,
                'doReturn' => true,
                // 'contentafter' => '',
            ]);
            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 2,
                'name' => 'comment',
                'desc' => '査読コメント',
                'content' => "査読コメントは著者に返ります。\n; textarea ; 60 ; 5 ; （著者に返ります）",
                'doReturn' => true,
            ]);
            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 3,
                'name' => 'for_meta_aec',
                'desc' => '編集委員向けコメント',
                'content' => "編集委員向けコメントは著者に返りません。\n;textarea ; 60 ; 5 ; (編集委員会向け) ",
                'mandatory' => false,
            ]);


            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 1,
                'name' => 'metascore',
                'desc' => 'メタ総合点',
                'content' => "5：採択、4：採択に近い、3：中立、2：不採択に近い、1：不採択\n; number ; 1 ; 5 ",
                'weight' => 1,
                'doReturn' => true,
                'target' => 1,
            ]);
            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 2,
                'name' => 'metacomment',
                'desc' => 'メタコメント',
                'content' => "メタコメントは著者に返ります。\n; textarea ; 60 ; 5 ; （著者に返ります）",
                'doReturn' => true,
                'target' => 1,
            ]);
            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 3,
                'name' => 'for_meta_aec',
                'desc' => '編集委員向けコメント',
                'content' => "編集委員向けコメントは著者に返りません。\n;textarea ; 60 ; 5 ; (編集委員会向け) ",
                'target' => 1,
            ]);

            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 1,
                'name' => 'result',
                'desc' => '措置',
                'content' => "; selection ; 採択 ; 条件付き ; 不採択",
                'doReturn' => true,
                'target' => 2,
            ]);
            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 2,
                'name' => 'aeccomment',
                'desc' => '幹事コメント',
                'content' => "幹事コメントは著者に返ります。\n; textarea ; 60 ; 5 ; （著者に返ります）",
                'doReturn' => true,
                'target' => 2,
            ]);
            Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 3,
                'name' => 'for_ec_by_aec',
                'desc' => '編集委員向けコメント',
                'content' => "編集委員向けコメントは著者に返りません。\n;textarea ; 60 ; 5 ; (編集委員会向け) ",
                'target' => 2,
            ]);

        }
        //
    }
}
