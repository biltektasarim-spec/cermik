<?php
$sizes = [
    'mipmap-mdpi' => 48,
    'mipmap-hdpi' => 72,
    'mipmap-xhdpi' => 96,
    'mipmap-xxhdpi' => 144,
    'mipmap-xxxhdpi' => 192
];

$baseDir = 'C:/AppServ/www/REHBER/CermikRehberiApp/app/src/main/res/';

foreach ($sizes as $folder => $size) {
    if (!is_dir($baseDir . $folder)) mkdir($baseDir . $folder, 0777, true);
    
    // ic_launcher.png
    $im = imagecreatetruecolor($size, $size);
    $bg = imagecolorallocate($im, 0, 136, 204); // #0088cc - Tasarımınıza uygun Mavi
    imagefill($im, 0, 0, $bg);
    imagepng($im, $baseDir . $folder . '/ic_launcher.png');
    
    // ic_launcher_round.png
    imagepng($im, $baseDir . $folder . '/ic_launcher_round.png');
    imagedestroy($im);
}

// Google Play Store Görselleri
$playDir = 'C:/AppServ/www/REHBER/CermikRehberiApp/PlayStore_Gorselleri/';
if (!is_dir($playDir)) mkdir($playDir, 0777, true);

// 1. App Icon (Simgemiz) - 512x512
$im = imagecreatetruecolor(512, 512);
$bg = imagecolorallocate($im, 0, 136, 204);
imagefill($im, 0, 0, $bg);
imagepng($im, $playDir . '1_Magaza_Simgesi_512x512.png');
imagedestroy($im);

// 2. Feature Graphic (Tanıtım Grafiği) - 1024x500
$im = imagecreatetruecolor(1024, 500);
$bg = imagecolorallocate($im, 0, 136, 204);
imagefill($im, 0, 0, $bg);
imagepng($im, $playDir . '2_Tanitim_Grafigi_1024x500.png');
imagedestroy($im);

echo "Gorseller Olusturuldu.";
?>
