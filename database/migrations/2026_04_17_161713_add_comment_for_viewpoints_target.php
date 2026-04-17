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
        Schema::table('viewpoints', function (Blueprint $table) {
            // targetカラムにコメントを追加
            $table->string('target')->comment('一般査読1、メタ2、幹事4の合計値(ビットマスク)で表示対象を指定する')->default(1)->change();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viewpoints', function (Blueprint $table) {
            // targetカラムのコメントを削除
            $table->string('target')->comment(null)->change();
        });
         //
    }
};
