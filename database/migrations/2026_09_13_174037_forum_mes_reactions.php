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
        Schema::create('forum_mes_reactions', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('forum_mes_id')->constrained('forum_mes')->cascadeOnDelete();
            $table->string('emoji', 64);
            $table->timestamps();
            $table->unique(['forum_mes_id', 'user_id', 'emoji']);
            $table->index('user_id');
            $table->index(['forum_mes_id', 'emoji'], 'forum_mes_id_emoji_index');
        });
        // デフォルトの照合順序では異なる絵文字が同一視されることがあるため、バイト完全一致で比較する
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE forum_mes_reactions MODIFY emoji VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_mes_reactions');

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE forum_mes_reactions MODIFY emoji VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
        }
    }
};
