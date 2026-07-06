<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;
    use FindByIdOrNameTrait, GetValueTrait;

    protected $fillable = [
        'name',
        'value',
        'isnumber',
        'isbool',
        'valid',
        'misc',
    ];


    /**
     * Settingの REVIEWER_MEMBER や PC_MEMBERをみて、自動でロールをわりあてる
     */
    public static function auto_role_member(): void
    {
        $sets = Setting::where("name", "like", "%_MEMBER")->where("valid", true)->get();
        foreach ($sets as $set) {
            $val = $set->value;
            if (strlen($val) < 2) continue;
            // role name
            $role_name = strtolower(explode("_", $set->name)[0]);
            $role = Role::findByIdOrName($role_name);
            // | で区切る
            $ary = explode("|", $val);
            if (count($ary) < 1) continue;
            foreach ($ary as $name) {
                $tmpu = User::where("name", $name)->first();
                if ($tmpu == null) continue;
                if (!$role->containsUser($tmpu->id)) { // ふくまれていなければ
                    $tmpu->roles()->attach($role);
                    info("auto_role_member {$name} {$role->name}");
                }
            }
        }
    }

    // public static function getval($setting_name, $dummy = null)
    // {
    //     $setting = Setting::where('name', $setting_name)->first();
    //     if ($setting) {
    //         return $setting->value;
    //     }
    //     return null;
    // }
    public static function setval(string $setting_name, string $setting_value): void
    {
        $setting = Setting::where('name', $setting_name)->first();
        if ($setting) {
            $setting->value = $setting_value;
            $setting->save();
        } else {
            Setting::create([
                'name' => $setting_name,
                'value' => $setting_value,
                'valid' => 1,
                'isnumber' => 0,
                'isbool' => 0,
            ]);
        }
    }

    public static function getary(string $setting_name): ?array
    {
        $val = self::getval($setting_name);
        if ($val) {
            return json_decode($val, true);
        }
        return null;
    }



    public static function seeder(): void
    {
        Setting::firstOrCreate([
            'name' => "NAME_OF_META",
        ], [
            'value' => "メタ査読者",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "SKIP_BIBINFO",
        ], [
            'value' => '["keyword","abst","eabst","ekeyword"]',
            'isnumber' => false,
            'isbool' => false,
        ]);
        // 
        Setting::firstOrCreate([
            'name' => "FILE_DESCRIPTIONS",
        ], [
            'value' => '{"pdf":"論文PDF","altpdf":"ティザー資料","img":"代表画像","video":"参考ビデオ","pptx":"PowerPoint(pptx)"}',
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "FILEPUT_DIR",
        ], [
            'value' => "z2024",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "PC_MEMBER",
        ], [
            'value' => "",
            'isnumber' => false,
            'isbool' => false,
            'valid' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "REVIEWER_MEMBER",
        ], [
            'value' => "",
            'isnumber' => false,
            'isbool' => false,
            'valid' => false,
        ]);
        $sets = Setting::where("name", "like", "%_MEMBER")->where("valid", true)->get();
        foreach ($sets as $set) {
            if (strlen($set->value) < 1) {
                $set->valid = false;
                $set->misc = "（注意）氏 名を|で区切って設定しておくと、自動でROLE付与します。";
                $set->save();
            }
        }

        // 表彰状用JSON のダウンロードキー
        $temporal_key = Setting::getval("CONFTITLE_YEAR") . Str::random(10);
        Setting::firstOrCreate([
            'name' => "AWARDJSON_DLKEY",
        ], [
            'value' => $temporal_key,
            'misc' => "表彰状生成用JSON Download Key",
            'isnumber' => false,
            'isbool' => false,
        ]);

        Setting::firstOrCreate([
            'name' => "LAST_QUEUEWORK_DATE",
        ], [
            'value' => "(TestQueueWork未実行)",
            'isnumber' => false,
            'isbool' => false,
        ]);

        Setting::firstOrCreate([
            'name' => "CFP_LINKTEXT",
        ], [
            'value' => "論文募集 / Call for Paper に戻る",
            'isnumber' => false,
            'isbool' => false,
        ]);

        Setting::firstOrCreate([
            'name' => "CROP_YHWX",
        ], [
            'value' => "[80,500, 1100,-1]",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '最後のXが負数だとセンタリング計算でXを求める',
        ]);
        Setting::firstOrCreate([
            'name' => "REPLACE_PUNCTUATION",
        ], [
            'value' => '{"。":"．","、":"，"}',
            'isnumber' => false,
            'isbool' => false,
            'misc' => '句読点。ReplaceKutenMiddlewareで使用する。valid=0で無効にできる。',
            'valid' => false,
        ]);

        // Viewpoint::change_separator();
        Setting::firstOrCreate([
            'name' => "REDIRECT",
        ], [
            'value' => "/paper",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '/paper/create | /paper | /vote',
        ]);

        Setting::firstOrCreate([
            'name' => "REVIEW_DURATION_DAYS",
        ], [
            'value' => "[24, 10, 5]",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '査読期間の日数。通常、メタ、最終の順で指定する。例: [24, 10, 5]',
        ]);

        Setting::firstOrCreate([
            'name' => "RESUBMIT_DURATION_DAYS",
        ], [
            'value' => "[0, 60, 60]",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '再投稿期間の日数。カテゴリごとに指定する。例: [0, 60, 60]',
        ]);

        Setting::firstOrCreate([
            'name' => "NAME_OF_MANAGER",
        ], [
            'value' => "投稿管理者",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '投稿管理者の呼称。例: "投稿管理者"、"編集担当幹事"など',
        ]);
        Setting::firstOrCreate([
            'name' => "NAME_OF_MANAGERS",
        ], [
            'value' => "投稿管理者",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '投稿管理者の呼称（複数形）。例: "投稿管理者"、"幹事団"など',
        ]);

        Setting::firstOrCreate([
            'name' => "SHOW_AEC_NAME",
        ], [
            'value' => "true",
            'isnumber' => false,
            'isbool' => true,
            'valid' => false,
            'misc' => '幹事の名前を投稿論文一覧（査読中）に表示する',
        ]);


        Vote::init();
        VoteItem::init();

        Role::firstOrCreate([
            'name' => "cm",
        ], [
            'abbr' => "cm",
            'desc' => "編集委員",
        ]);

        Role::firstOrCreate([
            'name' => "pub",
        ], [
            'abbr' => "pub",
            'desc' => "出版",
        ]);
    }
}
