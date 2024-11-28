<?php

namespace App\Models;

use App\Mail\BbNotify;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bb extends Model
{
    use HasFactory;

    protected $attributes = [
        'members' => '[]',
    ];
    protected $casts = [
        'members' => 'array',
    ];

    protected $fillable = [
        'name',
        'paper_id',
        'category_id',
        'type',
        'member',
        'key',
        'needreply',
        'isopen',
        'isclose',
        'subscribers',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function messages()
    {
        return $this->hasMany(BbMes::class, 'bb_id');
    }

    public function nummessages()
    {
        // メッセージの数を返す
        
        return $this->hasMany(BbMes::class, 'bb_id')->count();
    }
    public static function make_bb($uids, int $pid, int $cid)
    {

        // $firstmes = [
        //     1 => "ここは査読者同士の事前議論掲示板です。\n査読者は自身を名乗らないでください。必要があればRevIDを用いてください。RevIDは送信フォームに表示されています。\n（RevIDが表示されていない場合は、査読を担当していません。）\n注：RevIDは査読者のIDではなく、査読割当てごとに異なるIDです。",
        //     2 => "ここはメタ査読者と著者の掲示板です。（プログラム委員長も閲覧できます。）",
        //     3 => "ここは出版担当と著者の掲示板です。",
        // ];
        $bb = Bb::firstOrCreate([
            'paper_id' => $pid,
            'category_id' => $cid,
            'members' => $uids,
        ], [
            'key' => Str::random(30),
        ]);
        $mes = BbMes::firstOrCreate([
            'bb_id' => $bb->id,
        ], [
            'user_id' => 0,
            'subject' => 'ごあんない',
            'mes' => "掲示板を開設しました。",
        ]);
        return Bb::with("messages")->with("paper")->with("category")->find($bb->id);
    }

    /**
     * Bb通知メールをおくる
     */
    public static function send_email_nofity(Bb $bb, BbMes $bbmes)
    {
        // pcのみ利害関係に注意する。
        (new BbNotify($bb, $bbmes))->process_send();

    }
    public function url()
    {
        return route('bb.show', ['bb' => $this->id, 'key' => $this->key]);
    }
    public static function url_from_rev(Review $rev, int $type=1)
    {
        $bb = Bb::where("paper_id", $rev->paper_id)->where("category_id", $rev->category_id)->where("type", $type)->first();
        if ($bb==null) return null;
        return $bb->url();
    }

    public function get_mail_to_cc()
    {
        $tolist = [];
        $bcclist = [];

        foreach($this->members as $u){
            $tolist[] = $u->email;
        }
        return ["to" => $tolist, "bcc" => $bcclist ];
    }

    // public function get_reviewers()
    // {
    //     $revuids = Review::where("paper_id", $this->paper_id)->where("category_id",$this->category_id)->where("target", 0)->pluck("user_id", "id")->toArray();
    //     return User::whereIn("id", $revuids)->get();
    // }
    // public function revuid2rev()
    // {
    //     $revuid2rev = Review::where("paper_id", $this->paper_id)->where("category_id",$this->category_id)->where("target", 0)->pluck("id", "user_id")->toArray();
    //     return $revuid2rev;
    // }
    // public function ismeta_myself()
    // {
    //     // 自分がメタ査読者かどうかを返す
    //     $rev = Review::where("paper_id", $this->paper_id)->where("category_id", $this->category_id)->where("user_id", auth()->id())->where("target", 1)->first();
    //     return $rev != null;
    // }
    // public function metauser()
    // {
    //     // メタ査読者を返す
    //     $rev = Review::where("paper_id", $this->paper_id)->where("category_id", $this->category_id)->where("target", 1)->first();
    //     return $rev->user;
    // }

    // /**
    //  * ユーザIDから、シェファーディング掲示板を取得する
    //  */
    // public static function getShepherdingBbs($user_id)
    // {
    //     // get all meta reviews
    //     $metarev_pids = Review::where('user_id', $user_id)->where('target', 1)->get()->pluck('paper_id')->toArray();
    //     return Bb::whereIn('paper_id', $metarev_pids)->where('type', 2)->get();
    // }

}
