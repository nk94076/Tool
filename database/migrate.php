<?php
/**
 * Simple migration runner: executes all .sql files in database/migrations
 * in filename order, tracked in a `migrations` table so re-runs are safe.
 *
 * Usage: php database/migrate.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database;

$pdo = Database::connection();

$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(190) NOT NULL UNIQUE,
    executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $pdo->query("SELECT migration FROM migrations");
$already = $stmt->fetchAll(PDO::FETCH_COLUMN);

$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $already, true)) {
        echo "SKIP  $name (already applied)\n";
        continue;
    }

    echo "APPLY $name ... ";
    $sql = file_get_contents($file);

    // Split on semicolons at end of statements (safe for this schema: no stored procs).
    $statements = array_filter(array_map('trim', preg_split('/;\s*(\r?\n|$)/', $sql)));

    // Note: DDL statements (CREATE TABLE, etc.) cause an implicit commit in
    // MySQL/MariaDB, so migrations are not wrapped in a transaction — a
    // failure partway through must be fixed by hand or the DB reset, same
    // as any other DDL-heavy migration tool.
    try {
        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }
            $pdo->exec($statement);
        }
        $ins = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:m)");
        $ins->execute(['m' => $name]);
        echo "OK\n";
    } catch (Throwable $e) {
        echo "FAILED\n";
        fwrite(STDERR, "Migration $name failed: " . $e->getMessage() . "\n");
        exit(1);
    }
}

echo "All migrations applied.\n";
