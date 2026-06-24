<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\District;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Standard Login (Email/Phone + Password)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $identity = $request->email; // email veya telefon
        $phones = $this->phoneVariants($identity); // tüm olası formatlar

        $user = User::where('email', $identity)
                    ->orWhereIn('phone', $phones)
                    ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Check if user is active (OTP verified)
            if ($user->is_active == 0) {
                $otp = $this->generateOtpForPhone($user->phone);
                $user->update(['otp_code' => $otp]);
                $this->sendSms($user->phone, "RotaRehber Giriş Kodunuz: " . $otp);
                
                return response()->json([
                    'status' => 'needs_otp',
                    'message' => 'Lütfen telefonunuza gelen doğrulama kodunu girin.',
                    'temp_user_id' => $user->id
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->update(['last_login_at' => now()->setTimezone('Europe/Istanbul')->format('Y-m-d H:i:s')]);

            return response()->json([
                'status' => 'success',
                'message' => 'Giriş başarılı!',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Geçersiz bilgiler.'
        ], 401);
    }

    /**
     * User Registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required|min:6',
            'district_id' => 'required'
        ]);

        // Check if exists
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Bu e-posta adresi zaten kayıtlı.'], 400);
        }
        if (User::where('phone', $request->phone)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Bu telefon numarası zaten kayıtlı.'], 400);
        }

        // Get district name
        $district = District::find($request->district_id);
        $districtName = $district ? $district->name : '';

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'city' => $request->city ?? 'Diyarbakır',
            'district' => $districtName,
            'is_active' => 0,
            'created_at' => now()->setTimezone('Europe/Istanbul')->format('Y-m-d H:i:s')
        ]);

        $otp = $this->generateOtpForPhone($user->phone);
        $user->update(['otp_code' => $otp]);

        $this->sendSms($user->phone, "RotaRehber Kayıt Doğrulama Kodunuz: " . $otp);

        return response()->json([
            'status' => 'needs_otp',
            'message' => 'Lütfen telefonunuza gelen doğrulama kodunu girin.',
            'temp_user_id' => $user->id,
            'debug_otp' => $otp // Parity for testing
        ]);
    }

    /**
     * Quick Login Request (Tetikleme) - Phone based
     */
    public function quickLogin(Request $request)
    {
        $request->validate(['phone' => 'required']);

        $phones = $this->phoneVariants($request->phone);
        $user = User::whereIn('phone', $phones)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Bu telefon numarasıyla kayıtlı bir hesap bulunamadı.'], 404);
        }

        $otp = $this->generateOtpForPhone($user->phone);
        $user->update(['otp_code' => $otp]);

        $this->sendSms($user->phone, "RotaRehber Giriş Kodunuz: " . $otp);
        
        return response()->json([
            'status' => 'needs_otp',
            'message' => 'Doğrulama kodu gönderildi.',
            'temp_user_id' => $user->id,
            'debug_otp' => $otp
        ]);
    }

    /**
     * Forgot Password — OTP gönder
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['phone' => 'required']);

        $phones = $this->phoneVariants($request->phone);
        $user = User::whereIn('phone', $phones)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Bu telefon numarasıyla kayıtlı bir hesap bulunamadı.'], 404);
        }

        $otp = $this->generateOtpForPhone($user->phone);
        $user->update(['otp_code' => $otp]);
        $this->sendSms($user->phone, "RotaRehber Şifre Sıfırlama Kodunuz: " . $otp);

        return response()->json([
            'status' => 'needs_otp',
            'message' => 'Doğrulama kodu telefonunuza gönderildi.',
            'temp_user_id' => $user->id,
            'debug_otp' => $otp
        ]);
    }

    /**
     * Reset Password — OTP doğrula + yeni şifreyi kaydet
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone'    => 'required',
            'otp'      => 'required',
            'password' => 'required|min:6',
        ]);

        $phones = $this->phoneVariants($request->phone);
        $user = User::whereIn('phone', $phones)
                    ->where('otp_code', $request->otp)
                    ->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Geçersiz veya süresi dolmuş doğrulama kodu.'], 400);
        }

        $user->update([
            'password'  => Hash::make($request->password),
            'otp_code'  => null,
            'is_active' => 1,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Şifreniz başarıyla güncellendi.']);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required',
            'temp_user_id' => 'required'
        ]);

        $user = User::where('id', $request->temp_user_id)
                    ->where('otp_code', $request->otp_code)
                    ->first();

        if ($user) {
            $user->update(['is_active' => 1, 'otp_code' => null, 'last_login_at' => now()->setTimezone('Europe/Istanbul')->format('Y-m-d H:i:s')]);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'access_token' => $token,
                'user' => $user
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Hatalı kod.'], 401);
    }

    /**
     * Get user profile data (Stats + List)
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            // Stats
            $checkinsCount = DB::table('check_ins')->where('user_id', $user->id)->where('status', 'APPROVED')->count();
            $submissionsCount = DB::table('cek_gonder_forms')
                ->where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->orWhere('tel_no', $user->phone)
                ->count();

            // Detailed Lists (Web Parity)
            $checkins = DB::table('check_ins as c')
                ->select('c.id', 'c.status', 'c.created_at', 'c.target_type', 'd.name as district_name',
                         DB::raw('COALESCE(p.name, b.business_name) as target_name'))
                ->leftJoin('places as p', function($join) {
                    $join->on('c.target_id', '=', 'p.id')->where('c.target_type', '=', 'place');
                })
                ->leftJoin('businesses as b', function($join) {
                    $join->on('c.target_id', '=', 'b.id')->where('c.target_type', '=', 'business');
                })
                ->leftJoin('districts as d', 'c.district_id', '=', 'd.id')
                ->where('c.user_id', $user->id)
                ->orderBy('c.created_at', 'desc')
                ->limit(20)
                ->get();

            $submissions = DB::table('cek_gonder_forms as f')
                ->select('f.*', 'd.name as district_name')
                ->leftJoin('districts as d', 'f.district_id', '=', 'd.id')
                ->where('f.user_id', $user->id)
                ->orWhere('f.email', $user->email)
                ->orWhere('f.tel_no', $user->phone)
                ->orderBy('f.created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user,
                    'stats' => [
                        'checkins' => $checkinsCount,
                        'submissions' => $submissionsCount
                    ],
                    'checkins' => $checkins,
                    'submissions' => $submissions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Profile Photo Upload
     */
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'user_id'       => 'required',
            'profile_image' => 'required|image|max:5120',
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.'], 404);
        }

        try {
            // Sunucu open_basedir kısıtlamalarına takılmamak için public_path kullanıyoruz.
            $uploadPath = public_path('uploads/avatars');
            if (!is_dir($uploadPath)) {
                @mkdir($uploadPath, 0755, true);
            }

            $file     = $request->file('profile_image');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);

            // Veritabanına rotarehber.com bazlı çalışacak şekilde ekliyoruz
            $relPath = 'laravel_api/public/uploads/avatars/' . $filename;
            $user->update(['profile_image' => $relPath]);

            return response()->json([
                'status' => 'success',
                'url'    => $relPath,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Telefon numarasının olası tüm formatlarını döndür
     * 5327104206 → ['5327104206', '05327104206', '905327104206', '+905327104206']
     */
    private function phoneVariants(string $phone): array
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        // Başındaki 90 veya 0'ı kaldır, saf 10 haneli numara al
        if (strlen($digits) == 12 && substr($digits, 0, 2) == '90') {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) == 11 && $digits[0] == '0') {
            $digits = substr($digits, 1);
        }
        // 10 haneli kök numara (5xxxxxxxxx)
        return [
            $digits,              // 5327104206
            '0' . $digits,        // 05327104206
            '90' . $digits,       // 905327104206
            '+90' . $digits,      // +905327104206
        ];
    }

    /**
     * Apple Reviewers & Demo Account Bypass
     */
    private function generateOtpForPhone($phone)
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strpos($digits, '5555555555') !== false) {
            return 123456;
        }
        return rand(100000, 999999);
    }

    /**
     * Private SMS Helper
     */
    private function sendSms($phone, $message)
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strpos($digits, '5555555555') !== false) {
            return true; // Bypass SMS delivery for Apple Reviewers
        }

        try {
            // Fetch config from 'settings' table - match legacy keys
            $apiId = DB::table('settings')->where('name', 'sms_api_id')->value('value') ?: '7073c30918869aee144ddca9';
            $apiKey = DB::table('settings')->where('name', 'sms_api_key')->value('value') ?: 'bb37df2be980e603326bce12';
            $sender = DB::table('settings')->where('name', 'sms_title')->value('value') ?: 'FREEMIND';

            // Standard format: 05xx... -> 905xx...
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($phone) == 10 && $phone[0] == '5') $phone = '90' . $phone;
            elseif (strlen($phone) == 11 && $phone[0] == '0') $phone = '9' . $phone;

            $postData = [
                "api_id" => $apiId,
                "api_key" => $apiKey,
                "sender" => $sender,
                "message_type" => "normal",
                "message" => $message,
                "phones" => [$phone]
            ];

            $ch = curl_init("https://api.vatansms.net/api/v1/otp");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            return $response;
        } catch (\Exception $e) {
            \Log::error("SMS Error: " . $e->getMessage());
            return false;
        }
    }
}
