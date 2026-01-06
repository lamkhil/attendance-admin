<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $ext;

        $path = $file->storeAs('uploads', $filename, 's3');

        return response()->json([
            'success' => true,
            'path' => $path,
            'url'  => Storage::disk('s3')->url($path),
        ]);
    }
}
