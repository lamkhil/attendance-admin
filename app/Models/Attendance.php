<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
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
