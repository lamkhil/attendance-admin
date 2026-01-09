<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double'
    ];
}
