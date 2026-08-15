<?php
/**
 * Central configuration array, sourced from environment variables.
 * Do not put credentials directly in code — always via .env.
 */
declare(strict_types=1);

return [
    'app' => [
        'name' => env('APP_NAME', 'Adhook Employee Portal'),
        'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
        'env' => env('APP_ENV', 'production'),
        'debug' => (bool) env('APP_DEBUG', false),
        'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
        'key' => env('APP_KEY', ''),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => (int) env('DB_PORT', 3306),
        'name' => env('DB_NAME', 'adhook_portal'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
    'mail' => [
        'host' => env('MAIL_HOST', ''),
        'port' => (int) env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_email' => env('MAIL_FROM_EMAIL', 'noreply@adhookmedia.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Adhook Employee Portal'),
    ],
    'vapid' => [
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@adhookmedia.com'),
    ],
    'session' => [
        'name' => env('SESSION_NAME', 'adhook_portal_session'),
        'lifetime' => (int) env('SESSION_LIFETIME', 120), // minutes
        'secure' => (bool) env('SESSION_SECURE_COOKIE', true),
    ],
    'uploads' => [
        'max_mb' => (int) env('MAX_UPLOAD_MB', 2),
        'path' => BASE_PATH . '/public/uploads/profile_photos',
        'allowed_mime' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_ext' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
    'cron' => [
        'secret' => env('CRON_SECRET', ''),
    ],
];
