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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ワークフロー名
            $table->string('description')->nullable(); // ワークフローの説明
            $table->enum('subject', ['ec', 'aec', 'meta', 'rev1', 'rev2', 'rev3'])->default('ec'); // ワークフローの対象
            $table->enum('task', ['assign', 'approve', 'submit', 'confirm'])->default('assign'); // ワークフローの種類
            $table->enum('object', ['ec', 'aec', 'meta', 'rev1', 'rev2', 'rev3'])->default('meta'); // ワークフローの対象
            $table->integer('num_of_days')->default(7); // 期限日数
            $table->integer('next_workflow_id')->nullable(); // 次のワークフロー
            $table->integer('next_workflow_id2')->nullable(); // 次のワークフロー

            $table->timestamps();
            $table->comment("Submitが参照するワークフロー");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
