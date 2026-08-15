<?php
/**
 * Creates the ONE Super Admin account. CLI-only by design — there is no
 * web-based "create super admin" endpoint, so this cannot be triggered
 * remotely. Refuses to run if a Super Admin already exists.
 *
 * Usage (interactive):
 *   php database/seeders/create_super_admin.php
 *
 * Usage (non-interactive, e.g. CI/first deploy):
 *   php database/seeders/create_super_admin.php "Full Name" "email@adhookmedia.com" "StrongPassword123!"
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database;
use App\Models\SystemSetting;

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('This script may only be run from the command line.');
}

$pdo = Database::connection();

$existing = $pdo->query("SELECT id, full_name, official_email FROM users WHERE is_super_admin = 1 LIMIT 1")->fetch();
if ($existing) {
    fwrite(STDERR, "A Super Admin already exists ({$existing['official_email']}). Refusing to create another.\n");
    exit(1);
}

function prompt(string $label, bool $hidden = false): string
{
    echo $label;
    if ($hidden && stripos(PHP_OS, 'WIN') === false) {
        system('stty -echo');
        $value = trim((string) fgets(STDIN));
        system('stty echo');
        echo "\n";
    } else {
        $value = trim((string) fgets(STDIN));
    }
    return $value;
}

$argFullName = $argv[1] ?? null;
$argEmail = $argv[2] ?? null;
$argPassword = $argv[3] ?? null;

$fullName = $argFullName ?? prompt('Super Admin Full Name: ');
$email = mb_strtolower($argEmail ?? prompt('Super Admin Official Email: '));
$password = $argPassword ?? prompt('Super Admin Password (min 12 chars): ', true);

if ($fullName === '' || $email === '' || $password === '') {
    fwrite(STDERR, "All fields are required.\n");
    exit(1);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Invalid email address.\n");
    exit(1);
}

$allowedDomains = (new SystemSetting())->allowedEmailDomains();
$domainOk = false;
foreach ($allowedDomains as $domain) {
    if (str_ends_with($email, mb_strtolower($domain))) {
        $domainOk = true;
        break;
    }
}
if (!$domainOk) {
    fwrite(STDERR, "Email domain is not in the allowed official domains list (" . implode(', ', $allowedDomains) . "). Update system_settings.allowed_email_domains first if needed.\n");
    exit(1);
}

if (strlen($password) < 12) {
    fwrite(STDERR, "Password must be at least 12 characters.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (full_name, official_email, password_hash, is_super_admin, status, email_verified_at, profile_status)
         VALUES (:name, :email, :hash, 1, 'active', NOW(), 'not_started')"
    );
    $stmt->execute(['name' => $fullName, 'email' => $email, 'hash' => $hash]);
    $userId = (int) $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO employee_profiles (user_id) VALUES (:uid)")->execute(['uid' => $userId]);

    $roleId = $pdo->query("SELECT id FROM roles WHERE slug = 'super_admin'")->fetchColumn();
    if ($roleId) {
        $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)")
            ->execute(['uid' => $userId, 'rid' => $roleId]);
    }

    $pdo->prepare(
        "INSERT INTO audit_logs (actor_user_id, subject_user_id, action, ip_address, user_agent)
         VALUES (:uid, :uid, 'super_admin.created', 'cli', 'cli')"
    )->execute(['uid' => $userId]);

    $pdo->commit();
    echo "Super Admin created successfully: $email (user id $userId)\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Failed to create Super Admin: ' . $e->getMessage() . "\n");
    exit(1);
}
