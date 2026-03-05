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
        Schema::create('presensi_kerja_baktis', function (Blueprint $table) {
            $table->id();

            // identitas
            $table->string('nama');
            $table->string('nik_nip', 30)->index();

            // foto presensi
            $table->string('foto_path');

            // lokasi GPS
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('geotag')->nullable(); // alamat hasil reverse geocode

            // info perangkat
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // waktu presensi
            $table->timestamp('waktu_presensi')->useCurrent();

            $table->timestamps();

            // index untuk query cepat
            $table->index(['latitude','longitude']);
            $table->index('waktu_presensi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_kerja_baktis');
    }
};