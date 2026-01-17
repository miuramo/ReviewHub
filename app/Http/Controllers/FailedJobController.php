<?php

namespace App\Http\Controllers;

use App\Models\FailedJob;
use Illuminate\Http\Request;

class FailedJobController extends Controller
{
    //
    public function index($all = false)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);

        if ($all) {
            $failedJobs = FailedJob::all();
        } else {
            $failedJobs = FailedJob::where('is_read', false)->get();
        }
        return view('failed_jobs.index', compact('failedJobs', 'all'));
    }

    public function markAsRead(Request $request, $id)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);

        $failedJob = FailedJob::findOrFail($id);
        $failedJob->is_read = true;
        $failedJob->save();

        return redirect()->route('admin.failed_jobs')->with('feedback.success', 'Failed job marked as read.');
    }
}
