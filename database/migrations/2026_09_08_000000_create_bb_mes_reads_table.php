<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bb_mes_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bb_mes_id')->constrained('bb_mes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['bb_mes_id', 'user_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('bb_mes_reads');
    }
};