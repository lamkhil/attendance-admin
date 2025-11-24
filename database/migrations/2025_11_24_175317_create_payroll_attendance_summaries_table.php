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
        Schema::create('payroll_attendance_summaries', function (Blueprint $table) {
            $table->id();
            
            // FK ke user
            $table->unsignedBigInteger('user_id');

            // rekap periode
            $table->integer('month'); // 1 - 12
            $table->integer('year');  // 2025, 2026,...

            // rekap hari
            $table->integer('total_workdays')->default(0); // hari kerja efektif
            $table->integer('total_present')->default(0);
            $table->integer('total_late')->default(0);
            $table->integer('total_absent')->default(0); // alpha/bolos
            $table->integer('total_leave')->default(0); // cuti
            $table->integer('total_permission')->default(0); // izin
            $table->integer('total_sick')->default(0); // sakit
            $table->integer('total_official_duty')->default(0); // tugas luar

            // rekap jam
            $table->integer('total_work_hours')->default(0); // dalam jam
            $table->integer('total_overtime_hours')->default(0);

            // optional: nominal payroll (bonus/potongan)
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('late_penalty', 12, 2)->default(0);
            $table->decimal('absence_penalty', 12, 2)->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // 1 pegawai hanya punya 1 rekap per bulan
            $table->unique(['user_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_attendance_summaries');
    }
};
