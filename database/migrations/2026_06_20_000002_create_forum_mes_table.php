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
        Schema::create('forum_mes', function (Blueprint $table) {
            $table->id();
            $table->integer('forum_id');
            $table->integer('user_id')->nullable()->comment('0 はシステムメッセージ');
            $table->integer('post_id')->nullable()->comment('投稿者の役職ID (Post)');
            $table->string('subject')->nullable();
            $table->text('mes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_mes');
    }
};
