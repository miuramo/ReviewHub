<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
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
     */
    public function create()
    {
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
     */
    public function update(Request $req, Task $task)
    {
        // info($req->all());
        // info($task);
        // info($task->workflow);
        // 本来は、Taskを通じて、Workflowに従って処理してほしい
        $task = Task::with(['workflow', 'submit','next','next2'])->find($task->id);
        $ret = $task->process($req);
        if ($ret) {
            return redirect()->route('role.top', ['role' => $req->redirect_role])->with('feedback.success', 'Task completed successfully');
        } else {
            return redirect()->route('role.top', ['role' => $req->redirect_role])->with('feedback.error', 'Task failed');
        }
        return redirect()->route('role.top', ['role' => $req->redirect_role])->with('feedback.success', 'Task completed successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
