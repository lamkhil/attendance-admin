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
        Schema::create('rooms', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();

            $table->string('channel')->nullable();
            $table->string('channel_account')->nullable();
            $table->string('organization_id')->nullable();

            $table->string('account_uniq_id')->nullable();
            $table->string('channel_integration_id')->nullable();

            $table->string('session')->nullable();
            $table->timestamp('session_at')->nullable();

            $table->integer('unread_count')->default(0);

            $table->string('avatar')->nullable();

            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by_id')->nullable();
            $table->string('resolved_by_type')->nullable();

            $table->string('external_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
