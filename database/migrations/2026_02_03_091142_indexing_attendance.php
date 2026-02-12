<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Index untuk filter / group / sort
            $table->index('date');
            $table->index('created_at');

            // Index untuk join dengan user
            $table->index('user_id');

            // Optional: index compound jika sering query gabungan user + tanggal
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'date']);
        });
    }
};
