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
        Schema::create('messages', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('type');
            $table->boolean('is_campaign')->default(false);

            $table->string('room_id');
            $table->string('sender_id')->nullable();

            $table->string('participant_id')->nullable();
            $table->string('participant_type')->nullable();

            $table->string('organization_id')->nullable();

            $table->text('text')->nullable();
            $table->string('status')->nullable();

            $table->string('external_id')->nullable();
            $table->string('local_id')->nullable();
            $table->string('reply')->nullable()->index();

            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreign('sender_id')->references('id')->on('senders');
            $table->string('file_uniq_id')->nullable();
            $table->string('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('file_large_url')->nullable();
            $table->string('file_medium_url')->nullable();
            $table->string('file_small_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
