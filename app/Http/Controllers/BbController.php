<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBbRequest;
use App\Http\Requests\UpdateBbRequest;
use App\Models\Bb;
use App\Models\BbMes;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Submit;
use Illuminate\Http\Request;

class BbController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('role_any', 'admin|manager|ec')) abort(403);

        for ($i = 1; $i < 4; $i++) {
            $bbs[$i] = Bb::with("paper")->with("category")->where("type", $i)->get();
        }
        return view("bb.index")->with(compact("bbs"));
        //
    }

    public function index_for_pub()
    {
        if (!auth()->user()->can('role_any', 'admin|manager|ec|pub')) abort(403);

        $i = 3;
        $bbs[$i] = Bb::with("paper")->with("category")->where("type", $i)->get();

        return view("bb.index_for_pub")->with(compact("bbs"));
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $serial)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|ec|rev|meta')) abort(403, '権限がありません');
        $bb = Bb::gen_from_serial($serial);
        return redirect()->route('bb.show', ['bb' => $bb->id, 'key' => $bb->key]);
    }

    /**
     * Store a newly created resource in storage.
     * 掲示板作成
     */
    public function store(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|ec')) abort(403);
        // $catid = $req->input("catid");
        // $type = $req->input("type");
        $pids = trim($req->input("pids"));
        $ary = Paper::whereIn('id', explode(",", $pids))->get();
        foreach ($ary as $n => $paper) {
            Bb::make_bb($paper->currentsubmit);
        }
        return redirect()->route('bb.index')->with('feedback.success', "作成しました。");
    }

    /**
     * Display the specified resource.
     */
    public function show(int $bbid, string $key)
    {
        $bb = Bb::with("messages")->with("paper")->where('id', $bbid)->where('key', $key)->first();
        if ($bb == null) abort(403, 'bb not found');
        // type=2(査読掲示板) のとき、ユーザのrevid をセット
        // ちなみに、type=1 は著者とEC、type=3はECと全査読者、type=4はECOnly
        if ($bb->type == 2) {
            $rev = Review::where("paper_id", $bb->paper_id)->where("category_id", $bb->category_id)->where("user_id", auth()->id())->first();
            if ($rev == null) $revid = null;
            else $revid = $rev->id;
            // 利害関係者は掲示板を見れないようにする
            $rigais = RevConflict::arr_pu_rigai();
            if (isset($rigais[$bb->paper->id][auth()->id()]) && $rigais[$bb->paper->id][auth()->id()] < 3) {
                return abort(403, 'authors conflict');
            }
        } else {
            $revid = null;
        }
        // $isEC = auth()->user()->can('role_any', 'ec');
        $isEC = auth()->user()->can('manage_review', $bb->paper_id);
        return view("bb.show")->with(compact("bb", "revid", "isEC"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bb $bb)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBbRequest $request, Bb $bb)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bb $bb)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|ec')) abort(403);
        Bb::truncate();
        BbMes::truncate();
        return redirect()->route('bb.index')->with('feedback.success', "全削除しました。");
    }

    /**
     * 種別ごとに削除
     */
    public function destroy_bytype(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|ec|pub')) abort(403);
        $type = $req->input("type");
        if (!auth()->user()->can('role_any', 'admin|manager|ec')) {
            if ($type != 3) abort(403);
        }
        $target_bbids = Bb::where("type", $type)->pluck("id");
        BbMes::whereIn("bb_id", $target_bbids)->delete();
        Bb::where("type", $type)->delete();
        $for_pub = $req->input("for_pub");
        if ($for_pub) {
            return redirect()->route('bb.index_for_pub')->with('feedback.success', "出版掲示板をすべて削除しました。");
        }
        return redirect()->route('bb.index')->with('feedback.success', "削除しました。");
    }

    /**
     * 一括送信フォーム表示・処理
     */
    public function multisubmit(Request $req, int $type = 1)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);
        $preface = "論文編集委員会より、お知らせします。\r\n日本創造学会論文誌 第29巻が発行されました！";
        $subject = "論文誌が発行されました";

        $accept_papers = Submit::subs_accepted_notpublished([1])->pluck("booth", "paper_id")->toArray();

        $csv = "";
        foreach ($accept_papers as $paper_id => $booth) {
            $csv .= '"=======' . "\r\n" . sprintf("%03d", $paper_id) . "\r\n";
            $csv .= "=======" . "'" . "\r\n";
        }
        if ($req->has('action')) {
            $lines = explode("\r\n", $req->csv);
            $out = "";
            $buf = "";
            $subject = "";
            $pid = 0;
            $count = 0;
            $bufary = [];
            foreach ($lines as $n => $l) {
                $line = $l; // trim($l);
                if (preg_match("/={6,30}/", $line)) {
                    if ($pid == 0) {
                        continue;
                    }
                    $bufary[] = [
                        "PID" => $pid,
                        "subject" => trim($req->subject),
                        "body" => $req->preface . "\n" . $buf
                    ];
                    $buf = $subject = "";
                    $pid = 0;
                } elseif (preg_match("/^[0-9０-９]+$/", $line)) {
                    $pid = intval(mb_convert_kana($line, 'n', 'UTF-8'));
                    $count = 0;
                } else {
                    $buf .= $line . "\r\n";
                }
                $count++;
            }
            if ($req->input('action') == "submit") {
                foreach ($bufary as $n => $ba) {
                    Bb::submitplain(
                        $ba['PID'],
                        $type,
                        $ba['subject'],
                        $ba['body']
                    );
                }
                return redirect()->route('bb.multisubmit', ['type' => $type])->with('feedback.success', "一括送信しました。")->with(compact("out", "bufary", "preface", "subject", "csv"));
            } else {
                $preface = $req->preface;
                $subject = $req->subject;
                $csv = $req->csv;
                return view('bb.multisubmit', ['type' => $type])->with(compact("out", "bufary", "preface", "subject", "csv"));
            }
        }
        return view('bb.multisubmit', ['type' => $type])->with(compact("type", "preface", "subject", "csv"));
    }
}
