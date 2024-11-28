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
        Schema::table('workflows', function (Blueprint $table) {
            // $table->integer('status_id_at_started')->nullable()->after('join')->comment("開始時に論文に設定されるステータス");
            $table->integer('status_id_at_ended')->nullable()->after('join')->comment("終了時に論文に設定されるステータス");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            // $table->dropColumn('status_id_at_started');
            $table->dropColumn('status_id_at_ended');
            //
        });
    }
};
