<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Single shared PDO connection. Always uses prepared statements.
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                config('db.host'),
                config('db.port'),
                config('db.name')
            );

            // Align MySQL session timezone with the app's configured timezone so
            // CURRENT_TIMESTAMP/NOW() defaults match PHP-generated datetimes.
            $tz = new \DateTimeZone((string) config('app.timezone', 'Asia/Kolkata'));
            $offset = $tz->getOffset(new \DateTime('now', $tz));
            $sign = $offset < 0 ? '-' : '+';
            $offset = abs($offset);
            $mysqlOffset = sprintf('%s%02d:%02d', $sign, intdiv($offset, 3600), intdiv($offset % 3600, 60));

            try {
                self::$instance = new PDO($dsn, config('db.user'), config('db.pass'), [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '$mysqlOffset'",
                ]);
            } catch (PDOException $e) {
                app_log('DB connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Database connection failed.');
            }
        }

        return self::$instance;
    }
}
