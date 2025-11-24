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
            if (Schema::hasColumn('submits', 'award')) {
                $table->dropColumn('award');
            }
            if (Schema::hasColumn('submits', 'psession_id')) {
                $table->dropColumn('psession_id');
            }
            if (Schema::hasColumn('submits', 'booth')) {
                $table->dropColumn('booth');
            }
            if (!Schema::hasColumn('submits', 'aec_id')) {
                $table->integer('aec_id')->nullable()->after('round');
            }
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submits', function (Blueprint $table) {
            if (!Schema::hasColumn('submits', 'award')) {
                $table->integer('award')->nullable();
            }
            if (!Schema::hasColumn('submits', 'psession_id')) {
                $table->integer('psession_id')->nullable();
            }
            if (!Schema::hasColumn('submits', 'booth')) {
                $table->string('booth')->nullable();
            }
            if (Schema::hasColumn('submits', 'aec_id')) {
                $table->dropColumn('aec_id');
            }
            //
        });
    }
};
