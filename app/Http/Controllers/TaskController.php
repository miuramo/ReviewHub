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
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        // info($req->all());
        $review = Review::find($req->review);
        $review->do_assign(); // メールも送信する

        $paper = Paper::with('currentSubmit')->find($review->paper_id);
        return redirect()->route('paper.manage', ['paper' => $paper])->with('feedback.success', '査読タスクを作成しました');
        //
    }

    public function sendrequest(int $review, int $revuid)
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        $review = Review::find($review);
        // 依頼日時
        if ($review->request_at == null) {
            $review->request_at = now();
            $review->save();
        }

        $reviewer = $review->user;
        $paper = Paper::with('currentSubmit')->find($review->paper_id);
        (new ReviewRequest($paper, $reviewer, $review))->process_send();

        return redirect()->route('paper.manage', ['paper' => $paper])->with('feedback.success', '査読依頼メールを送信しました');
    }

    public function sendfirstmessage(int $review, int $revuid)
    {
        if (!auth()->user()->can('role_any', 'ec')) abort(403);
        MailTemplate::send_first_message($revuid);
        $review = Review::find($review);
        $paper = Paper::with('currentSubmit')->find($review->paper_id);
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
}
