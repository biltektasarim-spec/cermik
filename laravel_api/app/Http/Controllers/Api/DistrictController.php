<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistrictController extends Controller
{
    /**
     * Get all districts for the home screen.
     */
    public function index()
    {
        try {
            $districts = District::where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get();

            // İlçe kart resmi için öncelik sırası (Web parity):
            // 1. settings.hero_image varsa DAİMA kullan (admin'in yüklediği güncel resim)
            // 2. places.HotSpring.image_main
            // 3. districts.image (eski statik dosya - son çare)
            $districts = $districts->map(function($district) {
                // Her zaman settings.hero_image'ı kontrol et - varsa override et
                $heroImg = DB::table('settings')
                    ->where('district_id', $district->id)
                    ->where('name', 'hero_image')
                    ->value('value');
                
                if (!empty($heroImg)) {
                    // settings.hero_image en yüksek öncelik
                    $district->image = $heroImg;
                } else {
                    // settings'de hero_image yoksa HotSpring places'tan al
                    // district.image mevcut olsa bile (eski statik yol) override et
                    $hotspring = DB::table('places')
                        ->where('district_id', $district->id)
                        ->where('category', 'HotSpring')
                        ->whereNotNull('image_main')
                        ->first();
                    
                    if ($hotspring && !empty($hotspring->image_main)) {
                        $district->image = $hotspring->image_main;
                    }
                    // HotSpring da yoksa district.image olduğu gibi kalır
                }
                return $district;
            });

            return response()->json([
                'status' => 'success',
                'data' => $districts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific district details including mayor, settings and 8 standard categories.
     */
    public function show($id)
    {
        try {
            // Support both integer ID and slug parameters
            if (is_numeric($id)) {
                $district = District::find($id);
            } else {
                $district = District::where('slug', $id)->first();
            }

            if (!$district) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'İlçe bulunamadı.'
                ], 404);
            }
            
            // From this point onward, we only use the primary integer ID
            $id = $district->id;

            // Fetch settings for this district
            $global_settings = DB::table('settings')->where('district_id', 0)->pluck('value', 'name')->toArray();
            $district_settings = DB::table('settings')->where('district_id', $id)->pluck('value', 'name')->toArray();
            
            // Merge global and district settings carefully
            $settings_data = $global_settings;
            foreach ($district_settings as $name => $val) {
                $settings_data[$name] = $val;
            }

            // Web Parity: Dynamic Categories based on settings (Synchronized with cermik/index.php)
            $categories = [];
            $slug = $district->slug;
            
            // WEB İLE 1:1 KATEGORİ SIRALAMASI
            // cermik/index.php ve cungus/index.php dosyalarından birebir alınmıştır.
            // ORTAK kategoriler (her iki ilçede de var):
            $cat_map = [
                ['id' => 'Historical', 'slug' => 'historical-places', 'icon' => 'landmark', 'setting' => 'menu_historical', 'default_name' => 'Tarihi Yerler', 'img' => 'assets/img/categories/historical.jpg'],
                ['id' => 'Nature', 'slug' => 'nature-places', 'icon' => 'leaf', 'setting' => 'menu_nature', 'default_name' => 'Doğa ve Parklar', 'img' => 'assets/img/categories/nature.jpg'],
                ['id' => 'HotSpring', 'slug' => 'hotspring', 'icon' => 'hot-tub-person', 'setting' => 'menu_hotspring', 'default_name' => 'Kaplıcalar', 'img' => 'assets/img/categories/thermal.jpg'],
                ['id' => 'ParkAndGarden', 'slug' => 'parks-gardens', 'icon' => 'tree', 'setting' => 'menu_parks', 'default_name' => 'Park ve Bahçeler', 'img' => 'assets/img/categories/parks.jpg'],
                ['id' => 'cek-gonder', 'slug' => 'cek-gonder', 'icon' => 'camera', 'setting' => 'menu_cek_gonder', 'default_name' => 'Çek Gönder', 'img' => 'assets/img/categories/cek_gonder.jpg'],
                ['id' => 'pharmacy', 'slug' => 'pharmacy', 'icon' => 'prescription-bottle-medical', 'setting' => 'menu_pharmacy', 'default_name' => 'Nöbetçi Eczaneler', 'img' => 'assets/img/categories/medical.jpg'],
                ['id' => 'hotels', 'slug' => 'hotel', 'icon' => 'hotel', 'setting' => 'menu_hotels', 'default_name' => 'Otel ve Pansiyon', 'img' => 'assets/img/categories/hotels.jpg'],
                ['id' => 'restaurants', 'slug' => 'restaurant', 'icon' => 'utensils', 'setting' => 'menu_restaurants', 'default_name' => 'Lokantalar', 'img' => 'assets/img/categories/restaurants.jpg'],
            ];

            // ÇERMİK'E ÖZEL: Kuruyemiş Pazarı (cermik/index.php'de var, cungus/index.php'de YOK)
            if ($slug === 'cermik') {
                // Çek Gönder'den önce ekle (web sıralamsına göre)
                array_splice($cat_map, 4, 0, [[
                    'id' => 'Kuruyemis', 'slug' => 'kuruyemis', 'icon' => 'store',
                    'setting' => 'menu_kuruyemis', 'default_name' => 'Kuruyemiş Pazarı',
                    'img' => 'assets/img/categories/kuruyemis.jpg'
                ]]);
            }

            foreach ($cat_map as $cm) {
                // AYARLARDA KAPALI DEĞİLSE GÖSTER (VARSAYILAN AÇIK)
                $show = ($settings_data[$cm['setting']] ?? '1') == '1';
                
                // --- WEB İLE AYNI FİLTRELEME MANTIĞI ---
                // Web versiyonu (cermik/index.php) sadece menü ayarına bakıyor.
                // Sadece places bazlı menüler için veri kontrolü yapıyoruz (Historical, Nature, ParkAndGarden, HotSpring).
                // Kuruyemis, pharmacy, cek-gonder, hotels, restaurants: setting=1 ise göster, veri kontrolü yapma.
                if ($show) {
                    if (in_array($cm['id'], ['Historical', 'Nature', 'ParkAndGarden', 'HotSpring'])) {
                        // Mekan bazlı kategoriler - bu ilçede hiç veri yoksa menüyü gizle
                        $exists = DB::table('places')->where('district_id', $id)->where('category', $cm['id'])->where('is_approved', 1)->exists();
                        if (!$exists) $show = false;
                    } else if (in_array($cm['id'], ['hotels', 'restaurants'])) {
                        // Otel ve Restoran - işletme verisi yoksa gizle
                        $biz_cat = ($cm['id'] === 'hotels') ? 'Hotel' : 'Restaurant';
                        $exists = DB::table('businesses')->where('district_id', $id)->where('category', $biz_cat)->where('is_approved', 1)->exists();
                        if (!$exists) $show = false;
                    }
                    // Kuruyemis: places tablosunda kayıtlı, setting=1 ise her zaman göster (web gibi)
                    // pharmacy, cek-gonder: setting=1 ise her zaman göster
                }

                // Tüm ilçeler için HotSpring menüsünü grid'den gizle - hero banner olarak gösterilecek
                // Bu sayede yeni eklenen ilçelerde de HotSpring otomatik yatay banner olur
                if ($cm['id'] == 'HotSpring') $show = false;
                if ($show) {
                    $tr_name = $settings_data[$cm['setting'] . '_tr'] ?? $cm['default_name'];
                    
                    // FALLBACK: Pharmacy category might use 'menu_hospital' keys from the admin panel
                    if ($cm['id'] === 'pharmacy' && empty($settings_data['menu_pharmacy_tr'])) {
                        $tr_name = $settings_data['menu_hospital_tr'] ?? $tr_name;
                    }

                    $raw_img = $settings_data[$cm['setting'] . '_img'] ?? $cm['img'];
                    
                    // Sadece Çüngüş için statik assets/ klasör resimleri cungus/ prefix alır.
                    // DIKKAT: uploads/ yolları ortak kök dizinde olduğu için prefix EKLENMEZ.
                    if ($slug === 'cungus') {
                        if (strpos($raw_img, 'assets/') === 0) {
                            // Sadece assets/ statik dosyaları cungus/ altında
                            $raw_img = $slug . '/' . $raw_img;
                        }
                        // uploads/ ile başlıyorsa dokunma - admin'den yüklenen resimler ortak klasörde
                    }

                    $en_name = $settings_data[$cm['setting'] . '_en'] ?? $tr_name;
                    if ($cm['id'] === 'pharmacy' && empty($settings_data['menu_pharmacy_en'])) {
                        $en_name = $settings_data['menu_hospital_en'] ?? $en_name;
                    }

                    $cat_entry = [
                        'id' => $cm['id'],
                        'slug' => $cm['slug'],
                        'name' => null, // Flutter tarafında isEn kontrolü ile ?? fallback sağlaması için bilerek null bırakıldı
                        'name_tr' => $tr_name,
                        'name_en' => $en_name,
                        'icon' => $cm['icon'],
                        'image' => $raw_img,
                        'lat' => null,
                        'lng' => null,
                    ];

                    // Kuruyemis: lat/lng'yi places tablosundan çek (web ile aynı mantık)
                    if ($cm['id'] === 'Kuruyemis') {
                        $ky = DB::table('places')->where('category', 'Kuruyemis')->where('district_id', $id)->first();
                        if ($ky) {
                            $cat_entry['lat'] = $ky->lat;
                            $cat_entry['lng'] = $ky->lng;
                        }
                    }

                    $categories[] = $cat_entry;
                }
            }

            // Dinamik Hero Banner Mantığı: Tüm ilçeler için HotSpring places kaydı varsa otomatik hero banner
            // Bu sayede yeni eklenen ilçelerde de hardcoded değişikliğe gerek kalmaz
            $settings_data['hero_target'] = 'none'; // Default
            $settings_data['hero_target_id'] = null;

            $hotspring_place = DB::table('places')
                ->where('category', 'HotSpring')
                ->where('district_id', $id)
                ->first();

            if ($hotspring_place) {
                $settings_data['hero_title_tr'] = $hotspring_place->name ?? ($settings_data['menu_thermal_tr'] ?? 'Kaplıcalar');
                $settings_data['hero_image'] = $hotspring_place->image_main ?? ($settings_data['menu_thermal_img'] ?? 'assets/img/categories/kaplica.jpg');
                $settings_data['hero_title_en'] = $hotspring_place->name_en ?? ($settings_data['menu_thermal_en'] ?? 'Thermal Springs');
                $settings_data['hero_desc_tr'] = $hotspring_place->slogan ?? '';
                $settings_data['hero_target'] = 'place';
                $settings_data['hero_target_id'] = $hotspring_place->id;
                $settings_data['hero_lat'] = $hotspring_place->lat ?? null;
                $settings_data['hero_lng'] = $hotspring_place->lng ?? null;
            }

            // Custom banners (Live broadcasts or district hero)
            $live_broadcasts = DB::table('live_broadcasts')
                ->where(function($q) use ($id) {
                    $q->where('district_id', $id)->orWhereNull('district_id')->orWhere('district_id', 0);
                })
                ->where('is_active', 1)
                ->get();

            // custom_menus: DB'den çek, ardından HotSpring place varsa başa dinamik olarak ekle
            // get_district_details.php ile aynı mantık - tüm ilçeler için tutarlı hero banner
            $db_custom_menus = DB::table('custom_menus')
                ->where('district_id', $id)
                ->where('is_active', 1)
                ->orderBy('sort_order', 'asc')
                ->get()
                ->toArray();

            // HotSpring places kaydı varsa ve thermal menüsü aktifse, başa ekle (yatay hero banner)
            $thermal_active = ($settings_data['menu_thermal_status'] ?? '1') == '1';
            if ($hotspring_place && $thermal_active) {
                $hs_menu = (object)[
                    'id' => 99999,
                    'district_id' => $id,
                    'name_tr' => $hotspring_place->name ?? ($settings_data['menu_thermal_tr'] ?? 'Kaplıcalar'),
                    'name_en' => $hotspring_place->name_en ?? ($settings_data['menu_thermal_en'] ?? 'Thermal Springs'),
                    'slug' => 'hotspring',
                    'image' => $hotspring_place->image_main ?? ($settings_data['menu_thermal_img'] ?? 'assets/img/categories/kaplica.jpg'),
                    'place_id' => $hotspring_place->id,
                    'lat' => $hotspring_place->lat ?? null,
                    'lng' => $hotspring_place->lng ?? null,
                    'sort_order' => -1,
                    'is_active' => 1,
                    'icon' => 'fa-hot-tub-person',
                    'target_url' => null,
                ];
                array_unshift($db_custom_menus, $hs_menu);
            }

            $custom_menus = $db_custom_menus;

            // Municipal Guide (Side Menu) - Fix table name and remove missing is_active column
            $municipal_guide = DB::table('municipal_guide')
                ->where(function($q) use ($id) {
                    $q->where('district_id', $id)->orWhereNull('district_id')->orWhere('district_id', 0);
                })
                ->whereNull('parent_id')
                ->get();



            return response()->json([
                'status' => 'success',
                'data' => [
                    'district' => $district,
                    'settings' => $settings_data,
                    'categories' => $categories,
                    'live_broadcasts' => $live_broadcasts,
                    'custom_menus' => $custom_menus,
                    'municipal_guide' => $municipal_guide
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
