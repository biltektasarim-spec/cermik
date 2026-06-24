<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FormSubmitController extends Controller
{
    /**
     * Save 'Çek Gönder' form data and images.
     */
    public function saveCekGonder(Request $request)
    {
        try {
            $data = $request->all();
            
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('cek_gonder', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Save to DB
            DB::table('cek_gonder_forms')->insert([
                'user_id'     => $request->input('user_id'),
                'district_id' => $request->input('district_id'),
                'content'     => $request->input('content'),
                'image_paths' => json_encode($imagePaths),
                'created_at'  => \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d H:i:s'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Bildiriminiz başarıyla iletildi.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkIn(Request $request)
    {
        try {
            $user_id = $request->user()->id ?? $request->user_id;
            $target_id = $request->target_id;
            $target_type = $request->target_type ?? 'place';
            $district_id = $request->district_id;
            $user_lat = $request->lat ? (float)$request->lat : null;
            $user_lng = $request->lng ? (float)$request->lng : null;

            if (!$target_id || !$district_id) {
                return response()->json(['status' => 'error', 'message' => 'Geçersiz parametreler.'], 400);
            }

            if ($user_lat === null || $user_lng === null) {
                return response()->json(['status' => 'error', 'message' => 'Konum bilgileriniz alınamadı. Lütfen konum izni verin.'], 400);
            }

            // Get target location
            $table = ($target_type === 'place') ? 'places' : 'businesses';
            $target = DB::table($table)->where('id', $target_id)->select('lat', 'lng')->first();

            if (!$target || empty($target->lat) || empty($target->lng)) {
                return response()->json(['status' => 'error', 'message' => 'Mekan konum bilgisi bulunamadı.']);
            }

            // Haversine distance
            $earthRadius = 6371000;
            $latDiff = deg2rad($target->lat - $user_lat);
            $lonDiff = deg2rad($target->lng - $user_lng);
            $a = sin($latDiff / 2) * sin($latDiff / 2) +
                 cos(deg2rad($user_lat)) * cos(deg2rad($target->lat)) *
                 sin($lonDiff / 2) * sin($lonDiff / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            if ($distance > 100) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Check-in yapabilmek için mekana en az 100 metre yakınında olmalısınız.',
                    'distance' => round($distance) . 'm'
                ]);
            }

            // Check if user already has a check-in here recently (24 hours)
            $recent = DB::table('check_ins')
                ->where('user_id', $user_id)
                ->where('target_id', $target_id)
                ->where('target_type', $target_type)
                ->where('created_at', '>', now()->subHours(24))
                ->count();

            if ($recent > 0) {
                return response()->json(['status' => 'error', 'message' => 'Bu mekanda 24 saat aralıklar ile check-in yapabilirsiniz.']);
            }

            DB::table('check_ins')->insert([
                'user_id'     => $user_id,
                'target_id'   => $target_id,
                'target_type' => $target_type,
                'district_id' => $district_id,
                'status'      => 'APPROVED',
                'created_at'  => \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d H:i:s'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Tebrikler! Check-in işleminiz onaylandı.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record a general visit.
     */
    public function recordVisit(Request $request)
    {
        try {
            DB::table('user_visits')->insert([
                'user_id'     => $request->user_id,
                'page'        => $request->page,
                'district_id' => $request->district_id,
                'created_at'  => \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d H:i:s'),
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error'], 500);
        }
    }

    public function trackBusiness(Request $request)
    {
        $business_id = (int)$request->input('business_id', 0);
        $action = $request->input('action', '');

        if ($business_id > 0 && in_array($action, ['view', 'direction'])) {
            try {
                DB::table('business_stats')->insert([
                    'business_id' => $business_id,
                    'event_type'  => $action,
                    'created_at'  => \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d H:i:s')
                ]);
                return response()->json(['status' => 'success']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Database error'], 500);
            }
        }
        return response()->json(['status' => 'error', 'message' => 'Invalid parameters'], 400);
    }

    public function trackProximity(Request $request)
    {
        try {
            $user_id = $request->user()->id ?? null; // Null if anonymous or API uses session
            $target_id = (int)$request->input('target_id');
            $target_type = $request->input('target_type', 'place');
            $district_id = (int)$request->input('district_id');

            $user_lat = $request->lat ? (float)$request->lat : null;
            $user_lng = $request->lng ? (float)$request->lng : null;

            if (!$target_id || !$district_id || $user_lat === null || $user_lng === null) {
                return response()->json(['status' => 'error', 'message' => 'Eksik parametre.'], 400);
            }

            // Get target location
            $table = ($target_type === 'place') ? 'places' : 'businesses';
            $target = DB::table($table)->where('id', $target_id)->select('lat', 'lng')->first();

            if (!$target || empty($target->lat) || empty($target->lng)) {
                return response()->json(['status' => 'error', 'message' => 'Hedef koordinatları bulunamadı.']);
            }

            // Haversine
            $earthRadius = 6371000;
            $latDiff = deg2rad($target->lat - $user_lat);
            $lonDiff = deg2rad($target->lng - $user_lng);
            $a = sin($latDiff / 2) * sin($latDiff / 2) +
                 cos(deg2rad($user_lat)) * cos(deg2rad($target->lat)) *
                 sin($lonDiff / 2) * sin($lonDiff / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            if ($distance > 100) {
                return response()->json(['status' => 'error', 'message' => 'Mesafe çok uzak.', 'distance' => round($distance)]);
            }

            if ($user_id) {
                // Member Check
                $recent = DB::table('check_ins')
                    ->where('user_id', $user_id)
                    ->where('target_id', $target_id)
                    ->where('target_type', $target_type)
                    ->where('created_at', '>', now()->subHours(24))
                    ->count();

                if ($recent == 0) {
                    DB::table('check_ins')->insert([
                        'user_id'     => $user_id,
                        'target_id'   => $target_id,
                        'target_type' => $target_type,
                        'district_id' => $district_id,
                        'status'      => 'APPROVED',
                        'visit_type'  => 'AUTO',
                        'created_at'  => \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d H:i:s')
                    ]);
                    // Gamification can be added here if there's a backend system
                }
                return response()->json(['status' => 'success', 'message' => 'Üye ziyareti kaydedildi.']);
            } else {
                // Anonymous Proximity
                DB::table('passive_stats')->insert([
                    'target_id'   => $target_id,
                    'target_type' => $target_type,
                    'district_id' => $district_id,
                    'created_at'  => \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d H:i:s')
                ]);
                return response()->json(['status' => 'success', 'message' => 'Anonim yakınlık kaydı yapıldı.']);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
