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
            $table->timestamp('receiptsent_at')->nullable()->after('submitted_at')->comment('受領通知送信日時'); // 受領通知メールを送信した日時
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submits', function (Blueprint $table) {
            $table->dropColumn('receiptsent_at');
            //
        });
    }
};
