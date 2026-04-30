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
        Schema::table('log_accesses', function (Blueprint $table) {
            $table->integer('paper_id')->nullable()->after('url')->comment('推測される論文ID')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_accesses', function (Blueprint $table) {
            $table->dropIndex(['paper_id']);
            $table->dropColumn('paper_id');
        });
    }
};
