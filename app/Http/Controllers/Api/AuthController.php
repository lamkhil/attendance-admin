<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use App\Notifications\OtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    /** LOGIN STEP 1 */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $otp = rand(100000, 999999);

        Otp::create([
            'email' => $user->email,
            'otp' => $otp,
            'type' => 'login',
            'expired_at' => now()->addMinutes(5),
        ]);

        $user->notify(new OtpNotification($otp, 'login'));

        return response()->json([
            'message' => 'OTP dikirim ke email'
        ]);
    }

    /** LOGIN STEP 2 */
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required',
        ]);

        $otp = Otp::where([
            'email' => $request->email,
            'otp' => $request->otp,
            'type' => 'login',
        ])
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'message' => 'OTP tidak valid atau kadaluarsa'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('api-token')->plainTextToken;

        $otp->delete();

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /** FORGOT PASSWORD */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email tidak terdaftar'
            ], 404);
        }

        $otp = rand(100000, 999999);

        Otp::create([
            'email' => $user->email,
            'otp' => $otp,
            'type' => 'reset',
            'expired_at' => now()->addMinutes(5),
        ]);

        $user->notify(new OtpNotification($otp, 'reset'));

        return response()->json([
            'message' => 'OTP reset password dikirim'
        ]);
    }

    /** RESET PASSWORD */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $otp = Otp::where([
            'email' => $request->email,
            'otp' => $request->otp,
            'type' => 'reset',
        ])
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'message' => 'OTP tidak valid'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        $otp->delete();

        return response()->json([
            'message' => 'Password berhasil direset'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
