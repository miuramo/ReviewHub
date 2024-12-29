<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
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
     */
    public function create(Request $req)
    {
        // info($req->all());
        $review = Review::find($req->review);
        $paper = Paper::find($review->paper->id);
        $revuid = $req->revuid;
        Task::createReviewTask($paper->currentSubmit, $revuid);
        $review->request_at = now();
        $review->save();
        return redirect()->route('paper.manage',['paper' => $paper])->with('feedback.success', '査読タスクを作成しました');
        //
    }

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
