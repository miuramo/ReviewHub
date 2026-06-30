<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Viewpoint extends Model
{
    use HasFactory;

    public static $separator = ';'; // semi-colon is better than colon

    protected $fillable = [
        'category_id',
        'orderint',
        'name',
        'desc',
        'content',
        'contentafter',
        'forrev',
        'formeta',
        'weight',
        'doReturn',
        'doReturnAcceptOnly',

    ];

    /**
     * カテゴリとターゲットで査読観点を取得
     * ターゲットは、査読者向けなら0、メタなら1、幹事なら2。
     * ==関連して、Review.target を1つ増やす必要があるが、まずは変更なしで。将来的に、0は無効にしたい。== →とりあえずそのままで。
     */
    public static function by_category_target(int $cat_id, int $target012 = 0): \Illuminate\Database\Eloquent\Collection
    {
        $bitmask_target = pow(2, $target012); // 0,1,2,3 -> 1,2,4,8
        // Viewpointテーブルのtargetカラムはビットマスクになっている。target012に対応するビットが立っていれば、取得される。
        return Viewpoint::where("category_id", $cat_id)->whereRaw("target & ? != 0", [$bitmask_target])->orderBy("orderint")->get();
    }

    /**
     * コロンではなくセミコロンに変更
     */
    public static function change_separator(): string
    {
        $pre = ":";
        $post = self::$separator;
        // すべての査読観点を取得
        $vps = Viewpoint::all();

        $log = "Viewpoint\n";
        // もし ;(post) が含まれていない and  :(pre) が含まれている場合のみ変更
        // 各レコードのcontentフィールドの「:」を「;」に置換
        foreach ($vps as $vp) {
            if (strpos($vp->content, $post) === false) {
                if (strpos($vp->content, $pre) !== false) {
                    $vp->content = str_replace($pre, $post, $vp->content);
                    $vp->save();
                    $log .= $vp->id . " ";
                }
            }
        }
        // アンケート項目も同様に。
        $log .= "\nEnqueteItem\n";
        $enqitems = EnqueteItem::all();
        foreach ($enqitems as $vp) {
            // もし ;(post) が含まれていない and  :(pre) が含まれている場合のみ変更
            if (strpos($vp->content, $post) === false) {
                if (strpos($vp->content, $pre) !== false) {
                    $vp->content = str_replace($pre, $post, $vp->content);
                    $vp->save();
                    $log .= $vp->id . " ";
                }
            }
        }
        return $log;
    }

    /**
     * OrderInt をstep ずつで再設定する
     */
    public static function reorderint(int $cat_id, int $step = 10): void
    {
        $items = Viewpoint::where("category_id", $cat_id)->orderBy("orderint")->get();
        $num = $step;
        foreach ($items as $enqitm) {
            $enqitm->orderint = $num;
            $enqitm->save();
            $num += $step;
        }
    }

    public static function firstContent(string $desc): ?string
    {
        $vp = Viewpoint::where("desc", $desc)->first();
        if ($vp == null) {
            return null;
        }
        $ary = explode(self::$separator, $vp->content);
        return nl2br(trim($ary[0]));
    }

    /**
     * targetをビットマスクに変換する。0は無効、1は査読者向け、2はメタ向け、4は幹事向け。複数指定する場合は足す（例：7はすべて）。すでにビットマスクになっている場合は何もしない。
     */
    public static function fix_target_as_bitmask(): void
    {
        // 適用済みかどうかを、targetに0が一つでもあるかどうかで判断する。複数回適用するとデータが壊れるため。
        $target_min = Viewpoint::min("target");
        if ($target_min == 1) { // すでにビットマスクになっていると判断する。
            return;
        }
        // さらに、maxが3以下であれば、まだビットマスクに変換されていないと判断する。
        $target_max = Viewpoint::max("target");
        if ($target_max > 3) { // すでにビットマスクになっていると判断する。
            return;
        }

        // 2を4に、1を2に、0を1に変換する。
        $vps = Viewpoint::all();
        foreach($vps as $vp) {
            if ($vp->target == 0) {
                $vp->target = 1;
                $vp->save();
            } else if ($vp->target == 1) {
                $vp->target = 2;
                $vp->save();
            } else if ($vp->target == 2) {
                $vp->target = 4;
                $vp->save();
            }
        }
        Log::channel('plain')->info("Viewpoint: fixed target as bitmask");
    }

    /**
     * 論文用の査読観点を、ショートペーパー用にコピーする。その際、orderint順に作成していく。
     */
    public static function bundle_copy(int $src_cat_id = 1, int $dest_cat_id = 2): void
    {
        // もし、すでにショートペーパー用の査読観点が存在する場合は、コピーしない。
        $count = Viewpoint::where("category_id", $dest_cat_id)->count();
        if ($count > 0) {
            return;
        }

        $vps = Viewpoint::where("category_id", $src_cat_id)->orderBy("orderint")->get();
        $num = 10;
        foreach ($vps as $vp) {
            $newvp = new Viewpoint();
            $newvp->category_id = $dest_cat_id;
            $newvp->orderint = $num;
            $newvp->name = $vp->name;
            $newvp->desc = $vp->desc;
            $newvp->mandatory = $vp->mandatory;
            $newvp->content = $vp->content;
            $newvp->contentafter = $vp->contentafter;
            $newvp->target = $vp->target;
            $newvp->weight = $vp->weight;
            $newvp->doReturn = $vp->doReturn;
            $newvp->doReturnAcceptOnly = $vp->doReturnAcceptOnly;
            $newvp->subdesc = $vp->subdesc;
            $newvp->save();
            $num += 10;
        }
    }
}
