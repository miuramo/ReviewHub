<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Bb;
use App\Models\Paper;
use App\Models\Review;
use App\Models\Submit;
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
        $paper = Paper::with('currentSubmit')->find($review->paper_id);
        $revuid = $req->revuid;
        $task = Task::createReviewTask($paper->currentSubmit, $revuid);
        if ($review->target == 2){
            $task->due_date = $task->addDaysToDate(5); // 最終判定は5日
        } else if ($review->target == 0){
            $task->due_date = $task->addDaysToDate(24); // 通常査読は24日
        } else { // case of 1
            $task->due_date = $task->addDaysToDate(10); // 現在は使用していないが、メタの場合は、10日
        }
        $task->save();

        $review->request_at = now();
        $review->save();
        Bb::add_message(
            $paper->currentSubmit,
            2,
            '【日本創造学会論文誌】査読のお願い',
            "査読者のかたへ\nお忙しいところすみませんが、日本創造学会論文誌に投稿された論文の査読をお願いいたします。\n\n以下のURLから、確認してください。\n" . env('APP_URL') . "/role/rev/top",
            $review->id,
        );

        return redirect()->route('paper.manage', ['paper' => $paper])->with('feedback.success', '査読タスクを作成しました');
        //
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


        if ($ret) {
            Bb::add_message(
                $task->submit,
                2,
                '査読完了の報告',
                "投稿管理者のかたへ\n査読報告の編集が完了しましたことを、報告します。",
                $req->rev_id,
            );
            return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.success', '査読へのご協力ありがとうございました。');
        } else {
            return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.error', 'タスク処理に失敗しました');
        }
        return redirect()->route('role.top', ['role' => $jumprole])->with('feedback.success', 'Task completed successfully');
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
