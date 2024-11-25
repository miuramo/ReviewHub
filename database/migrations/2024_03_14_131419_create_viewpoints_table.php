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
        Schema::create('viewpoints', function (Blueprint $table) {
            $table->comment('査読観点・質問項目');
            $table->id();
            $table->integer('category_id')->default(0);
            $table->integer('orderint')->default(0);
            $table->string('name')->nullable()->comment("key");
            $table->string('desc')->nullable()->comment("keyの説明");
            $table->text('content')->nullable()->comment("表示する内容(HTML)");
            $table->text('contentafter')->nullable()->comment("フォーム要素の下に表示する内容(HTML)");
            $table->boolean('target')->default(0)->comment("一般査読者向け=0、メタ査読者向け=1、担当幹事向け=2");
            $table->integer('weight')->default(0)->comment('スコア計算時の重み');
            $table->boolean('doReturn')->default(false)->comment('著者に見せるなら1');
            $table->boolean('doReturnAcceptOnly')->default(false)->comment('採択時のみ返す');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viewpoints');
    }
};
