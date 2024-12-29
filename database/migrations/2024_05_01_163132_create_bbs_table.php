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
        Schema::create('bbs', function (Blueprint $table) {
            $table->comment('掲示板の種類とアクセス制限');
            $table->id();
            $table->string('name')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('paper_id')->nullable();
            $table->integer('rev_id')->nullable(); // type=2のみ有効
            $table->integer('type')->default(4)->comment('1なら投管と著者 2なら投管と単一査読者 3なら投管と全査読者 4なら投管のみ');
            $table->string('key')->nullable();
            $table->boolean('needreply')->default(false);
            $table->boolean('isopen')->default(true);
            $table->boolean('isclose')->default(false);
            // $table->string('subscribers')->nullable()->comment('author|ec|aec|meta|rev|pub|admin');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbs');
    }
};
