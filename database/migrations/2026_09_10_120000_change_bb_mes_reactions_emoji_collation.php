<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // デフォルトの照合順序では異なる絵文字が同一視されることがあるため、バイト完全一致で比較する
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bb_mes_reactions MODIFY emoji VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bb_mes_reactions MODIFY emoji VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
        }
    }
};
