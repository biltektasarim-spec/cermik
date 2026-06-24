<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Super-Safe BusinessController for Mobile App Compatibility
 * Optimized for local XAMPP environments to prevent HTML-error-related crashes on the phone.
 * Handled with PHP 7.4+ compatibility (no str_starts_with).
 */
class BusinessController extends Controller
{
    public function index(Request $request)
    {
        try {
            $districtId = $request->query('district_id');
            $category = $request->query('category');

            if (!$districtId) {
                return response()->json(['status' => 'error', 'message' => 'district_id required'], 400);
            }

            $query = DB::table('businesses')
                ->where('district_id', $districtId)
                ->where('is_approved', 1);

            if ($category) {
                $category = strtolower($category);
                $query->where(function($q) use ($category) {
                    $q->where('category', $category)
                      ->orWhere('category', 'LIKE', '%' . $category . '%');
                });
            }

            $businesses = $query->get();

            $transformed = $businesses->map(function($biz) {
                return $this->transform($biz);
            })->values()->all();

            return response()->json([
                'status' => 'success',
                'count' => count($transformed),
                'data' => $transformed
            ]);
        } catch (\Throwable $e) {
            error_log("API Error (Business Index): " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    public function show($id)
    {
        try {
            $biz = DB::table('businesses')->where('id', $id)->first();

            if (!$biz) {
                return response()->json(['status' => 'error', 'message' => 'Business not found'], 404);
            }

            $products = DB::table('products')->where('business_id', $id)->get();
            $biz->products = $products->map(function($p) {
                $np = new \stdClass();
                $np->id = (int)$p->id;
                $np->product_name = $p->name ?? ($p->product_name ?? 'Ürün');
                $np->product_name_en = $p->name_en ?? ($p->product_name_en ?? '');
                
                $img = $p->image_path ?? ($p->image ?? '');
                if ($img && mb_strpos($img, 'http') !== 0 && mb_strpos($img, 'uploads/') !== 0) {
                    if (mb_strpos($img, 'products/') === 0) $img = 'uploads/' . $img;
                    else $img = 'uploads/products/' . $img;
                }
                $np->image = $img;
                $np->price = (string)($p->price ?? '0');
                $np->description = $p->description ?? '';
                $np->description_en = $p->description_en ?? '';
                return $np;
            })->values()->all();

            $transformed = $this->transform($biz);

            // Statistics (1:1 with Web)
            $monthStart = date('Y-m-01 00:00:00');
            $yearStart  = date('Y-01-01 00:00:00');

            $resMonthly = DB::table('business_stats')
                ->where('business_id', $id)
                ->where('event_type', 'view')
                ->where('created_at', '>=', $monthStart)
                ->count();

            $resYearly = DB::table('business_stats')
                ->where('business_id', $id)
                ->where('event_type', 'view')
                ->where('created_at', '>=', $yearStart)
                ->count();

            $transformed->stats = (object)[
                'monthly_views' => (int)$resMonthly,
                'yearly_views' => (int)$resYearly
            ];

            return response()->json([
                'status' => 'success',
                'data' => $transformed
            ]);
        } catch (\Throwable $e) {
            error_log("API Error (Business Show): " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function transform($biz)
    {
        $res = new \stdClass();

        $res->id = (int)($biz->id ?? 0);
        $res->district_id = (int)($biz->district_id ?? 0);
        $res->name = $biz->business_name ?? ($biz->name ?? '');
        $res->name_en = $biz->business_name_en ?? ($biz->name_en ?? '');
        $res->category = $biz->category ?? '';
        $res->phone = $biz->phone ?? '';
        $res->email = $biz->email ?? '';
        $res->website = $biz->website ?? '';

        $res->is_active = (isset($biz->is_approved) && $biz->is_approved == 1);
        $res->isActive = $res->is_active;
        $res->has_order = (isset($biz->order_enabled) && $biz->order_enabled == 1);
        $res->order_link = $biz->order_link ?? null;

        $res->lat = ($biz->lat && is_numeric($biz->lat)) ? (double)$biz->lat : null;
        $res->lng = ($biz->lng && is_numeric($biz->lng)) ? (double)$biz->lng : null;

        // 4. Image Handling (Bulletproof Prefixing)
        $img = $biz->image_main ?? ($biz->image ?? '');
        $img = ltrim((string)$img, '/'); // Remove leading slash
        
        if ($img && mb_strpos($img, 'http') !== 0 && mb_strpos($img, 'uploads/') === false) {
            if (mb_strpos($img, 'businesses/') !== false) {
                $img = 'uploads/' . ltrim($img, 'uploads/');
            } else {
                $img = 'uploads/businesses/' . $img;
            }
        }
        // Normalize double slashes
        $img = str_replace('//', '/', $img);
        
        $res->image_main = $img;
        $res->image = $img;

        $pano = $biz->panorama_360 ?? null;
        if ($pano) {
            $pano = ltrim((string)$pano, '/');
            if (mb_strpos($pano, 'http') !== 0 && mb_strpos($pano, 'uploads/') === false) {
                if (mb_strpos($pano, 'businesses/') !== false) $pano = 'uploads/' . $pano;
                else $pano = 'uploads/businesses/' . $pano;
            }
            $pano = str_replace('//', '/', $pano);
        }
        $res->panorama_360 = $pano;
        
        $qr = $biz->qr_code_path ?? null;
        if ($qr && mb_strpos($qr, 'http') !== 0 && mb_strpos($qr, 'uploads/') !== 0) {
            if (mb_strpos($qr, 'businesses/') === 0) $qr = 'uploads/' . $qr;
            else $qr = 'uploads/businesses/' . $qr;
        }
        $res->qr_code_path = $qr;

        $gallery = [];
        if (isset($biz->image_gallery) && is_string($biz->image_gallery)) {
            $decoded = json_decode($biz->image_gallery, true);
            if (is_array($decoded)) $gallery = array_values($decoded);
        }
        $res->image_gallery = $gallery;

        $res->description = trim($biz->description ?? '');
        if ($res->description === '...' || $res->description === '..') $res->description = null;
        
        $res->description_en = trim($biz->description_en ?? '');
        if ($res->description_en === '...' || $res->description_en === '..') $res->description_en = null;

        // CRITICAL FOR FLUTTER: hotel_info — orijinal key-value çiftleri korunuyor
        // Web ile birebir uyum: {"Wi-Fi": "Var", "Kahvaltı": "Açık Büfe", "Oda Sayısı": "25"}
        $hRaw = new \stdClass();
        if (isset($biz->hotel_info)) {
            $raw = [];
            if (is_string($biz->hotel_info) && $biz->hotel_info !== '') {
                $raw = json_decode($biz->hotel_info, true) ?: [];
            } elseif (is_array($biz->hotel_info)) {
                $raw = $biz->hotel_info;
            }
            foreach ($raw as $k => $v) {
                if ($v === null || $v === false || $v === '' || $v === '0' || $v === 0) continue;
                $hRaw->$k = (string)$v;
            }
        }
        $res->hotel_info = (object)$hRaw;

        // CRITICAL FOR FLUTTER: working_hours → Flutter {monday:{open,close,is_closed}} formatına çevir
        // Web DB formatı: {"days":[1,2,3,4,5], "open":"09:00", "close":"22:00"}
        // Per-day formatı: {"pazartesi":{"open":"09:00","close":"18:00"}} veya {"monday":{...}}
        $wHours = new \stdClass();
        if (isset($biz->working_hours) && $biz->working_hours !== '' && $biz->working_hours !== null) {
            $rawH = [];
            if (is_string($biz->working_hours)) $rawH = json_decode($biz->working_hours, true) ?: [];
            elseif (is_array($biz->working_hours)) $rawH = $biz->working_hours;

            // Format A: {days:[0,1,2,...], open:"HH:MM", close:"HH:MM"} — Web admin formatı
            if (isset($rawH['days']) && isset($rawH['open']) && isset($rawH['close'])) {
                $dayNumbers  = array_map('intval', (array)$rawH['days']);
                $dayNamesMap = [0=>'sunday',1=>'monday',2=>'tuesday',3=>'wednesday',4=>'thursday',5=>'friday',6=>'saturday'];
                foreach ($dayNamesMap as $num => $engName) {
                    $entry = new \stdClass();
                    if (in_array($num, $dayNumbers)) {
                        $entry->open      = (string)$rawH['open'];
                        $entry->close     = (string)$rawH['close'];
                        $entry->is_closed = false;
                    } else {
                        $entry->open      = '';
                        $entry->close     = '';
                        $entry->is_closed = true;
                    }
                    $wHours->$engName = $entry;
                }
            } else {
                // Format B: {"Pazartesi": "09:00-18:00"} veya {"monday": {open,close}}
                $dayMap = [
                    'pazartesi' => 'monday', 'pzt' => 'monday',
                    'salı'      => 'tuesday', 'sali' => 'tuesday', 'sal' => 'tuesday',
                    'çarşamba'  => 'wednesday', 'carsamba' => 'wednesday', 'çar' => 'wednesday',
                    'perşembe'  => 'thursday', 'persembe' => 'thursday', 'per' => 'thursday',
                    'cuma'      => 'friday', 'cum' => 'friday',
                    'cumartesi' => 'saturday', 'cmt' => 'saturday',
                    'pazar'     => 'sunday', 'paz' => 'sunday',
                ];
                foreach ($rawH as $day => $data) {
                    $dK = mb_strtolower(trim((string)$day), 'UTF-8');
                    $targetKey = $dayMap[$dK] ?? $dK;
                    $entry = new \stdClass();
                    $entry->is_closed = false;
                    if (is_array($data)) {
                        if (isset($data['open'])) {
                            $entry->open  = (string)$data['open'];
                            $entry->close = (string)($data['close'] ?? '');
                        } elseif (isset($data[0]) && isset($data[1])) {
                            $entry->open  = (string)$data[0];
                            $entry->close = (string)$data[1];
                        } else { $entry->open = ''; $entry->close = ''; }
                        if (!empty($data['is_closed'])) $entry->is_closed = true;
                    } elseif (is_string($data)) {
                        $str = trim($data);
                        if (mb_strtolower($str) === 'kapalı' || mb_strtolower($str) === 'kapali' || $str === '') {
                            $entry->is_closed = true; $entry->open = ''; $entry->close = '';
                        } elseif (mb_strpos($str, '-') !== false) {
                            $parts = explode('-', $str, 2);
                            $entry->open  = trim($parts[0]);
                            $entry->close = trim($parts[1] ?? '');
                        } else { $entry->open = $str; $entry->close = ''; }
                    } else { $entry->open = ''; $entry->close = ''; }
                    $wHours->$targetKey = $entry;
                }
            }
        }
        $res->working_hours = (object)$wHours;

        $res->products = $biz->products ?? [];

        return $res;
    }
}
