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
        Schema::create('room_telegram_threads', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->unique();
            $table->bigInteger('telegram_chat_id')->nullable();
            $table->bigInteger('telegram_thread_id')->nullable();

            $table->foreign('room_id')
                ->references('id')
                ->on('rooms')
                ->cascadeOnDelete();

            // channel
            $table->bigInteger('telegram_channel_id')->nullable();
            $table->bigInteger('telegram_channel_message_id')->nullable();

            // discussion group
            $table->bigInteger('telegram_group_id')->nullable();
            $table->bigInteger('telegram_discussion_message_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_telegram_threads');
    }
};
