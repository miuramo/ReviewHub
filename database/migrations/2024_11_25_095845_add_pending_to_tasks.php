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
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('started')->default(false)->after('workflow_id');
            $table->boolean('has_trouble')->default(false)->after('started');
            $table->json('issues')->nullable()->after('has_trouble');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('started');
            $table->dropColumn('has_trouble');
            $table->dropColumn('issues');
            //
        });
    }
};
