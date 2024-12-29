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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('submit_id')->nullable(); // SubmitID
            $table->integer('workflow_id')->default(1); // ワークフローID
            $table->date('due_date')->nullable(); // 期限日
            $table->json('log')->nullable(); // 経過ログ
            $table->boolean('completed')->default(false); // 完了フラグ
            $table->timestamp('completed_at')->nullable(); // 完了日時
            $table->boolean('approved')->default(false); // 承認フラグ
            $table->timestamp('approved_at')->nullable(); // 承認日時
            $table->integer('subject_id')->nullable(); // 作業担当者
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
