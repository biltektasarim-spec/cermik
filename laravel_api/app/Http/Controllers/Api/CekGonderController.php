<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CekGonder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CekGonderController extends Controller
{
    /**
     * Store a new Snap & Send (Çek Gönder) submission.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ad_soyad' => 'required|string|max:100',
            'tc_no' => 'required|string|digits:11',
            'basvuru_turu' => 'required|string',
            'aciklama' => 'required|string',
            'district_id' => 'required',
            'foto1' => 'nullable|image|max:10240',
            'foto2' => 'nullable|image|max:10240',
            'foto3' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only(['district_id', 'user_id', 'fcm_token', 'basvuru_turu', 'ad_soyad', 'tc_no', 'email', 'tel_no', 'aciklama']);
            
            // SUNUCU SAATİ HATASI WORKAROUND:
            // Sunucu sistem saati ~9.5 saat ileri gidiyor (hosting NTP sorunu).
            // Gerçek saati Google sunucusundan HTTP Date header'ı ile alıyoruz.
            $data['created_at'] = $this->getRealTime();

            // Eğer veritabanında process_status kolonu varsa ekle (migration çalışmamışsa patlamaması için)
            if (\Illuminate\Support\Facades\Schema::hasColumn('cek_gonder_forms', 'process_status')) {
                $data['process_status'] = 'Beklemede';
            }
            
            // Sunucu open_basedir kısıtlamalarına takılmamak için public_path kullanıyoruz.
            // Admin paneli de güncellendi, her iki klasörü (kök ve laravel_api/public) kontrol edecek.
            $uploadPath = public_path('uploads/cek_gonder');
            if (!is_dir($uploadPath)) {
                @mkdir($uploadPath, 0755, true);
            }

            for ($i = 1; $i <= 3; $i++) {
                $fileKey = 'foto' . $i;
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    $filename = 'cg_' . time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    
                    // DB'ye laravel_api'nin dışındaki relative yolu eklersek admin paneli ve flutter okuyabilir
                    // rotarehber.com/laravel_api/public/uploads/cek_gonder/... 
                    // Ancak daha temiz olması için sadece uploads/cek_gonder/... olarak kaydediyoruz
                    $data[$fileKey] = 'laravel_api/public/uploads/cek_gonder/' . $filename;
                }
            }

            $submission = CekGonder::create($data);

            // Optional: Replicate the legacy email notification if needed
            // $this->sendNotificationEmail($submission);

            return response()->json([
                'status' => 'success',
                'message' => 'Başvurunuz başarıyla belediyemize iletilmiştir.',
                'id' => $submission->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Beklenmedik bir hata oluştu.',
                'debug' => $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Sunucu saati hatalı olduğu için gerçek zamanı Google'dan alır.
     * Google'ın HTTP yanıtındaki "Date" başlığını okur ve
     * Europe/Istanbul timezone'una çevirir.
     * cURL başarısız olursa fallback olarak Carbon kullanır.
     */
    private function getRealTime(): string
    {
        try {
            $ch = curl_init('https://www.google.com');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response && preg_match('/Date:\s*(.+)\r?\n/i', $response, $m)) {
                $googleTime = strtotime(trim($m[1])); // UTC timestamp
                if ($googleTime > 0) {
                    return \Carbon\Carbon::createFromTimestampUTC($googleTime)
                        ->setTimezone('Europe/Istanbul')
                        ->format('Y-m-d H:i:s');
                }
            }
        } catch (\Throwable $e) {
            // cURL başarısız — fallback
        }

        // Fallback: sunucu saati (hosting düzelirse bu zaten doğru çalışır)
        return \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d H:i:s');
    }
}
