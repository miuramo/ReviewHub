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
            $table->json('members')->nullable()->comment('メンバーUIDs')->after('type');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bbs', function (Blueprint $table) {
            $table->dropColumn('members');
            //
        });
    }
};
