<?php

namespace App\Http\Controllers;

use App\Exports\PapersExport4Hiroba;
use App\Exports\PapersExportFromView;
use App\Jobs\ExportHintFileJob;
use App\Jobs\Test9w;
use App\Mail\DisableEmail;
use App\Mail\ForAuthor;
use App\Models\Bb;
use App\Models\BbMes;
use App\Models\Contact;
use App\Models\EnqueteAnswer;
use App\Models\File;
use App\Models\LogAccess;
use App\Models\LogCreate;
use App\Models\LogForbidden;
use App\Models\LogModify;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Submit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

use ZipArchive;

class ManagerController extends Controller
{

    public function rebuildPDFThumb()
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);
        File::rebuildPDFThumb();
        return redirect()->route('admin.dashboard');
    }


    /**
     * CROP Imageの確認と再作成
     */
    public function paperlist_headimg()
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        $all = Paper::whereNotNull('pdf_file_id')->get();

        return view('admin.paperlist_headimg')->with(compact("all"));
    }
    public function paperlist_headimg_recrop()
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        $all = Paper::whereNotNull('pdf_file_id')->get();
        foreach ($all as $paper) {
            $paper->pdf_file->altimg_recrop();
        }
        return redirect()->route('admin.paperlist_headimg')->with('feedback.success', 'タイトル画像の再クロップを開始しました。');
    }




    public function mailtest()
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        if (!auth()->user()->id == 1) abort(403);
        $papers = Paper::all();
        $mts = MailTemplate::all();
        foreach ($mts as $mt) {
            foreach ($papers as $paper) {
                (new ForAuthor($paper, $mt))->process_send();
                // Mail::send(new ForAuthor($paper, $mt));
            }
        }
        return redirect()->route('admin.admindb');
    }

    public function test9w()
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        Test9w::dispatch();
        // $this->ocr9w();
        ExportHintFileJob::dispatch();
        return redirect()->route('admin.dashboard')->with('feedback.success', 'テストQueueを実行しました。再読み込みして各種設定→LAST_QUEUEWORK_DATEが更新されていることを確認してください。');
    }

    public function ocr9w()
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        File::rebuildOcrTsv();
        // OcrJob::dispatch();
        return redirect()->route('admin.dashboard')->with('feedback.success', 'OCR Queueを実行しました。');
    }

    public function paperauthorhead(Request $req)
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        $sets = Setting::where("name", "like", "SKIP_HEAD_%")->where("valid", true)->get();
        $papers = Paper::whereNotNull("pdf_file_id")->get();
        if ($req->input('action') == 'titleupdate') { // 第3要素のタイトルで書き換える
            foreach ($papers as $paper) {
                $title = $paper->title_candidate();
                foreach ($sets as $set) {
                    $title = str_replace($set->value, "", $title);
                }
                // authorheadが含まれていたら
                $pos = mb_strpos($title, mb_substr($paper->authorhead, 0, 2));
                if ($pos > -1 && $pos !== false) {
                    $title = mb_substr($title, 0, $pos);
                }
                $paper->title = $title;
                $paper->save();
            }
            return redirect()->route('admin.paperauthorhead')->with('feedback.success', 'タイトルを一括更新しました');
        }
        if ($req->input('action') == 'setfirstauthor_ifnull') { // ★★第一著者未設定★★ について、第一著者の苗字を設定する
            foreach ($papers as $paper) {
                if (mb_strlen($paper->authorhead) < 1) {
                    $myouji = explode(" ", $paper->paperowner->name)[0];
                    $paper->authorhead = $myouji;
                    $paper->save();
                }
            }
            return redirect()->route('admin.paperauthorhead')->with('feedback.success', '★★第一著者未設定★★ について、第一著者の苗字を設定しました');
        }
        if ($req->has('authorheads')) { // テキストエリアがある場合
            $lines = explode("\n", $req->input('authorheads'));
            $lines = array_map("trim", $lines);
            foreach ($lines as $n => $line) {
                $ary = explode(";;", $line);
                $ary = array_map("trim", $ary);
                $paper = Paper::find($ary[0]);
                $paper->authorhead = $ary[1];
                $paper->save();
                // info($ary);
            }
            return redirect()->route('admin.paperauthorhead')->with('feedback.success', 'updated');
        }

        return view('admin.paperauthorhead')->with(compact("papers", "sets"));
    }

    /**
     * テスト用：ワークフローを進める
     */
    public function submit_proceed(Request $req, int $subid)
    {
        if (!auth()->user()->can('role_any', 'ec|aec|meta')) abort(403);
        $submit = Submit::find($subid);
        if ($req->action == 'reset') {
            foreach ($submit->tasks as $task) {
                $task->completed = false;
                $task->completed_at = null;
                $task->require_approve = false;
                $task->object_id = null;
                $task->approved = false;
                $task->approved_at = null;
                if ($task->workflow->id > 1) {
                    $task->subject_id = null;
                }
                $task->save();
            }
            $submit->paper->status_id = 2; //投稿完了
            $submit->paper->save();
            $submit->ec_decision_at = null;
            $submit->notify_at = null;
            $submit->save();
            $submit->paper->lockAll(false);
            return redirect()->route('sub.show', ['sub' => $subid])->with('feedback.success', "すべてリセットしました。");
        } else {

            $phase = $req->phase; // 
            foreach ($submit->tasks as $task) {
                if ($task->approved) continue;
                if ($task->id > $phase) break;
                $task->random_proceed(); // いい感じに進める
            }
        }

        return redirect()->route('sub.show', ['sub' => $subid])->with('feedback.success', "自動ですすめました。");
    }

    /**
     * 受領通知（査読に進みます）を送信する
     */
    public function submit_sendreceipt(int $subid)
    {
        if (!auth()->user()->can('role_any', 'ec|aec|meta')) abort(403);
        $submit = Submit::find($subid);
        if ($submit->paper->status_id < 2) {
            return back()->with('feedback.error', "受領通知を送信しようとしましたが、まだ投稿完了していません。");
        }
        $myname = auth()->user()->name;
        $paper = $submit->paper;
        $numround = $submit->round;
        if ($numround > 1) {
            $mesround = "{$numround}回目の";
        } else {
            $mesround = "";
        }
        Bb::add_message(
            $submit,
            1, // type=1 投稿管理者と著者の掲示板
            "【論文編集委員会より】論文を受領いたしました",
            "{$paper->paperowner->affil}  {$paper->paperowner->name}様\n\n日本創造学会論文編集委員会の {$myname} と申します。\n\n" .
                "論文「{$submit->paper->title}」を受領いたしました。\n" .
                "{$mesround}査読に進みますので、しばらくお待ちください。\n\n"
        );
        $submit->receiptsent_at = now();
        $submit->save();
        return back()->with('feedback.success', "受領通知を送信しました。");
    }
    /**
     * 受領通知（最終原稿ありがとう）を送信する
     */
    public function submit_sendreceipt_final(int $subid)
    {
        if (!auth()->user()->can('role_any', 'ec|aec|meta')) abort(403);
        $submit = Submit::find($subid);
        if ($submit->paper->status_id < 2) {
            return back()->with('feedback.error', "受領通知を送信しようとしましたが、まだ投稿完了していません。");
        }
        $myname = auth()->user()->name;
        $paper = $submit->paper;
        Bb::add_message(
            $submit,
            1, // type=1 投稿管理者と著者の掲示板
            "【論文編集委員会より】論文を受領いたしました",
            "{$paper->paperowner->affil}  {$paper->paperowner->name}様\n\n日本創造学会論文編集委員会の {$myname} と申します。\n\n" .
                "論文「{$submit->paper->title}」の最終原稿を受領いたしました。\n" .
                "なお、論文誌の発行（J-STAGE掲載）は、年に2回（6月・12月）のスケジュールを予定しております。\n\n" .
                "出版までしばらくお時間をいただく場合がありますが、どうかご了承ください。\n\n"
        );
        return back()->with('feedback.success', "受領通知を送信しました。");
    }


    /**
     * 査読結果開示通知を送信する
     */
    public function submit_senddisclose(int $subid)
    {
        if (!auth()->user()->can('role_any', 'ec|aec|meta')) abort(403);
        $submit = Submit::find($subid);
        if ($submit->ec_decision_at == null) {
            return back()->with('feedback.error', "エラー！このボタンを押すより前に、査読結果を著者に開示してください。");
        }
        $myname = auth()->user()->name;
        $paper = $submit->paper;
        $numround = $submit->round;
        if ($numround > 1) {
            $mesround = "{$numround}回目の";
        } else {
            $mesround = "";
        }
        if ($submit->accept_id == 2){
            $conditional_accept_message = "再投稿期限は、本日より30日後の " . date('Y年m月d日', strtotime('+30 days')) . " です。\n";
            $conditional_accept_message .= "（再投稿期限は、著者による査読結果確認のあと、投稿一覧画面でも確認することができます。）\n\n";
            $conditional_accept_message .= "再投稿の方法は、投稿一覧 → 論文サムネイル画像をクリック → 投稿編集画面上部 に記載されますので、こちらの指示に従ってください。\n\n";
        } else {
            $conditional_accept_message = "";
        }
        Bb::add_message(
            $submit,
            1, // type=1 投稿管理者と著者の掲示板
            "【論文編集委員会より】第{$numround}回査読結果の開示",
            "{$paper->paperowner->affil}  {$paper->paperowner->name}様\n\n日本創造学会論文編集委員会の {$myname} と申します。\n\n" .
                "投稿いただいておりました論文「{$submit->paper->title}」の、\n第{$numround}回査読結果を開示いたしました。\n\n" .
                "査読結果は、投稿一覧 → 第{$numround}回査読結果（オレンジ色のボタン）から、確認してください。\n" . 
                "査読結果を確認されましたら、査読結果ページ上部の「査読結果を確認した」ボタンを押してください。\n\n" .
                $conditional_accept_message .
                "ご不明な点がありましたら、掲示板にてご質問ください。\n\n"
        );
        return back()->with('feedback.success', "査読結果の開示通知を送信しました。");
    }

    /**
     * 統計情報
     */
    public function stats()
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);

        // reviews について、group by user_id, target で集計し、各査読者の担当した論文数、レビュー数を取得
        $review_stats = Review::select('user_id', 'target', DB::raw('count(*) as review_count'))
            ->groupBy('user_id', 'target')
            ->orderBy('target','desc')->orderBy('review_count', 'desc')
            ->get();
        $users = User::whereIn('id', $review_stats->pluck('user_id'))->get()->keyBy('id');
        return view('admin.stats')->with(compact('review_stats', 'users'));
    }
}
