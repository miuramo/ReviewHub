<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogAccessRequest;
use App\Http\Requests\UpdateLogAccessRequest;
use App\Models\LogAccess;
use App\Models\Task;
use App\Models\User;

class LogAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($paper = null)
    {
        if (!auth()->user()->can('role_any', 'ec')) {
            abort(403, 'Unauthorized action.');
        }
        // info($users);
        // return view('log_access.index', compact('logs','user','users'));
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
    public function store(StoreLogAccessRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($review)
    {
        if (!auth()->user()->can('role_any', 'ec')) {
            abort(403, 'You are not editorial committee.');
        }
        $revobj = \App\Models\Review::find($review);
        if (!$revobj) {
            abort(404, 'Review not found.');
        }
        if (!auth()->user()->can('manage_review', $revobj->paper->id)) abort(403, "you are not a manager");
        $user = $revobj->user->id;
        $fileid = $revobj->paper->pdf_file_id;
        $task = Task::where('submit_id', $revobj->submit->id)
            ->where('subject_id', $user)
            ->first();
        if ($user) {
            $logs = LogAccess::where('uid', $user)->where(function($query) use ($fileid, $review, $task) {
                $query->orWhere('url', 'like', "/file/{$fileid}/show/%");
                $query->orWhere('url', 'like', "/review/{$review}%");
                $query->orWhere('url', 'like', "/task/{$task->id}%");
            })->latest()->paginate(1000);
            
            $users = User::select('id','name')->get()->pluck('name', 'id')->toArray();
            return view('log_access.show', compact('logs', 'user','users','review'));
        }
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LogAccess $logAccess)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLogAccessRequest $request, LogAccess $logAccess)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LogAccess $logAccess)
    {
        //
    }
}
