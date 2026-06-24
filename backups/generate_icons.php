<?php
/**
 * ROTAREHBER - Native App Mipmap Icon Generator
 * "rotarehber (640 x 640 piksel).png" -> CermikRehberiApp mipmap dizinlerine ölçekler
 */

$src = __DIR__ . '/rotarehber (640 x 640 piksel).png';

if (!file_exists($src)) {
    die("HATA: rotarehber (640 x 640 piksel).png bulunamadı: $src");
}

if (!extension_loaded('gd')) {
    die("HATA: PHP GD kütüphanesi yüklü değil.");
}

$sizes = [
    'mipmap-mdpi'    => 48,
    'mipmap-hdpi'    => 72,
    'mipmap-xhdpi'   => 96,
    'mipmap-xxhdpi'  => 144,
    'mipmap-xxxhdpi' => 192,
];

$resDir = __DIR__ . '/CermikRehberiApp/app/src/main/res/';

$srcImg = imagecreatefrompng($src);
if (!$srcImg) {
    die("HATA: rotarehber.png PNG olarak okunamadı.");
}

$srcW = imagesx($srcImg);
$srcH = imagesy($srcImg);

echo "<pre>\n";
echo "Kaynak: $src ({$srcW}x{$srcH})\n\n";

$ok = 0;
$errors = 0;

foreach ($sizes as $folder => $size) {
    $dir = $resDir . $folder . '/';
    
    if (!is_dir($dir)) {
        echo "UYARI: Klasör bulunamadı: $dir\n";
        $errors++;
        continue;
    }

    // Normal kare ikon oluştur (ic_launcher.png)
    $dst = imagecreatetruecolor($size, $size);
    // Saydam arka plan
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefill($dst, 0, 0, $transparent);
    imagealphablending($dst, true);

    imagecopyresampled($dst, $srcImg, 0, 0, 0, 0, $size, $size, $srcW, $srcH);

    // ic_launcher.png
    $outFile = $dir . 'ic_launcher.png';
    if (imagepng($dst, $outFile)) {
        echo "OK: ic_launcher.png -> $folder ({$size}x{$size})\n";
        $ok++;
    } else {
        echo "HATA: Yazılamadı: $outFile\n";
        $errors++;
    }

    // ic_launcher_round.png (aynı boyut, dairesel maske)
    $round = imagecreatetruecolor($size, $size);
    imagealphablending($round, false);
    imagesavealpha($round, true);
    $trans2 = imagecolorallocatealpha($round, 0, 0, 0, 127);
    imagefill($round, 0, 0, $trans2);
    imagealphablending($round, true);

    // Önce kaynağı kopyala
    imagecopyresampled($round, $srcImg, 0, 0, 0, 0, $size, $size, $srcW, $srcH);

    // Dairesel maske uygula
    $masked = imagecreatetruecolor($size, $size);
    imagealphablending($masked, false);
    imagesavealpha($masked, true);
    $trans3 = imagecolorallocatealpha($masked, 0, 0, 0, 127);
    imagefill($masked, 0, 0, $trans3);

    $cx = $cy = $size / 2;
    $r  = $size / 2;
    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            if (($x - $cx) * ($x - $cx) + ($y - $cy) * ($y - $cy) <= $r * $r) {
                $c = imagecolorat($round, $x, $y);
                imagesetpixel($masked, $x, $y, $c);
            }
        }
    }

    $roundFile = $dir . 'ic_launcher_round.png';
    if (imagepng($masked, $roundFile)) {
        echo "OK: ic_launcher_round.png -> $folder ({$size}x{$size})\n";
        $ok++;
    } else {
        echo "HATA: Yazılamadı: $roundFile\n";
        $errors++;
    }

    imagedestroy($dst);
    imagedestroy($round);
    imagedestroy($masked);
}

imagedestroy($srcImg);

// ic_launcher_foreground.png (xxhdpi için 108x108 - adaptif ikon iç kısım)
$fgSrcImg = imagecreatefrompng($src);
$fgSize = 108;
$fgDir = $resDir . 'mipmap-xxhdpi/';
if (is_dir($fgDir) && $fgSrcImg) {
    $fg = imagecreatetruecolor($fgSize, $fgSize);
    imagealphablending($fg, false);
    imagesavealpha($fg, true);
    $fgTrans = imagecolorallocatealpha($fg, 0, 0, 0, 127);
    imagefill($fg, 0, 0, $fgTrans);
    imagealphablending($fg, true);
    imagecopyresampled($fg, $fgSrcImg, 0, 0, 0, 0, $fgSize, $fgSize, imagesx($fgSrcImg), imagesy($fgSrcImg));
    $fgFile = $fgDir . 'ic_launcher_foreground.png';
    if (imagepng($fg, $fgFile)) {
        echo "OK: ic_launcher_foreground.png -> mipmap-xxhdpi ({$fgSize}x{$fgSize})\n";
        $ok++;
    }
    imagedestroy($fg);
    imagedestroy($fgSrcImg);
}

echo "\n✅ Tamamlandı: $ok dosya oluşturuldu, $errors hata.\n";
echo "</pre>";
?>
