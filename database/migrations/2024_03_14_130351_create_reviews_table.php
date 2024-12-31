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
        Schema::create('reviews', function (Blueprint $table) {
            $table->comment('査読割り当て');
            $table->id();
            $table->integer('submit_id')->nullable();
            $table->integer('paper_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('user_id')->nullable()->comment("ReviewerID");
            $table->integer('target')->default(0)->comment("0は通常査読、1はメタ査読、2は最終判定");
            $table->integer('status')->nullable()->comment("0は未回答、1は回答中、2は完了");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
