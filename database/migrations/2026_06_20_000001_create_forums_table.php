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
        Schema::create('forums', function (Blueprint $table) {
            $table->comment('委員会共有掲示板。Forum.created_at の年度内に担当任期を持つユーザのみ閲覧・書き込み可。');
            $table->id();
            $table->integer('post_id')->comment('役職ID (Post)');
            $table->integer('user_id')->comment('作成者 (User)');
            $table->string('title');
            $table->boolean('isclose')->default(false)->comment('締め切り済みかどうか');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forums');
    }
};
