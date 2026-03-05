<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PresensiKerjaBakti extends Model
{
    protected $fillable = [
        'nama',
        'nik_nip',
        'foto_path',
        'latitude',
        'longitude',
        'geotag',
        'ip_address',
        'user_agent',
        'waktu_presensi',
    ];

    public function getFotoUrlAttribute()
    {
        return $this->foto_path ? Storage::disk('s3')->url($this->foto_path) : null;
    }
}