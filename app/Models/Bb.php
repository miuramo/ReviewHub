<?php

namespace App\Models;

use App\Mail\BbNotify;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bb extends MetaModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'paper_id',
        'category_id',
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
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
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

    /**
     * TaskController.create または
     * TaskController.update から呼ばれる。
     */
    public static function add_message(Submit $sub, $type, $subject, $mes, $rev_id = 0)
    {
        $bb = Bb::where('paper_id', $sub->paper->id)->where('type', $type)->where('rev_id', $rev_id)->first();
        if (!$bb) {
            $bb = Bb::make_bb($sub, $type, $rev_id);
        }
        $bbmes = BbMes::create([
            'bb_id' => $bb->id,
            'user_id' => auth()->id(),
            'subject' => $subject,
            'mes' => $mes,
        ]);
        //メール通知
        (new BbNotify($bb, $bbmes))->process_send();

        return $bb;
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
        $mes = BbMes::create([
            'bb_id' => $bb->id,
            'user_id' => 0,
            'subject' => 'ごあんない',
            'mes' => $firstmes[$type],
        ]);
        return $bb;
    }
    public static function gen_make_url(int $sub_id, int $type, int $rev_id = 0)
    {
        $serial = MetaModel::ary2serial(["sub_id" => $sub_id, "type" => $type, "rev_id" => $rev_id]);
        return route('bb.gen', ['serial' => $serial]);
    }
    public static function gen_from_serial(string $serial)
    {
        $ary = MetaModel::serial2ary($serial);
        $sub = Submit::with('paper')->find($ary["sub_id"]);
        return Bb::make_bb($sub, $ary["type"], $ary["rev_id"]);
    }
    //
    public static function ordinal($number)
    {
        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        if ($number % 100 >= 11 && $number % 100 <= 13) {
            $suffix = 'th';
        } else {
            $suffix = $suffixes[$number % 10];
        }

        return $number . $suffix;
    }

    /**
     * Bb通知メールをおくる
     */
    // public static function send_email_nofity(Bb $bb, BbMes $bbmes)
    // {
    //     // pcのみ利害関係に注意する。
    //     (new BbNotify($bb, $bbmes))->process_send();
    // }
    public function url()
    {
        return route('bb.show', ['bb' => $this->id, 'key' => $this->key]);
    }
    public static function url_from_bbid(int $bbid)
    {
        $bb = Bb::find($bbid);
        if ($bb == null) return null;
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
        foreach ($this->paper->managers as $manager) {
            $retuobj[] = $manager;
        }
        if ($this->type == 1) {
            $retuobj[] = $this->paper->paperowner;
            foreach ($this->paper->contacts as $contact) {
                $retuobj[] = $contact;
            }
        } else if ($this->type == 2) {
            $revobj = Review::find($this->rev_id);
            $revuser = User::find($revobj->user_id);
            $retuobj[] = $revuser;
        } else if ($this->type == 3) {
            $reviews = Review::with('user')->where("paper_id", $this->paper_id)->where("submit_id", $this->submit_id)->get();
            foreach ($reviews as $review) {
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
        if ($this->type == 4 || $this->type == 3) {
            $tolist = array_merge($tolist, $manager_list);
        } else {
            $bcclist = array_merge($bcclist, $manager_list);
        }

        if ($this->type == 1) {
            $to_cc_list = $this->paper->get_mail_to_cc();
            $tolist[] = $to_cc_list['to'];
            $bcclist = array_merge($bcclist, $to_cc_list['cc']);
        } else if ($this->type == 2) {
            // reload this from db
            $revobj = Review::find($this->rev_id);
            $revuser = User::find($revobj->user_id);
            $tolist[] = $revuser->email;
        } else if ($this->type == 3) {
            $reviews = Review::with('user')->where("paper_id", $this->paper_id)->where("submit_id", $this->submit_id)->get();
            foreach ($reviews as $review) {
                $bcclist[] = $review->user->email;
            }
        } else if ($this->type == 4) {
        }

        return ["to" => $tolist, "cc" => $cclist, "bcc" => $bcclist];
    }

    /**
     * 定型文を作成する
     */
    public function getRevTemplates()
    {
        $revobj = Review::find($this->rev_id);
        $paper_id = $revobj->paper_id;
        $revuser = User::find($revobj->user_id);
        $conftitle = Setting::getval('CONFTITLE');
        // 前回査読の報告を検索
        $vpid_score = Viewpoint::where('name', 'score')->where('category_id', 1)->first()
            ->id;
        // この査読者の、直前の査読の点数を取得する
        $allscore =
            Score::with('review')->where('viewpoint_id', $vpid_score)->whereHas('review', function ($q) use ($paper_id, $revuser) {
                $q->where('paper_id', $paper_id);
                $q->where('user_id', $revuser->id);
            })
            ->orderBy('created_at', 'desc') // この査読者の、この論文に対する査読のうち、最新のものが最初に来る
            ->get();
        $score = $allscore->first()->value ?? 0;
        if ($score == 1) {
            $revjudgment = '不採録';
        } elseif ($score == 2) {
            $revjudgment = '条件付き採録';
        } elseif ($score == 3) {
            $revjudgment = '採録';
        } else {
            $revjudgment = '不明';
        }
        // info("revjudgment: $revjudgment (score=$score)");
        // 最終（最新）査読結果をsubmitsから取得する
        $allsubmit = Submit::with('accept')
            ->where('paper_id', $this->paper_id)
            // ->whereNotNull('ec_decision_at')
            ->where('canceled', 0)
            ->orderBy('ec_decision_at', 'desc') // 最終判断がついたものについて、新しい順にならんだあと、 ec_decision_atがnullのものが最後に来る
            ->get();
        // info("submit: " . json_encode($allsubmit));
        // info("submit count: " . count($allsubmit));
        // info("allscore: " . json_encode($allscore));
        // info("allscore count: " . count($allscore));

        $first_thank = $revuser->affil .
            '  ' .
            $revuser->name .
            "様\n\n" .
            "このたびは、{$conftitle}に投稿された下記の論文\n" .
            "「{$this->paper->title}」\n" .
            "の査読にご協力いただき、ありがとうございました。\n\n";
        /**
         * お礼メールの場合
         * 
         * 開示メールの場合
         * 
         * 催促メールの場合
         */

        $submit = $allsubmit->first();
        $lastsubmit = $allsubmit->last();
        $meta_not_decided = ($lastsubmit->ec_decision_at == null); // まだ最終決定がされていないときtrue

        $task = Task::where('submit_id', $revobj->submit->id)
            ->where('subject_id', $revuser->id)
            ->first();
        // 予定締切：{{ $task->due_date }}

        // 採録
        if ($submit != null) {
            $lastmes = "引き続き、{$conftitle}編集業務へのご協力、よろしくお願いいたします。";
            if ($submit->accept_id == 1) {
                // 採録
                $lastmes = "引き続き、{$conftitle}編集業務へのご協力、よろしくお願いいたします。";
            } elseif ($submit->accept_id == 2) {
                // 条件付き
                if ($revjudgment == '不採録') {
                    $lastmes =
                        "{$revuser->name}様には前回の査読において{$revjudgment}の判定をいただいており、誠に恐縮ではありますが、\n" .
                        "著者から改訂稿が提出されましたら、引き続き、査読をお願いできればと考えております。\n\n";
                } else {
                    $lastmes = "{$revuser->name}様には、著者から改訂稿が提出されましたら、\n引き続き、査読をお願いできればと考えております。\n\n";
                }
                $lastmes .= '何卒よろしくお願いいたします。';
            } elseif ($submit->accept_id == 2) {
                // 不採録
                $lastmes = '査読にご協力いただき、誠にありがとうございました。';
            } else {
                $lastmes = '査読にご協力いただき、誠にありがとうございました。';
            }
            $templates['査読のお礼(判定未確定)'] = [
                'sub' => '査読にご協力いただき、ありがとうございました',
                'mes' => $first_thank . "最終的な判定結果につきましては、後日こちらの掲示板で報告いたします。\n" .
                    "引き続き、{$conftitle}へのご協力をいただけると幸いです。\n" .
                    "どうぞよろしくお願いいたします。",
            ];
            $templates['査読のお礼(判定確定済)'] = [
                'sub' => '査読にご協力いただき、ありがとうございました',
                'mes' => $first_thank . $lastmes,
            ];

            // info($submit);
            $templates['査読結果の開示報告(継続)'] = [
                'sub' => '査読結果を著者に通知しました',
                'mes' => $first_thank .
                    "編集委員会で審議した結果、本論文は「{$submit['accept']['name']}」となりました。\n\n" .
                    "著者に通知した査読結果は、投稿システムメニューの\n" .
                    '「査読」→「最近担当した査読」→「著者に通知した査読結果」' .
                    "からご確認いただけます。\n" .
                    route('role.top', ['role' => 'rev']) .
                    "\n" .
                    "\n" .
                    $lastmes,
            ];
            $templates['査読結果の開示報告(終了)'] = [
                'sub' => '査読結果を著者に通知しました',
                'mes' => $first_thank .
                    "編集委員会で審議した結果、本論文は「{$submit['accept']['name']}」となりました。\n\n" .
                    "著者に通知した査読結果は、投稿システムメニューの\n" .
                    '「査読」→「最近担当した査読」→「著者に通知した査読結果」' .
                    "からご確認いただけます。\n" .
                    route('role.top', ['role' => 'rev']) .
                    "\n" .
                    "\n" .
                    "お忙しいところ査読にご協力いただき、誠にありがとうございました。\n" .
                    "今後とも、{$conftitle}編集業務へのご協力、よろしくお願いいたします。",
            ];
            if ($task) {
                $templates['催促(1)'] = [
                    'sub' => '査読の状況についてお知らせください',
                    'mes' => $first_thank .
                        "当初のお願いでは、査読期限を {$task->due_date} としてお願いしておりましたが、\n" .
                        "現在のところ、査読のご提出が確認できておりません。\n" .
                        "お忙しいところ恐縮ですが、査読の状況についてお知らせいただけますと幸いです。\n" .
                        "どうぞよろしくお願いいたします。\n",
                ];
                $templates['催促(2)'] = [
                    'sub' => '至急ご対応をお願いいたします',
                    'mes' => $first_thank .
                        "当初のお願いでは、査読期限を {$task->due_date} としてお願いしておりましたが、\n" .
                        "現在のところ、査読のご提出が確認できておりません。\n" .
                        "お忙しいところ恐縮ですが、至急ご対応いただけると幸いです。\n" .
                        "どうぞよろしくお願いいたします。\n",
                ];
            }
        }
        return $templates;
    }

    /**
     * 最近一週間の著者掲示板の議論があるかどうかを返す
     */
    public static function recent_bb_accepted()
    {
        // 最近一週間の著者掲示板の議論があるかどうかを返す
        $one_week_ago = now()->subWeek();
        $recent_bbids = BbMes::where('created_at', '>=', $one_week_ago)
            ->select('bb_id')->distinct()->pluck('bb_id')->toArray();
        $accept_papers = Submit::subs_accepted_notpublished([1])->pluck("booth", "paper_id")->toArray();

        return Bb::where('type',1)->whereIn('id', $recent_bbids)->whereIn('paper_id', array_keys($accept_papers))->orderBy('paper_id')->get();
    }

    /**
     * 採択された著者掲示板一覧を返す
     */
    public static function bb_accepted()
    {
        $accept_papers = Submit::subs_accepted_notpublished([1])->pluck("booth", "paper_id")->toArray();

        return Bb::where('type',1)->whereIn('paper_id', array_keys($accept_papers))->orderBy('paper_id')->get();
    }
    public static function submitplain(int $pid, int $type, string $subject, string $mes)
    {
        $paper = Paper::find($pid);
        if ($paper == null) return null;
        $bb = Bb::where("paper_id", $pid)->where("type", $type)->first();
        if ($bb == null) {
            return null;
        }
        $mes = BbMes::create([
            'bb_id' => $bb->id,
            'user_id' => auth()->id(),
            'subject' => $subject,
            'mes' => $mes,
        ]);
        (new BbNotify($bb, $mes))->process_send();
        return $mes;
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
