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
        Schema::create('extra_links', function (Blueprint $table) {
            $table->id();
            $table->string('label')->comment('リンクの表示名')->nullable();
            $table->string('url')->comment('リンクのURL')->nullable();
            $table->string('color')->comment('リンクボタンの色')->default('cyan');
            $table->string('role_name')->comment('リンクが関連する役割の名前')->default('ec');
            $table->boolean('is_header')->comment('ヘッダ部に表示するかどうか')->default(true);
            $table->boolean('is_footer')->comment('フッタ部に表示するかどうか')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_links');
    }
};
