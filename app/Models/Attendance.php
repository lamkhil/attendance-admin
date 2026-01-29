<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{

    protected $casts = [
        'check_out_lat' => 'float',
        'check_out_lng' => 'float',
        'check_in_lat' => 'float',
        'check_in_lng' => 'float'
    ];

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    // checkIn and checkOut can be derived from logs if needed
    public function checkInLog()
    {
        return $this->logs()->orderBy('timestamp')->first();
    }

    public function checkOutLog()
    {
        return $this->logs()->orderByDesc('timestamp')->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function imageToBase64(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (! Storage::disk('s3')->exists($path)) {
            return null;
        }

        // ambil binary dari S3
        $binary = Storage::disk('s3')->get($path);

        // detect mime type
        $mime = Storage::disk('s3')->mimeType($path) ?? 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    }
}
