---
description: ROTAREHBER Android APK Çıktısı Alma Rehberi
---

Bu rehber, hazırlanan altyapıyı kullanarak projenizi gerçek bir Android uygulamasına (.apk) dönüştürmeniz için gereken adımları içerir.

### Ön Gereksinimler
1. Bilgisayarınızda **Node.js** kurulu olmalıdır (Sistemde npm bulunamadı, lütfen kurunuz).
2. **Android Studio** kurulu ve güncel olmalıdır.

### Adım 1: Bağımlılıkları Kurun
Proje kök dizininde bir terminal açın ve şu komutu çalıştırın:
```bash
npm install
```

### Adım 2: Capacitor Yapılandırmasını Senkronize Edin
Web projeyi native Android projesine dönüştürmek için:
```bash
npx cap add android
npx cap sync android
```

### Adım 3: Android Studio'yu Açın
Android Studio'yu şu komutla veya manuel olarak başlatın:
```bash
npx cap open android
```

### Adım 4: APK Üretimi
1. Android Studio içinde projenin yüklenmesini bekleyin.
2. Üst menüden **Build > Build Bundle(s) / APK(s) > Build APK(s)** seçeneğine tıklayın.
3. İşlem tamamlandığında sağ altta çıkan "locate" linkine tıklayarak APK dosyanıza ulaşabilirsiniz.

> [!NOTE]
> `capacitor.config.json` dosyası projenizin şu anki yerel adresine (`http://localhost/REHBER/`) yönlendirilmiş durumdadır. Yayına alırken bu adresi canlı URL'niz ile değiştirmeyi unutmayın.
