<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Mail\ReviewRequest;
use App\Models\Bb;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\Review;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     * 査読開始ボタンを押したとき
     * 査読開始ボタンは、tswitch (in rstatus)
     */
    public function create(Request $req)
    {
        // if (!auth()->user()->can('role_any', 'ec')) abort(403);
        // info($req->all());
        $review = Review::find($req->review);
        $paper_id = $review->paper_id;
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403);
        $review->do_assign(); // メールも送信する

        $paper = Paper::with('currentSubmit')->find($review->paper_id);
        return redirect()->route('paper.manage', ['paper' => $paper])->with('feedback.success', '査読タスクを作成しました');
        //
    }

    /**
     * 依頼メール送信前の本文確認・編集画面
     */
    public function sendrequest_confirm(int $review, int $revuid)
    {
        $reviewModel = Review::find($review);
        $paper_id = $reviewModel->paper_id;
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403);

        $reviewer = $reviewModel->user;
        $paper = Paper::with('currentSubmit')->find($reviewModel->paper_id);
        $mail = new ReviewRequest($paper, $reviewer, $reviewModel);

        return view('task.sendrequest_confirm')->with([
            'review' => $reviewModel,
            'revuid' => $revuid,
            'subject' => $mail->subject,
            'body' => $mail->body,
            'reviewer' => $reviewer,
        ]);
    }

    /**
     * 確認画面で編集された本文をセッションに保存し、実際の送信処理（GET /task_sendrequest/...）へリダイレクトする。
     * 送信処理のURLをアクセスログの集計（LogAccess::dates_sendrequest）と合わせるため、本処理では送信自体は行わない。
     */
    public function sendrequest_prepare(Request $req, int $review, int $revuid)
    {
        $reviewModel = Review::find($review);
        $paper_id = $reviewModel->paper_id;
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403);

        $req->session()->put("sendrequest_body_{$review}_{$revuid}", (string) $req->input('body'));
        $req->session()->put("sendrequest_subject_{$review}_{$revuid}", (string) $req->input('subject'));

        return redirect()->route('task.sendrequest', ['review' => $review, 'revuid' => $revuid]);
    }

    /**
     * 確認画面で編集中の件名・本文をもとに、Markdownメールを画面プレビューする（送信は行わない）
     */
    public function sendrequest_preview(Request $req, int $review, int $revuid)
    {
        $reviewModel = Review::find($review);
        $paper_id = $reviewModel->paper_id;
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403);

        $reviewer = $reviewModel->user;
        $paper = Paper::with('currentSubmit')->find($reviewModel->paper_id);
        $mail = new ReviewRequest($paper, $reviewer, $reviewModel, (string) $req->input('body'), (string) $req->input('subject'), preview: true);

        $to = implode(', ', (array) ($mail->mail_to_cc['to'] ?? []));
        $cc = implode(', ', (array) ($mail->mail_to_cc['cc'] ?? []));
        $bcc = implode(', ', (array) ($mail->mail_to_cc['bcc'] ?? []));
        $header = view('task.sendrequest_preview_header')->with(compact('to', 'cc', 'bcc') + ['subject' => $mail->subject])->render();

        $html = $mail->render();
        // <body> 直後にTo/Cc/Bccの表示を差し込む
        if (preg_match('/<body[^>]*>/i', $html)) {
            $html = preg_replace('/(<body[^>]*>)/i', '$1' . $header, $html, 1);
        } else {
            $html = $header . $html;
        }
        return $html;
    }

    public function sendrequest(int $review, int $revuid)
    {
        // if (!auth()->user()->can('role_any', 'ec')) abort(403);
        $review = Review::find($review);
        $paper_id = $review->paper_id;
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403);
        // 依頼日時
        if ($review->request_at == null) {
            $review->request_at = now();
            $review->save();
        }

        $bodyOverride = session()->pull("sendrequest_body_{$review->id}_{$revuid}");
        $subjectOverride = session()->pull("sendrequest_subject_{$review->id}_{$revuid}");

        $reviewer = $review->user;
        $paper = Paper::with('currentSubmit')->find($review->paper_id);
        (new ReviewRequest($paper, $reviewer, $review, $bodyOverride, $subjectOverride))->process_send();

        return redirect()->route('paper.manage', ['paper' => $paper])->with('feedback.success', '査読依頼メールを送信しました');
    }

    public function sendfirstmessage(int $review, int $revuid)
    {
        $review = Review::find($review);
        $paper_id = $review->paper_id;
        if (!auth()->user()->can('manage_review', $paper_id)) abort(403);
        // if (!auth()->user()->can('role_any', 'ec')) abort(403);
        MailTemplate::send_first_message($revuid);
        $paper = Paper::with('currentSubmit')->find($paper_id);
        return redirect()->route('paper.manage', ['paper' => $paper])->with('feedback.success', 'パスワード設定方法（最初のログインの方法）を送信しました');
    }

    // public function createhantei(int $sub_id)
    // {
    //     if (!auth()->user()->can('role_any', 'ec')) abort(403);
    //     $sub = Submit::find($sub_id);
    //     // info($sub);
    //     // ここに柔軟な査読者の割り当てと査読タスク生成の処理を書く
    //     $task = Task::create([
    //         'submit_id' => $sub->id,
    //         'workflow_id' => 11,
    //         'subject_id' => auth()->user()->id, 
    //         'object_id' => auth()->user()->id,
    //     ]);
    //     $rev = Review::firstOrCreate([
    //         'submit_id' => $sub->id,
    //         'paper_id' => $sub->paper->id,
    //         'category_id' => $sub->paper->category_id,
    //         'target' => 2,
    //         'user_id' => auth()->user()->id,
    //     ]);

    //     $paper = Paper::find($sub->paper->id);
    //     return redirect()->route('paper.manage',['paper' => $paper])->with('feedback.success', '判定タスクを作成しました');
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * 査読完了を報告する
     * from: 
     */
    public function update(Request $req, Task $task)
    {
        // 本来は、Taskを通じて、Workflowに従って処理してほしい
        $task = Task::with(['workflow', 'submit'])->find($task->id);
        $ret = $task->process($req);

        $jumprole = $req->redirect_role;
        $jumprole = str_replace('1', '', $jumprole);
        $jumprole = str_replace('2', '', $jumprole);
        $jumprole = str_replace('3', '', $jumprole);

        $name_of_manager = \App\Models\Setting::getValue("NAME_OF_MANAGER");

        if ($ret) {
            if ($task->workflow->task == "submit") {
                Bb::add_message(
                    $task->submit,
                    2,
                    '査読完了の報告',
                    "{$name_of_manager}のかたへ\n査読報告の編集が完了しましたことを、報告します。",
                    $req->rev_id,
                );
                return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.success', '査読へのご協力ありがとうございました。');
            } else {
                return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.success', 'Task completed successfully');
            }
        } else {
            return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.error', 'タスク処理に失敗しました');
        }
        // return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.success', 'Task completed successfully');
    }

    /**
     * 承認画面からの承認または辞退があったとき
     */
    public function approve(Request $req, Task $task)
    {
        $jumprole = $req->redirect_role;
        $jumprole = str_replace('1', '', $jumprole);
        $jumprole = str_replace('2', '', $jumprole);
        $jumprole = str_replace('3', '', $jumprole);
        if ($req->approve) {
            $task->approve($req, true);
            return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.success', 'タスクを承認しました');
        } else {
            // 不承認メールを送る
            $task->approve($req, false);
            return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.success', 'タスクを辞退しました');
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }

    /**
     * 完了したタスクを、未完了に戻す
     */
    public function revert(Task $task)
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        $task->revert();
        return redirect()->back()->with('feedback.success', 'タスクを未完了に戻しました');
    }
}
