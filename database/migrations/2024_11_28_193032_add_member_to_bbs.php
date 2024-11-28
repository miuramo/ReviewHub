<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bbs', function (Blueprint $table) {
            $table->integer('submit_id')->nullable()->comment('SubmitID')->after('paper_id');
            $table->unsignedBigInteger(('task_id'))->nullable()->comment('TaskID')->after('submit_id');
            $table->json('members')->nullable()->comment('メンバーUIDs')->after('type');
            $table->dropColumn('category_id');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bbs', function (Blueprint $table) {
            $table->dropColumn('submit_id');
            $table->dropColumn('task_id');
            $table->dropColumn('members');
            $table->integer('category_id')->nullable()->comment('カテゴリID')->after('paper_id');
            //
        });
    }
};
