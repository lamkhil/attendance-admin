<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Attendance;
use App\Models\AttendanceLog;

class FixAttendancePhotoPath extends Command
{
    protected $signature = 'attendance:fix-photo-path';
    protected $description = 'Convert S3 photo URL to path for Filament FileUpload';

    public function handle()
    {
        $baseUrl = Storage::disk('s3')->url('');

        AttendanceLog::query()
            ->whereNotNull('photo_url')
            ->each(function ($row) use ($baseUrl) {

                if (str_starts_with($row->photo_url, 'http')) {
                    $row->photo_url = str_replace(
                        $baseUrl,
                        '',
                        $row->photo_url
                    );
                    $row->save();

                    $this->info("Fixed ID {$row->id}");
                }
            });

        $this->info('DONE');
    }
}
