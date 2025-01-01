<?php

namespace App\Models;

use App\Mail\BbNotify;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bb extends MetaModel
{
    use HasFactory;

    // protected $attributes = [
    //     'members' => '[]',
    // ];
    // protected $casts = [
    //     'members' => 'array',
    // ];

    protected $fillable = [
        'name',
        'paper_id',
        'submit_id',
        'type',
        'rev_id',
        'key',
        'needreply',
        'isopen',
        'isclose',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }
    public function submit()
    {
        return $this->belongsTo(Category::class, 'submit_id');
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
    public static function make_bb(Submit $sub, int $type = 1, int $rev_id = 0)
    {
        $firstmes = [
            1 => "ここは投稿管理者と著者の掲示板です。",
            2 => "ここは投稿管理者と査読者の掲示板です。他の査読者の方はメンバーに含まれていません。",
            3 => "ここは投稿管理者と全査読者の掲示板です。\n査読者は自身を名乗らないでください。必要があればRevIDを用いてください。RevIDは送信フォームに表示されています。\n（RevIDが表示されていない場合は、査読を担当していません。）\n注：RevIDは査読者のIDではなく、査読割当てごとに異なるIDです。",
            4 => "ここは投稿管理者同士の掲示板です。\n査読者はメンバーに含まれていません。",
        ];
        $bb = Bb::firstOrCreate([
            'paper_id' => $sub->paper_id,
            'submit_id' => $sub->id,
            'type' => $type,
            'rev_id' => $rev_id, // type=2のときのみ
        ], [
            'key' => Str::random(30),
        ]);
        $mes = BbMes::firstOrCreate([
            'bb_id' => $bb->id,
        ], [
            'user_id' => 0,
            'subject' => 'ごあんない',
            'mes' => $firstmes[$type],
        ]);
        return Bb::with("messages")->with("paper")->find($bb->id);
    }
    public static function gen_make_url(int $sub_id, int $type, int $rev_id = 0)
    {
        $serial = MetaModel::ary2serial(["sub_id" => $sub_id, "type"=>$type, "rev_id"=>$rev_id]);
        return route('bb.gen', ['serial' => $serial]);
    }
    public static function gen_from_serial(string $serial)
    {
        $ary = MetaModel::serial2ary($serial);
        $sub = Submit::with('paper')->find($ary["sub_id"]);
        return Bb::make_bb($sub, $ary["type"], $ary["rev_id"]);
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
    public static function url_from_bbid(int $bbid)
    {
        $bb = Bb::find($bbid);
        if ($bb==null) return null;
        return $bb->url();
    }
    public static function url_from_rev(Review $rev, int $type = 1)
    {
        $bb = Bb::where("paper_id", $rev->paper_id)->where("category_id", $rev->category_id)->where("type", $type)->first();
        if ($bb == null) return null;
        return $bb->url();
    }
    public function get_participants()
    {
        $retuobj = [];
        foreach($this->paper->managers as $manager){
            $retuobj[] = $manager;
        }
        if ($this->type == 1) {
            $retuobj[] = $this->paper->paperowner;
            foreach($this->paper->contacts as $contact){
                $retuobj[] = $contact;
            }
        } else if ($this->type == 2) {
            $review = Review::with('user')->find($this->rev_id);
            $retuobj[] = $review->user;
        } else if ($this->type == 3) {
            $reviews = Review::with('user')->where("paper_id", $this->paper_id)->where("submit_id", $this->submit_id)->get();
            foreach($reviews as $review){
                $retuobj[] = $review->user;
            }
        } else if ($this->type == 4) {
        }
        return $retuobj;
    }
    public function get_mail_to_cc()
    {
        $tolist = [];
        $cclist = [];
        $bcclist = [];

        $manager_list = $this->paper->get_mail_manager();
        if ($this->type == 4 || $this->type == 3){
            $tolist = array_merge($tolist, $manager_list);
        } else {
            $bcclist = array_merge($bcclist, $manager_list);
        }

        if ($this->type == 1) {
            $to_cc_list = $this->paper->get_mail_to_cc();
            $tolist[] = $to_cc_list['to'];
            $bcclist = array_merge($bcclist, $to_cc_list['cc']);
        } else if ($this->type == 2) {
            $review = Review::with('user')->find($this->rev_id);
            $tolist[] = $review->user->email;
        } else if ($this->type == 3) {
            $reviews = Review::with('user')->where("paper_id", $this->paper_id)->where("submit_id", $this->submit_id)->get();
            foreach($reviews as $review){
                $bcclist[] = $review->user->email;
            }
        } else if ($this->type == 4) {
        }

        return ["to" => $tolist, "cc"=>$cclist, "bcc" => $bcclist];
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
