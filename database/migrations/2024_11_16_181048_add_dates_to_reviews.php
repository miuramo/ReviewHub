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
        Schema::table('reviews', function (Blueprint $table) {
            $table->datetime('request_at')->nullable()->comment("査読打診日");
            $table->datetime('start_at')->nullable()->comment("査読開始日");
            $table->datetime('end_at')->nullable()->comment("査読終了日");
            $table->softDeletes();
            $table->boolean('locked')->default(false)->comment("査読ロック")->after('status');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('request_at');
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');
            $table->dropColumn('locked');
            $table->dropSoftDeletes();
            //
        });
    }
};
