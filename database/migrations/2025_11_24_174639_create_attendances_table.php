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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedBigInteger('shift_id')->nullable();

            $table->dateTime('check_in')->nullable();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->string('check_in_photo')->nullable();

            $table->dateTime('check_out')->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 10, 7)->nullable();
            $table->string('check_out_photo')->nullable();

            $table->enum('status', ['hadir','telat','izin','cuti'])->default('hadir');
            $table->integer('work_hours')->default(0);
            $table->integer('overtime_hours')->default(0);

            $table->timestamps();

            $table->unique(['user_id','date']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('shift_id')->references('id')->on('shifts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
