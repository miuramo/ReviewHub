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
        Schema::table('reviews', function (Blueprint $table) {
            $table->timestamp('downloaded_at')->nullable()->after('request_at'); // ダウンロードしたかどうかのチェックはTaskからの依頼をうけて、Reviewが行う
            $table->timestamp('review_edit_started_at')->nullable()->after('downloaded_at'); // 編集開始した日時
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('downloaded_at');
            $table->dropColumn('review_edit_started_at');
        });
    }
};
