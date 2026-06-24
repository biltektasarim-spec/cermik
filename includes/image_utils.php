<?php
/**
 * Image Utilities for RotaRehber
 */

/**
 * Resizes an image to fit within maxWidth and maxHeight while maintaining aspect ratio.
 * 
 * @param string $sourcePath Path to source image
 * @param string $targetPath Path to save resized image
 * @param int $maxWidth Maximum width
 * @param int $maxHeight Maximum height
 * @param int $quality JPEG/WEBP quality (0-100)
 * @return bool True on success, false on failure
 */
function resizeImage($sourcePath, $targetPath, $maxWidth, $maxHeight, $quality = 80) {
    if (!file_exists($sourcePath)) return false;
    
    // Check if GD library is enabled
    if (!function_exists('imagecreatetruecolor')) {
        // Fallback: just copy the file if no resize is possible
        if ($sourcePath !== $targetPath) {
            return @copy($sourcePath, $targetPath);
        }
        return true;
    }
    $info = getimagesize($sourcePath);
    if (!$info) return false;
    
    list($width, $height, $type) = $info;
    $ratio = $width / $height;
    
    // Calculate new dimensions
    if ($width > $maxWidth || $height > $maxHeight) {
        if ($maxWidth / $maxHeight > $ratio) {
            $newWidth = $maxHeight * $ratio;
            $newHeight = $maxHeight;
        } else {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
        }
    } else {
        // No need to resize, just copy if different paths
        if ($sourcePath !== $targetPath) {
            return copy($sourcePath, $targetPath);
        }
        return true;
    }
    
    $newWidth = (int)$newWidth;
    $newHeight = (int)$newHeight;
    $dst = imagecreatetruecolor($newWidth, $newHeight);
    
    // Handle transparency for PNG and WEBP
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP || $type == IMAGETYPE_GIF) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Load source image
    switch ($type) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG:  $src = @imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_GIF:  $src = @imagecreatefromgif($sourcePath); break;
        case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($sourcePath); break;
        default: return false;
    }
    
    if (!$src) return false;
    
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save image
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG: 
            $result = imagejpeg($dst, $targetPath, $quality); 
            break;
        case IMAGETYPE_PNG:  
            // PNG quality is 0-9
            $pngQuality = (int)(($quality / 100) * 9);
            $result = imagepng($dst, $targetPath, 9 - $pngQuality); 
            break;
        case IMAGETYPE_GIF:  
            $result = imagegif($dst, $targetPath); 
            break;
        case IMAGETYPE_WEBP: 
            $result = imagewebp($dst, $targetPath, $quality); 
            break;
    }
    
    imagedestroy($src);
    imagedestroy($dst);
    return $result;
}
