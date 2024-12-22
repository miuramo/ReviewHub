<?php

namespace Database\Seeders;

use App\Http\Controllers\PaperController;
use App\Mail\Submitted;
use App\Models\File;
use App\Models\Paper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Tests\TestCase;
use Tests\Feature\PaperTest;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!App::environment('testing')) {
            // File::factory()->count(1)->create();
            $file = new UploadedFile('./tests/Feature/_int.pdf', '_int.pdf', 'application/pdf', null, true);
            $f = File::createnew($file, 1, 1);
            $f->filetype_id = 1;
            $f->save();

            // メール送信
            $paper = Paper::find(1);
            $paper->pdf_file_id = $f->id;
            $paper->save();
            if ($paper->status_id <= 2) {
                $paper->status_id = 2;
                $paper->currentsubmit->submitted_at = now();
                $paper->currentsubmit->save();
                $paper->save();

                //newSubmit_newTasks from workflow (すでに、Submitは作成済み)
                // $paper->currentsubmit->newTasks();
            }
            (new Submitted($paper))->process_send();
        }
    }
}
