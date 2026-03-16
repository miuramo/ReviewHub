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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('役職の名前');
            $table->string('menutext')->nullable()->comment('メニューに表示するテキスト');
            $table->string('menulink')->nullable()->comment('メニューのリンク');
            $table->string('rolenames')->nullable()->comment('この役職に関連するロール（権限）の名前をカンマ区切りで保存');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
