<?php
declare(strict_types=1);

namespace App\Services;

final class FileUploadService
{
    /**
     * Validates and stores a profile photo upload. Returns the relative
     * public path on success, or null on any validation failure.
     *
     * Security measures:
     *  - Real MIME sniffing via finfo (never trusts the client Content-Type)
     *  - Extension whitelist cross-checked against sniffed MIME
     *  - Size limit
     *  - Filename regenerated (random) — never uses client-supplied name
     *  - Stored outside PHP execution (see public/.htaccess uploads block)
     */
    public static function handleProfilePhoto(array $file, int $userId): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $maxBytes = (int) config('uploads.max_mb') * 1024 * 1024;
        if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMime = (array) config('uploads.allowed_mime');
        if (!in_array($mime, $allowedMime, true)) {
            return null;
        }

        $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $mimeToExt[$mime] ?? null;
        if ($ext === null || !in_array($ext, (array) config('uploads.allowed_ext'), true)) {
            return null;
        }

        // Re-encode via GD to strip any embedded payload / EXIF and guarantee
        // the file really is a decodable image, not just a spoofed MIME type.
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png' => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
            default => null,
        };
        if ($image === false || $image === null) {
            return null;
        }

        $dir = (string) config('uploads.path');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'u' . $userId . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $dir . '/' . $filename;

        $saved = match ($ext) {
            'jpg' => imagejpeg($image, $destination, 85),
            'png' => imagepng($image, $destination, 6),
            'webp' => imagewebp($image, $destination, 85),
            default => false,
        };
        imagedestroy($image);

        if (!$saved) {
            return null;
        }

        return '/uploads/profile_photos/' . $filename;
    }
}
