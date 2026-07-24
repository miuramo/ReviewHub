<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaperStoreRequest;
use App\Mail\Submitted;
use App\Models\Accept;
use App\Models\Bb;
use App\Models\BbMes;
use App\Models\Category;
use App\Models\Confirm;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\File;
use App\Models\Paper;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Status;
use App\Models\Submit;
use App\Models\User;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaperController extends Controller
{
    /**
     * メール送信
     */
    public function sendSubmitted(string $id)
    {
        $aT = $this->author_check($id);
        if ($aT > 0 || auth()->user()->can('manage_review', $id)) {
            $paper = Paper::with(["contacts", "currentsubmit"])->find($id);
            if ($paper->pdf_file_id != 0 && count($paper->validateFiles()) == 0) {
                // status_id が 1（投稿準備中） だったら、 2（投稿完了） にする
                if ($paper->status_id <= 2) {
                    $paper->status_id = 2;
                    if ($paper->currentsubmit->submitted_at == null) {
                        $paper->currentsubmit->submitted_at = now();
                    }
                    $paper->currentsubmit->save();
                    $paper->save();

                    //newSubmit_newTasks from workflow (すでに、Submitは作成済み)
                    // $paper->currentsubmit->newTasks(); // 新規投稿完了時のタスク
                    // 暫定の、査読管理者を設定する。掲示板を作成し、投稿する。
                    // ここでは自動的にタスクを生成しない。かわりに、管理画面で、タスク群生成ボタンを押したら生成する。

                }
                (new Submitted($paper))->process_send();
                // $mail->send();
                if (auth()->user()->can('manage_review', $id)) {
                    return redirect()->route('paper.manage', ['paper' => $paper->id])->with('feedback.success', "投稿状況メールを代理送信しました。");
                }
                return redirect()->route('paper.edit', ['paper' => $paper->id])->with('feedback.success', "投稿状況メールを送信しました。");
            } else {
                return redirect()->route('paper.edit', ['paper' => $paper->id])->with('feedback.error', "投稿状況メールを送信しようとしましたが、まだ投稿が完了していませんでした。下のメッセージをご確認ください。");
            }
        } else {
            return redirect()->route('paper.index')->with('feedback.error', "権限がありません。");
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = Paper::with('currentstatus')->where('owner', Auth::user()->id)->get()->sortBy("id");
        // info($all[0]->currentstatus);
        foreach ($all as $p) {
            $p->validate_accepted();
            Status::updatePaperStatus($p->currentsubmit);
        }

        $coauthor_all = new Collection();
        $u = User::find(Auth::user()->id);
        if ($u != null) {
            $coauthor_all = $u->coauthor_papers();
        }

        return view("paper.index")->with(compact("all", "coauthor_all"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->name == User::$initialName) {
            return redirect()->route('user.profile.edit')->with('feedback.success', '最初に「氏 名」を設定してください。氏と名のあいだには半角スペースをいれてください。');
        }

        $kakunin = Confirm::where('grp', 1)->where('valid', 1)->select('name', 'mes')->get()->pluck('mes', 'name')->toArray();
        $mailkakunin = Confirm::where('grp', 2)->where('valid', 1)->select('name', 'mes')->get()->pluck('mes', 'name')->toArray();

        return view("paper.create")->with(compact("kakunin", "mailkakunin"));
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaperStoreRequest $request)
    {
        // バリデーションエラーが発生した場合
        return $request->shori();
    }
    /**
     * Update the specified resource in storage.
     * 投稿連絡用メールアドレスを更新
     */
    public function update(PaperStoreRequest $request, string $id)
    {
        return $request->shori_update($id);
    }

    /**
     * 他人にみられないように。共著者もOK
     */
    public function author_check(string $id): int
    {
        try {
            $paper = Paper::findOrFail($id);
            if (Gate::allows('show_paper', $paper)) {
                return $paper->getAuthorType();
            }
        } catch (ModelNotFoundException $ex) {
        }
        return -1;
    }
    /**
     * タイトル拡大画像
     *
     * TODO: URLを複雑にする
     */
    public function headimgshow(string $id, string $firsthash)
    {
        // PDFがあるか？複数あったらどうするか？
        // $aT = $this->author_check($id); // 所有確認
        // if ($aT < 0) return $this->noimage();

        // $paper = Paper::findOrFail($id);
        // if (!Gate::allows('show_paper', $paper)) {
        //     abort(403,'IMAGE FORBIDDEN');
        // }

        // $any = File::where('user_id', Auth::user()->id)->where('paper_id', $id)->where('mime', 'application/pdf')->first();
        $any = File::where('paper_id', $id)->where('mime', 'application/pdf')->where('key', 'like', $firsthash . "%")->first();
        if ($any != null) {
            // まだファイルがなければ、準備中をかえす
            if (!file_exists($any->getPdfHeadPath())) {
                $any->makePdfHeadThumb();
                $this->preparing_image();
                return;
            }
            return response()->file($any->getPdfHeadPath()); //->header('Content-Type: image/png');
            // } else {
            // }
        } else {
            return $this->noimage();
            // return;
        }
    }


    /**
     * ドロップ後の、Ajaxでの更新
     */
    public function filelist(string $id)
    {
        // $this->author_check($id); // 所有確認
        $paper = Paper::findOrFail($id);
        if (!Gate::allows('show_paper', $paper)) {
            abort(403, 'forbidden_filelist');
        }
        // PDFがあるか？複数あったらどうするか？
        try {
            $all = File::where('user_id', Auth::user()->id)->where('paper_id', $id)->get()->sortByDesc("id");
            return view("paper.filelist", ["paper" => $id])->with(compact("id", "all"));
        } catch (ModelNotFoundException $ex) {
        }
    }
    private function noimage()
    {
        // ファイルがあれば、それを返す。
        $fn = "nofile.png";
        if (file_exists(storage_path(File::apf() . '/' . $fn))) {
            return response()->file(storage_path(File::apf() . '/' . $fn));
        }
        // ファイルがないので、作成する。
        $im = imagecreatetruecolor(300, 100);
        // $bg = imagecolorallocate($im, 153, 102, 255);
        $bg = imagecolorallocate($im, 255, 255, 230);
        imagefilledrectangle($im, 0, 0, 300, 100, $bg);
        imageAlphaBlending($im, true);
        imageSaveAlpha($im, true);
        $colw = imagecolorallocate($im, 255, 255, 255);
        $colb = imagecolorallocate($im, 100, 0, 0);
        $colr = imagecolorallocate($im, 205, 50, 50);
        $dejavu = public_path('font/DejaVuSans.ttf');

        for ($x = -2; $x < 3; $x++)
            for ($y = -2; $y < 3; $y++)
                ImageTTFText($im, 26, 0, 20 + $x, 47 + $y, $colr, $dejavu, "!!! Warning !!!");

        ImageTTFText($im, 26, 0, 20, 47, $colw, $dejavu, "!!! Warning !!!");
        // imagestring($im, 16, 20, 20, "!!! Warning !!!", $colw);
        // imagestring($im, 5, 20, 20, "!!! Warning !!!", $colw);

        for ($x = -2; $x < 3; $x++)
            for ($y = -2; $y < 3; $y++)
                ImageTTFText($im, 13, 0, 20 + $x, 80 + $y, $colb, $dejavu, "Paper PDF Not Uploaded Yet.");

        ImageTTFText($im, 13, 0, 20, 80, $colw, $dejavu, "Paper PDF Not Uploaded Yet.");

        // imagestring($im, 5, 20, 60, , $colw);
        // ob_start();
        // フォルダがなければ作る
        File::mkdir_ifnot(storage_path(File::apf()));

        imagepng($im, storage_path(File::apf() . '/' . $fn));
        return response()->file(storage_path(File::apf() . '/' . $fn));

        // $img = ob_get_clean();
        // $size = strlen($img);
        // header("Content-Type: image/png");
        // header("Content-Length: {$size}");
        // echo $img;

    }
    // サムネイル準備中の画像
    private function preparing_image()
    {
        $im = imagecreate(300, 90);
        $bg = imagecolorallocate($im, 255, 255, 255);
        $colw = imagecolorallocate($im, 255, 255, 255);
        $colc = imagecolorallocate($im, 102, 255, 255);

        for ($x = -2; $x < 3; $x++)
            for ($y = -2; $y < 3; $y++)
                imagestring($im, 5, 20 + $x, 40 + $y, "Preparing... Wait a moment.", $colc);

        imagestring($im, 5, 20, 40, "Preparing... Wait a moment.", $colw);
        header("Content-Type: image/png");
        imagepng($im);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // 権限チェックする 1=main 2=coauthor
        // $authorType = $this->author_check($id); // 所有確認
        try {
            $paper = Paper::findOrFail($id);
            if (!Gate::allows('show_paper', $paper)) {
                abort(403, 'forbidden_for_others');
            }
            $id_03d = sprintf(env('PID_FORMAT', '%04d'), $id);

            // 回答可能(canedit)または参照可能(readonly)
            $enqs = Enquete::needForSubmit($paper);

            // 既存回答
            $eans = EnqueteAnswer::where('paper_id', $id)->get();
            $enqans = [];
            foreach ($eans as $ea) {
                $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
            }
            //ファイルエラー
            $fileerrors = $paper->validateFiles();

            return view("paper.show", ["paper" => $id])->with(compact("id", "id_03d", "paper", "enqs", "enqans", "fileerrors"));
        } catch (ModelNotFoundException $ex) {
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $paper = Paper::findOrFail($id);
            if (!Gate::allows('edit_paper', $paper)) {
                abort(403, 'forbidden_for_coauthor_or_others');
            }
            $id_03d = sprintf(env('PID_FORMAT', '%04d'), $id);
            $all = File::where('user_id', Auth::user()->id)->where('paper_id', $id)->get()->sortByDesc("id");

            // 回答可能(canedit)または参照可能(readonly)
            $enqs = Enquete::needForSubmit($paper);
            $ids = array_keys($enqs['until']);
            // 既存回答
            $eans = EnqueteAnswer::where('paper_id', $id)->whereIn('enquete_id', $ids)->get();
            $enqans = [];
            foreach ($eans as $ea) {
                $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
            }

            //ファイルエラー
            $fileerrors = $paper->validateFiles();
            // アンケートエラー
            $enqerrors = Enquete::validateEnquetes($paper);

            $cat = Category::find($paper->category_id);
            // 書誌情報エラー(もしshow_bibinfo_btnが1かつ、書誌情報が無い場合)
            if ($cat->show_bibinfo_btn) {
                $biberrors = $paper->validateBibinfo();
            } else {
                $biberrors = [];
            }
            $enqerrors = array_merge($enqerrors, $biberrors);

            $koumoku = Paper::mandatory_bibs(); //必須書誌情報            

            // paper->validate_accepted()でもよいが、せっかくエラーを調べたので、それを使う。
            $paper->accepted = (count($fileerrors) == 0 && count($enqerrors) == 0);
            $paper->save();


            return view("paper.edit", ["paper" => $id])->with(compact("id", "id_03d", "all", "paper", "enqs", "enqans", "fileerrors", "enqerrors", "biberrors", "cat", "koumoku"));
        } catch (ModelNotFoundException $ex) {
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $this->author_check($id); // 所有確認
        $paper = Paper::findOrFail($id);
        // $paper = Paper::where('owner', Auth::user()->id)->where('id', $id)->first();
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        // ロックされてたら、消せない。
        if ($paper->locked) {
            return redirect()->route('paper.index')->with('feedback.error', '削除失敗：投稿はロックされています');
        }
        foreach ($paper->files as $file) {
            $file->remove_the_file();
            $file->delete_me();
        }
        $paper->delete_me();
        return redirect()->route('paper.index')->with('feedback.success', '投稿情報と関連ファイルを削除しました');
    }

    /**
     * Subからの、査読結果の表示
     * @param string $id Submit ID
     */
    public function review(string $id, string $token)
    {
        $sub = Submit::findOrFail($id);
        if (!auth()->user()->can('role_any', 'ec|aec|rev|meta')) {
            if (!Gate::allows('show_paper', $sub->paper)) {
                abort(403, 'forbidden_for_others');
            }
        }
        if ($sub->paper->token() != $token) return abort(403, "Review Browse TOKEN ERROR");
        $accepts = Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        return view('paper.review', ['sub' => $id])->with(compact("sub", "accepts"));
    }

    public function confirmreview(string $id, string $token)
    {
        $sub = Submit::findOrFail($id);
        if ($sub->paper->owner != Auth::user()->id) {
            abort(403, 'forbidden_for_others');
        }
        if ($sub->paper->token() != $token) return abort(403, "Review Confirm TOKEN ERROR");

        // review result を確認済みにする
        $sub->notify_at = ((new DateTime())->format('Y-m-d H:i:s'));
        $sub->save();
        // sub->accept_id から accept を取得
        $accept = Accept::find($sub->accept_id);
        // sub->accept_id が 2であれば次のSubmitをつくる。

        if (strpos($accept->name, '条件付') !== false || $accept->judge == 0) { // 条件付き採録の場合
            $sub->paper->lockMe(false); // Paperロックを解除（じつは開示時点でやっている）
            $sub->paper->status_id = 1; //投稿準備中に戻す。ラウンドは以下で1つ増える
            $sub->paper->save();

            // 古いファイルはアーカイブする
            $sub->paper->archiveFiles();

            // 再投稿期限までの日数を取得する。カテゴリごとに設定できる。
            $resubmit_duration_days = Setting::getary('RESUBMIT_DURATION_DAYS')[$sub->paper->category_id] ?? 30; //
            Submit::factory()->create([
                'paper_id' => $sub->paper->id,
                'category_id' => $sub->paper->category_id,
                'round' => $sub->round + 1,
                'resubmit_until' => date('Y-m-d', strtotime($sub->ec_decision_at . ' + ' . $resubmit_duration_days . ' days')), // 現在からの日時ではなく、1つ前のSubmitの判定通知日時からの日時にした。
                'previous_submit_id' => $sub->id,
            ]); // ->init_reviews();

            return redirect()->route('paper.edit', ['paper' => $sub->paper])->with('feedback.success', '査読結果の確認ありがとうございました。再投稿は指定期日までに論文PDFと回答書PDFをアップロードしてください。（回答書PDFのフォーマット指定はありません）');
        } else if ($accept->judge < 0) { // 不採録や取り下げの場合 // Submit.updateCurrentDecisionに書いてある。採録なら1、条件付きなら2,不採録なら6,取り下げなら7
            // reject の場合、Paperを無効にする
            $sub->paper->status_id = $sub->accept_id + 5; // statusの11不採録,12取り下げに合わせる
            // TODO: 本来は、$sub->accept_id から accept->name を取得して、status を検索し、paper->status_id をセットする。
            $sub->paper->save();
        }

        // $accepts = Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        return redirect()->route('paper.edit', ['paper' => $sub->paper])->with('feedback.success', '査読結果の確認ありがとうございました。');
    }


    /**
     * PDFテキストをドラッグして選択、書誌情報(アブストラクトや英文タイトル)の設定
     */
    public function dragontext(string $id)
    {
        $paper = Paper::findOrFail($id);
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        // PDFがなければ終了
        if ($paper->pdf_file_id == null) {
            return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.error', 'PDFがありません。');
        }

        if ($paper->locked) {
            return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.error', '現在、投稿はロックされているため、書誌情報の設定はできません。');
        }

        $pdftext = $paper->pdf_file->getPdfText();
        $pdftext = $this->normalizeSpaces($pdftext);

        // $pdftext = mb_ereg_replace('\n+',"\n",$pdftext);
        $reps = ["ﬁ" => "fi", "ﬀ" => "ff", "ﬃ" => "ffi"];
        foreach ($reps as $riga => $non) {
            $pdftext = mb_ereg_replace($riga, $non, $pdftext);
        }
        return view('paper.dragontext', ['paper' => $id])->with(compact("pdftext", "paper"));
    }

    /**
     * title, abst, keyword, etitle, eabst, ekeyword 単体での更新
     */
    public function dragontextpost(Request $req, string $id)
    {
        $paper = Paper::findOrFail($id);
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        $target_field = $req->input("target_field");
        $target_value = $req->input("target_value");
        $maydirty = $req->input("maydirty");
        if (strlen($target_value) > 0) {
            $paper->{$target_field} = $target_value;
            // maydirty
            $md = $paper->maydirty;
            if ($maydirty == "true" || (isset($md[$target_field]) && $md[$target_field] == "true")) {
                $md[$target_field] = $maydirty;
                $paper->maydirty = $md;
            }
            $paper->save();
            return json_encode(["field" => $target_field, "value" => $target_value]);
        } else {
            return json_encode(["field" => $target_field, "value" => $paper->{$target_field}]);
        }
    }
    /**
     * 全角文字に挟まれた《半角スペース》に加え、全角と半角（英文字・数字）に挟まれた《半角スペース》も一括削除
     */
    public static function normalizeSpaces(string $val)
    {
        // 前後の空白をトリム
        $val = trim($val);

        // 「全角文字」を表すクラス
        // - 一-龥: 漢字
        // - ぁ-ゔ: ひらがな
        // - ァ-ヴー: カタカナ（長音符含む）
        // - 々〆〤: 特殊文字
        // - ．，。、: 全角句読点
        // - ＀-￯: 全角形（全角記号・英数字など、U+FF01〜U+FF60, U+FFE0〜U+FFE6）
        $zenkaku = '一-龥ぁ-ゔァ-ヴー々〆〤．，。、！-～';

        // 全角 + 全角 の間の半角スペースを削除
        $val = preg_replace(
            '/([' . $zenkaku . '])\s+([' . $zenkaku . '])/u',
            '$1$2',
            $val
        );

        // 全角 + 半角 の間の半角スペースを削除
        $val = preg_replace(
            '/([' . $zenkaku . '])\s+([a-zA-Z0-9])/u',
            '$1$2',
            $val
        );

        // 半角 + 全角 の間の半角スペースを削除
        $val = preg_replace(
            '/([a-zA-Z0-9])\s+([' . $zenkaku . '])/u',
            '$1$2',
            $val
        );

        return $val;
    }

    /**
     * 著者名と所属
     */
    public function update_authorlist(Request $req, string $id)
    {
        $paper = Paper::findOrFail($id);
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        $authorlist = $req->input("authorlist");
        $eauthorlist = $req->input("eauthorlist");
        if (strlen($authorlist) > 5 || strlen($eauthorlist) > 5) {
            $paper->authorlist = $authorlist;
            $paper->eauthorlist = $eauthorlist;
            $paper->save();
            return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.success', '著者名と所属を保存しました。');
        } else {
            return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.error', '著者名と所属を入力してください。');
        }
    }

    /**
     * 投稿Paperのロック状態管理 TODO:
     */
    public function adminlock(Request $req)
    {
        if (!auth()->user()->can('role_any', 'ec|pub|web')) abort(403);
        if ($req->method() === 'POST') {
            if ($req->has('action')) { // action is lock or unlock
                foreach ($req->all() as $k => $v) {
                    if (strpos($k, "targetcat") === 0) {
                        DB::transaction(function () use ($req, $v) {
                            $papers = Paper::where("category_id", $v)->get();
                            foreach ($papers as $paper) {
                                $paper->locked = ($req->input('action') === 'lock');
                                $paper->save();
                            }
                        });
                    }
                }
            }
            return redirect()->route('paper.adminlock')->with('feedback.success', "選択カテゴリの投稿Paperを{$req->input('action')}にしました。（ただし、deleted is null が対象）");
        }

        $fs = ["valid", "locked"];
        $sql1 = "select count(id) as cnt, " . implode(",", $fs);
        $sql1 .= " ,category_id from papers where deleted_at is NULL group by " . implode(",", $fs);
        $sql1 .= " ,category_id order by category_id, " . implode(",", $fs);
        $cols = DB::select($sql1);

        $sql2 = "select id, " . implode(",", $fs);
        $sql2 .= " ,category_id from papers where deleted_at is NULL order by category_id, " . implode(",", $fs);
        $res2 = DB::select($sql2);
        $pids = [];
        foreach ($res2 as $res) {
            if (is_array(@$pids[$res->category_id][$res->valid][$res->locked])) {
                $pids[$res->category_id][$res->valid][$res->locked][] = sprintf(env('PID_FORMAT', '%04d'), $res->id);
            } else {
                $pids[$res->category_id][$res->valid][$res->locked] = [];
                $pids[$res->category_id][$res->valid][$res->locked][] = sprintf(env('PID_FORMAT', '%04d'), $res->id);
            }
        }
        return view('admin.paperlock')->with(compact("cols", "pids"));
    }

    public function manage(Request $req, int $paper_id)
    {
        $paper = Paper::findOrFail($paper_id);
        $files = File::where('paper_id', $paper_id)->orderByDesc('created_at')->get();
        // dd($files);
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403, "you are not a paper manager");

        // 最終判定があれば、それを反映する。本来task complete時にやるが、そのあと修正するかもしれないので。
        $paper->currentsubmit->updateCurrentDecision();
        Status::updatePaperStatus($paper->currentsubmit);

        return view('paper.manage')->with(compact("paper", "files"));
    }

    public function revstatus(Request $req, int $paper_id)
    {
        $paper = Paper::findOrFail($paper_id);
        $files = File::where('paper_id', $paper_id)->orderByDesc('created_at')->get();
        if (!auth()->user()->can('see_review', $paper_id)) abort(403, "you are not a committee manager (cannot see review status)");

        // 最終判定があれば、それを反映する。本来task complete時にやるが、そのあと修正するかもしれないので。
        $paper->currentsubmit->updateCurrentDecision();
        Status::updatePaperStatus($paper->currentsubmit);

        return view('paper.revstatus')->with(compact("paper", "files"));
    }


    public function finishedList()
    {
        return view('paper.finished-list');
    }

    public function manage_papermanager(Request $req, int $paper_id)
    {
        $paper = Paper::findOrFail($paper_id);
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403, "you are not a system manager");

        $users = User::where('valid', 1)->get();
        $revrole = Role::findByIdOrName('rev');
        $candidates = $revrole->users_except_paper_manager($paper_id)->where('valid', 1)->get();
        return view('paper.manage_papermanager')->with(compact("paper", "users", "revrole", "candidates"));
    }

    public function bb_summary(int $paper_id)
    {
        $paper = Paper::findOrFail($paper_id);
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403, "you are not a paper manager");
        // 著者との掲示板、査読者との掲示板、編集委員との掲示板でのやりとりをまとめて時系列順に表示する。

        $bbids = Bb::where('paper_id', $paper_id)
            ->whereNot('type', 3)
            ->orderBy('type', 'asc')
            ->orderBy('rev_id', 'asc')
            ->pluck('id')
            ->toArray();
        $bbmessages = BbMes::with('bb', 'user')->whereIn('bb_id', $bbids)->orderBy('created_at')->get();
        // 関連するメッセージのuser_id をすべて取得する。
        $user_ids = $bbmessages->pluck('user_id')->unique()->toArray();

        $bbs = Bb::where('paper_id', $paper_id)
            ->orderBy('type', 'asc')
            ->orderBy('rev_id', 'asc')
            ->get();

        return view('paper.bb_summary')->with(compact("paper", "bbids", "bbmessages", "bbs", "user_ids"));
    }

    public function change_owner(Request $req, int $paper_id)
    {
        $paper = Paper::findOrFail($paper_id);
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        // $paperの投稿連絡用メールアドレスに設定したアカウントを列挙する。
        $coauthors = new Collection();
        foreach ($paper->contacts as $contact) {
            $u = User::where('email', $contact->email)->first();
            if ($u != null) {
                $coauthors->push($u);
            }
        }
        // postされたら、paperのownerを変更する。
        if ($req->method() === 'POST') {
            $new_owner_id = $req->input('new_owner');
            $new_owner = User::find($new_owner_id);
            if ($new_owner != null) {
                $paper->change_owner($new_owner_id);
                return redirect()->route('paper.index')->with('feedback.success', "投稿の所有者を{$new_owner->name}さんに変更しました。");  
            } else {
                return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.error', "選択されたユーザーが見つかりませんでした。");
            }
        }
        $authorlist = $paper->authorlist_ary();
        return view('paper.change_owner')->with(compact("paper", "coauthors", "authorlist"));
    }

    /**
     * EC/AEC専用: paper.id の変更
     */
    public function change_paper_id(Request $req, int $paper_id)
    {
        if (!Gate::allows('role_any', 'ec|aec')) {
            abort(403, 'forbidden');
        }
        $paper = Paper::withTrashed()->findOrFail($paper_id);

        if ($req->method() === 'POST') {
            $new_id = (int) $req->input('new_paper_id');

            // バリデーション
            if ($new_id <= 0) {
                return redirect()->route('paper.edit', ['paper' => $paper_id])
                    ->with('feedback.error', '新しいIDは1以上の整数を入力してください。');
            }
            if ($new_id === $paper_id) {
                return redirect()->route('paper.edit', ['paper' => $paper_id])
                    ->with('feedback.error', '変更前と同じIDが入力されました。');
            }

            // 新IDが既に存在するか（ソフトデリート済みも含む）
            $exists = Paper::withTrashed()->where('id', $new_id)->exists();
            if ($exists) {
                return redirect()->route('paper.edit', ['paper' => $paper_id])
                    ->with('feedback.error', "投稿ID {$new_id} は既に存在するため変更できません。");
            }

            try {
                DB::transaction(function () use ($paper_id, $new_id) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::table('papers')->where('id', $paper_id)->update(['id' => $new_id]);
                    foreach ([
                        'files', 'submits', 'bbs', 'reviews',
                        'rev_conflicts', 'paper_contact', 'enquete_answers',
                        'paper_manager',
                    ] as $tbl) {
                        DB::table($tbl)->where('paper_id', $paper_id)->update(['paper_id' => $new_id]);
                    }
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                });
            } catch (\Throwable $e) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                Log::error("change_paper_id failed: {$e->getMessage()}");
                return redirect()->route('paper.edit', ['paper' => $paper_id])
                    ->with('feedback.error', 'ID変更中にエラーが発生しました: ' . $e->getMessage());
            }

            return redirect()->route('paper.edit', ['paper' => $new_id])
                ->with('feedback.success', "投稿IDを {$paper_id} から {$new_id} に変更しました。");
        }

        // GETの場合: 変更可能かどうかのチェック情報を返す
        return redirect()->route('paper.edit', ['paper' => $paper_id]);
    }

    /**
     * EC/AEC専用: 著者リストから新ユーザーを作成してオーナーを変更
     */
    public function create_user_owner_ec(Request $req, int $paper_id)
    {
        if (!Gate::allows('role_any', 'ec|aec')) {
            abort(403, 'forbidden');
        }
        $paper = Paper::findOrFail($paper_id);

        $req->validate([
            'author_index' => ['required', 'integer', 'min:0'],
            'new_user_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($req->input('new_user_email')));

        // メールアドレスが既存ユーザと重複していないか確認
        if (User::where('email', $email)->exists()) {
            return redirect()->route('paper.edit', ['paper' => $paper_id])
                ->with('feedback.error', "メールアドレス「{$email}」は既に登録されているユーザです。既存ユーザへの委譲には上のフォームをご利用ください。");
        }

        $authorlist = $paper->authorlist_ary();
        $idx = (int) $req->input('author_index');
        if (!isset($authorlist[$idx])) {
            return redirect()->route('paper.edit', ['paper' => $paper_id])
                ->with('feedback.error', '著者リストのインデックスが無効です。');
        }
        $author = $authorlist[$idx];
        $name  = $author[0] ?? '';
        $affil = $author[1] ?? '';

        // ユーザ作成
        $new_user = User::create([
            'name'     => $name,
            'affil'    => $affil,
            'email'    => $email,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
            'email_verified_at' => now(),
        ]);

        // オーナー変更
        $paper->change_owner($new_user->id);

        return redirect()->route('paper.index')
            ->with('feedback.success', "新しいユーザー「{$name}」（{$email}）を作成し、投稿の所有者を変更しました。");
    }

    /**
     * Admin専用: ユーザー検索 (JSON)
     */
    public function user_search_admin(Request $req, int $paper_id)
    {
        if (!Gate::allows('admin')) {
            abort(403, 'forbidden');
        }
        $q = trim($req->input('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }
        $users = User::where(function ($query) use ($q) {
            $query->where('name', 'like', '%' . $q . '%')
                  ->orWhere('email', 'like', '%' . $q . '%')
                  ->orWhere('affil', 'like', '%' . $q . '%');
        })
        ->select('id', 'name', 'affil', 'email')
        ->orderBy('id')
        ->limit(30)
        ->get();

        return response()->json($users);
    }

    /**
     * Admin専用: 既存ユーザーを選択してオーナーを変更
     */
    public function assign_owner_admin(Request $req, int $paper_id)
    {
        if (!Gate::allows('admin')) {
            abort(403, 'forbidden');
        }
        $paper = Paper::findOrFail($paper_id);

        $req->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $new_owner = User::findOrFail($req->input('user_id'));
        $paper->change_owner($new_owner->id);

        return redirect()->route('paper.index', ['paper' => $paper_id])
            ->with('feedback.success', "投稿の所有者を「{$new_owner->name}」（{$new_owner->email}）に変更しました。");
    }
}
