<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
