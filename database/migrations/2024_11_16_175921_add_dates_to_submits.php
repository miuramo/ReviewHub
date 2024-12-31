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
        Schema::table('submits', function (Blueprint $table) {
            $table->integer('round')->nullable()->comment("査読ラウンド 1,2,3...")->after('category_id');
            $table->date('resubmit_until')->nullable()->comment("再投稿期限");
            $table->date('submitted_at')->nullable()->comment("投稿日");
            $table->date('review_until')->nullable()->comment("査読期限");
            $table->date('ec_decision_at')->nullable()->comment("判定通知日");
            $table->date('notify_at')->nullable()->comment("(著者)判定確認日");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submits', function (Blueprint $table) {
            $table->dropColumn('round');
            $table->dropColumn('resubmit_until');
            $table->dropColumn('submitted_at');
            $table->dropColumn('review_until');
            $table->dropColumn('ec_decision_at');
            $table->dropColumn('notify_at');
            
            //
        });
    }
};
