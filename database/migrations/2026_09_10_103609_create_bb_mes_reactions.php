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
        Schema::create('bb_mes_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bb_mes_id')->constrained('bb_mes')->cascadeOnDelete();
            $table->string('emoji', 64);
            $table->timestamps();
            $table->unique(['bb_mes_id', 'user_id', 'emoji']);
            $table->index('user_id');
            $table->index(['bb_mes_id', 'emoji'], 'bb_mes_id_emoji_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bb_mes_reactions');
    }
};
