<?php
// fix_permissions.php
// Bu script uploads klasöründeki izin sorunlarını (ERİŞİM ENGELLENDİ) çözmek için tasarlanmıştır.
// Tarayıcıdan çalıştırıldığında SYSTEM kullanıcısı yetkisiyle (AppServ varsayılan) çalışır.

header('Content-Type: text/plain; charset=utf-8');

$uploadsDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

if (!is_dir($uploadsDir)) {
    die("Uploads klasörü bulunamadı: $uploadsDir\n");
}

echo "İzinler sıfırlanıyor: $uploadsDir\n";

// 1. İzinleri miras alacak şekilde sıfırla
$cmd1 = "icacls \"$uploadsDir\" /reset /t /c /q 2>&1";
echo "Komut 1: $cmd1\n";
$output1 = shell_exec($cmd1);
echo $output1 . "\n";

// 2. 'Everyone' grubuna Tam Denetim ver (okuma/ziplerken sorun çıkmaması için)
$cmd2 = "icacls \"$uploadsDir\" /grant Everyone:(OI)(CI)F /t /c /q 2>&1";
echo "Komut 2: $cmd2\n";
$output2 = shell_exec($cmd2);
echo $output2 . "\n";

echo "\nİşlem tamamlandı. Şimdi 'make_zip.php' dosyasını tekrar çalıştırmayı deneyin.\n";
?>
