<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedTinyInteger('rank')->default(0)->after('name')
                  ->comment('権限階層。大きいほど上位。高位ユーザは自rank以下のフォーラムを作成・閲覧・書き込み可。');
        });

        // 既存レコードのランクを名前で設定
        DB::table('posts')->where('name', '編集委員')->update(['rank' => 1]);
        DB::table('posts')->where('name', '幹事')->update(['rank' => 2]);
        DB::table('posts')->where('name', '編集長')->update(['rank' => 3]);
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('rank');
        });
    }
};
