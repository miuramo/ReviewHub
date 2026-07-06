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
        Schema::table('accepts', function (Blueprint $table) {
            $table->integer('status_id')->nullable()->after('judge'); // 判定を返すときに、この値をpaper.status_idにセットする。statuses と対応づける。たとえば、採録なら10
            $table->boolean('do_lock')->default(false)->after('status_id'); // 判定を返したときに、投稿をロックするかを設定。setDecisionでセットする
            // accepts.judgeは以前のままで残す。正なら採録、負なら不採録、0なら未定。
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accepts', function (Blueprint $table) {
            $table->dropColumn('status_id');
            $table->dropColumn('do_lock');
        });
    }
};
