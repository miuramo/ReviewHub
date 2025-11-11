<?php

namespace App\Jobs;

use App\Models\Submit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RoRJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * PDFのときだけ、ジョブを実行する。see StoreFileRequest
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $targets = [1, 2]; // カテゴリID
        $subs = Submit::subs_accepted_notpublished($targets);
        foreach ($subs as $sub) {
            // まだ設定されていなければ、作成する
            if (strlen($sub->paper->ror) < 1000) {
                $out = $sub->paper->fetchRoR();
                Log::info("RoRJob: paper_id={$sub->paper->id} fetchRoR result: \r\n" . $out);
            }
        }
    }
}
