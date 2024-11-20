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
        Schema::table('submits', function (Blueprint $table) {
            $table->dropColumn('award');
            $table->dropColumn('psession_id');
            $table->dropColumn('booth');
            $table->integer('aec_id')->nullable()->after('round'); 
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submits', function (Blueprint $table) {
            $table->integer('award')->nullable();
            $table->integer('psession_id')->nullable();
            $table->string('booth')->nullable();
            $table->dropColumn('aec_id');
            //
        });
    }
};
