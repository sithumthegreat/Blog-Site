<?php
function uploadCoverImage(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxFileSize = 2 * 1024 * 1024; // 2 MB Limit

    if ($file['size'] > $maxFileSize) {
        return null;
    }

    // Check MIME type using built-in mime_content_type
    $mimeType = mime_content_type($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes, true)) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Resized images are always saved as .jpg to keep things simple and
    // keep file sizes small and predictable, regardless of the source format.
    $filename = bin2hex(random_bytes(16)) . '.jpg';
    $destination = $uploadDir . $filename;

    $resized = resizeAndSaveCoverImage($file['tmp_name'], $mimeType, $destination);

    if (!$resized) {
        return null;
    }

    return 'uploads/' . $filename;
}

/**
 * Resizes an uploaded image down to a max width/height (keeping aspect
 * ratio) and saves it as a compressed JPEG. This makes the actual file
 * on disk small — not just visually cropped by CSS.
 */
function resizeAndSaveCoverImage(string $sourcePath, string $mimeType, string $destination): bool {
    $maxWidth = 1200;
    $maxHeight = 800;
    $jpegQuality = 80; // 0 (worst) - 100 (best). 80 is a good size/quality balance.

    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = @imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = @imagecreatefrompng($sourcePath);
            break;
        case 'image/webp':
            $sourceImage = @imagecreatefromwebp($sourcePath);
            break;
        case 'image/gif':
            $sourceImage = @imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    if (!$sourceImage) {
        return false;
    }

    $originalWidth = imagesx($sourceImage);
    $originalHeight = imagesy($sourceImage);

    // Only shrink if the image is actually bigger than our target —
    // never upscale a small image.
    $scale = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);

    $newWidth = (int) round($originalWidth * $scale);
    $newHeight = (int) round($originalHeight * $scale);

    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

    // Flatten transparency (PNG/GIF/WEBP) onto a white background,
    // since we're always saving as JPEG which has no alpha channel.
    $white = imagecolorallocate($resizedImage, 255, 255, 255);
    imagefill($resizedImage, 0, 0, $white);

    imagecopyresampled(
        $resizedImage,
        $sourceImage,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $originalWidth, $originalHeight
    );

    $saved = imagejpeg($resizedImage, $destination, $jpegQuality);

    imagedestroy($sourceImage);
    imagedestroy($resizedImage);

    return $saved;
}