# RotaRehber Proje Hafızası (project_memory.md)

Bu dosya, Antigravity AI asistanının projeyi, mimariyi ve kullanıcı tercihlerini her zaman "hatırlaması" için oluşturulmuştur. Her yeni oturumda bu dosyayı okumasını isteyebilirsiniz.

## 🏗️ Mimari Yapı (Architecture)
- **Backend (Laravel API)**: `laravel_api` klasörü. Tüm veriler mobilde JSON olarak buradan çekilir.
- **Frontend (Flutter Mobile)**: `FLUTTER/rotarehber_app` klasörü. 
- **Web Platformu**: PHP tabanlı (`cermik`, `cungus` vb. klasörler).
- **Kritik Kural**: Mobil uygulama, web platformuyla **1:1 görsel ve içerik senkronu** (Web Parity) sağlamalıdır.

## ⚙️ Teknik Standartlar ve Kurallar (Rules)
- **Slug Bazlı Eşleşme**: Kategori aramaları her zaman slug üzerinden yapılmalıdır (Örn: `historical-places`, `nature-places`).
- **Veri Temizliği**: "DENEME", "asdasd" gibi placeholder (taslak) veriler API ve Flutter katmanında filtrelenmelidir.
- **Eczane Mantığı**: Eczaneler `api/fetch_pharmacies.php` üzerinden Diyarbakır Eczacı Odası sitesinden (scraper) canlı çekilir. Mobil, API'deki `PharmacyController` üzerinden bu canlı veriyi alır.
- **Kaplıca (HotSpring)**: Çermik sayfasında banner doğrudan gerçek mekan detayına (`PlaceDetailScreen`) yönlenmelidir.

## ✅ Tamamlanan Kritik İşlemler (Çermik Sync)
- [x] **PlaceController & BusinessController**: Kategori eşleşmeleri hibrit (slug+name) hale getirildi.
- [x] **DistrictDetailsScreen**: Menü ikonları (Landmark, Leaf vb.) ve yönlendirme slugları web ile eşitlendi.
- [x] **DistrictProvider**: Yan menüdeki (Drawer) "asdasd" verileri filtrelendi.
- [x] **PharmacyController**: Eczane çekme (Scraper) yolu ve require_once hataları giderildi.

## 🚀 Beni Tanı (Context Command)
Her yeni model değişiminde kullanıcı şu komutu verdiğinde bu dosyayı baştan sona oku:
> "Antigravity, proje kökündeki project_memory.md dosyasını oku ve beni tanı."

---
*Son Güncelleme: 2026-04-07*
