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
        Schema::table('bbs', function (Blueprint $table) {
            // 例: Bb::where('paper_id', ...)->where('type', ...)->where('rev_id', ...)->first()
            // 例: Bb::where('paper_id', ...)->where('type', 2)->whereIn('rev_id', ...) などの検索を高速化
            $table->index(['paper_id', 'type', 'rev_id'], 'bbs_paper_type_rev_index');
        });

        Schema::table('bb_mes', function (Blueprint $table) {
            // 例: $bb->messages() / where('bb_id', ...) / created_at 順の取得
            $table->index(['bb_id', 'created_at'], 'bb_mes_bb_created_index');
        });

        Schema::table('bb_mes_reads', function (Blueprint $table) {
            // 既読レコードは主に bb_mes_id と user_id でアクセスされるため、個別の検索を高速化
            $table->index('bb_mes_id', 'bb_mes_reads_bb_mes_id_index');
            $table->index('user_id', 'bb_mes_reads_user_id_index');
            $table->index(['user_id', 'read_at'], 'bb_mes_reads_user_read_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bb_mes_reads', function (Blueprint $table) {
            $table->dropIndex('bb_mes_reads_user_read_index');
            $table->dropIndex('bb_mes_reads_user_id_index');
            $table->dropIndex('bb_mes_reads_bb_mes_id_index');
        });

        Schema::table('bb_mes', function (Blueprint $table) {
            $table->dropIndex('bb_mes_bb_created_index');
        });

        Schema::table('bbs', function (Blueprint $table) {
            $table->dropIndex('bbs_paper_type_rev_index');
        });
    }
};
