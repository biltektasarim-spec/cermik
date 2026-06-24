<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    /**
     * Get pharmacies and hospitals for a district.
     *
     * Eczane verisi güncellemesi: config.php'yi doğrudan include etmek
     * Laravel içinde session_start() tetiklediğinden JSON response bozuluyordu.
     * Çözüm: sync_duty_pharmacy.php'ye arka planda HTTP isteği gönder
     * (fire & forget), ardından DB'yi Eloquent ile oku.
     */
    public function index(Request $request)
    {
        try {
            $dId = $request->district_id;

            // District Slug Resolution
            if ($dId && !is_numeric($dId)) {
                $dist = DB::table('districts')->where('slug', $dId)->orderBy('id', 'asc')->first();
                if ($dist) $dId = $dist->id;
            }

            if (!$dId) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Geçerli bir ilçe ID veya slug gerekli.'
                ], 400);
            }

            // İlçe slug'ını bul (sync script için gerekli)
            $districtSlug = DB::table('districts')->where('id', $dId)->value('slug') ?? 'cermik';

            // Eczane verisini arka planda güncelle (fire & forget).
            // sync_duty_pharmacy.php kendi PDO bağlantısını kurar, session/header sorunu yok.
            $this->triggerPharmacySync($districtSlug);

            // DB'den oku (sync script hali hazırda güncelledi ya da cache geçerliyse zaten güncel)
            $pharmacies = DB::table('pharmacies')
                ->where('district_id', $dId)
                ->orderBy('is_on_duty', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            $hospitals = DB::table('hospitals')
                ->where('district_id', $dId)
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'pharmacies' => $pharmacies,
                    'hospitals'  => $hospitals
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get duty pharmacies only.
     */
    public function getDuty(Request $request)
    {
        try {
            $dId = $request->district_id;

            if ($dId) {
                $districtSlug = DB::table('districts')->where('id', $dId)->value('slug') ?? 'cermik';
                $this->triggerPharmacySync($districtSlug);
            }

            $query = DB::table('pharmacies')->where('is_on_duty', 1);

            if ($dId) {
                $query->where('district_id', $dId);
            }

            $pharmacies = $query->orderBy('name', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data'   => $pharmacies
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * sync_duty_pharmacy.php'ye arka planda HTTP isteği gönder (fire & forget).
     * 1 saniyelik timeout: cevap beklenmez, script kendi başına çalışır.
     * Bu sayede session/header çakışması olmadan eczane verisi güncellenir.
     */
    private function triggerPharmacySync(string $districtSlug): void
    {
        try {
            $host     = request()->getSchemeAndHttpHost(); // örn: https://rotarehber.com
            $syncUrl  = $host . '/api/sync_duty_pharmacy.php?district=' . urlencode($districtSlug);

            $ch = curl_init($syncUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);       // 1 sn sonra devam et
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);      // Sinyal bekleme
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // Sync başarısız olsa bile API çalışmaya devam eder (DB'deki mevcut veriyi döner)
            \Illuminate\Support\Facades\Log::warning('PharmacySync failed: ' . $e->getMessage());
        }
    }
}
