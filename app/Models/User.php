<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'current_shift',
        'today'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Relasi attendance hari ini
     */
    public function todayAttendance(): HasOne
    {
        return $this->hasOne(Attendance::class)
            ->whereDate('date', now());
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Attribute: today
     */
    public function getTodayAttribute()
    {
        $attendance = $this->todayAttendance;

        if (! $attendance) {
            return null;
        }

        return [
            'date' => $attendance->date,
            'day' => Carbon::parse($attendance->date)->translatedFormat('l'),

            'check_in' => isset($attendance->check_in)? Carbon::parse($attendance->check_in)->format('H:i') : null,
            'check_out' => isset($attendance->check_out) ? Carbon::parse($attendance->check_out)->format('H:i') : null,

            'status' => $attendance->status,
            'work_minutes' => $attendance->work_minutes,
        ];
    }

    public function getCurrentShiftAttribute(): ?array
    {
        $now = Carbon::now();
        $day = $now->dayOfWeek; 
        // 1 = Monday ... 6 = Saturday, 0 = Sunday

        // Sunday = libur
        if ($day === Carbon::SUNDAY) {
            return null;
        }

        // Default
        $start = '07:30';
        $end   = '16:00';
        $type  = 'normal';

        // Friday
        if ($day === Carbon::FRIDAY) {
            $end = '16:30';
            $type = 'jumat';
        }

        // Saturday
        if ($day === Carbon::SATURDAY) {
            $start = '09:00';
            $end   = '14:00';
            $type  = 'sabtu';
        }

        return [
            'start' => $start,
            'end'   => $end,
            'type'  => $type,
        ];
    }
}
