<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\PresensiKerjaBakti;

class PresensiKerjaBaktiController extends Controller
{
    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'nama' => 'required|string|max:150',
            'nik_nip' => 'required|string|max:30',
            'foto' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'geotag' => 'nullable|string|max:255',
        ]);

        try {

            // =========================
            // Decode Base64 Foto
            // =========================
            $foto = $request->foto;

            if (preg_match('/^data:image\/(\w+);base64,/', $foto, $type)) {

                $foto = substr($foto, strpos($foto, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Format foto tidak valid'
                    ], 422);
                }

                $foto = base64_decode($foto);

                if ($foto === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Decode foto gagal'
                    ], 422);
                }

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Format foto tidak valid'
                ], 422);
            }

            // =========================
            // Simpan Foto
            // =========================
            $filename = 'presensi/' . date('Y/m/') . Str::random(20) . '.' . $type;

            Storage::disk('s3')->put($filename, $foto);

            // =========================
            // Simpan Database
            // =========================
            $presensi = PresensiKerjaBakti::create([
                'nama' => $request->nama,
                'nik_nip' => $request->nik_nip,
                'foto_path' => $filename,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'geotag' => $request->geotag,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'waktu_presensi' => now(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Presensi berhasil',
                'data' => $presensi
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);

        }
    }
}