<?php
/**
 * Idempotent seeder: permissions, default roles, system settings, email
 * templates, sample departments/designations. Safe to re-run.
 *
 * The ONE Super Admin account is created separately by
 * database/seeders/create_super_admin.php — never by this script.
 *
 * Usage: php database/seeders/seed.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database;

$pdo = Database::connection();

// ---------------------------------------------------------------------
// Permissions
// ---------------------------------------------------------------------
$permissions = [
    ['dashboard.view', 'View Dashboard', 'dashboard'],
    ['employees.view', 'View Employees', 'employees'],
    ['employees.create', 'Create Employees', 'employees'],
    ['employees.edit', 'Edit Employees', 'employees'],
    ['employees.delete', 'Delete Employees', 'employees'],
    ['employees.activate', 'Activate Employees', 'employees'],
    ['employees.deactivate', 'Deactivate Employees', 'employees'],
    ['employees.unlock', 'Unlock Employee Profiles', 'employees'],
    ['employees.lock', 'Lock Employee Profiles', 'employees'],
    ['roles.view', 'View Roles', 'roles'],
    ['roles.create', 'Create Roles', 'roles'],
    ['roles.edit', 'Edit Roles', 'roles'],
    ['roles.delete', 'Delete Roles', 'roles'],
    ['permissions.manage', 'Manage Permissions', 'roles'],
    ['departments.manage', 'Manage Departments', 'organization'],
    ['designations.manage', 'Manage Designations', 'organization'],
    ['email_templates.manage', 'Manage Email Templates', 'communication'],
    ['notifications.manage', 'Manage Notification Settings', 'communication'],
    ['announcements.manage', 'Manage Announcements', 'communication'],
    ['secret_santa.manage', 'Manage Secret Santa', 'secret_santa'],
    ['reports.view', 'View Reports', 'reports'],
    ['audit_logs.view', 'View Audit Logs', 'reports'],
    ['email_logs.view', 'View Email Logs', 'reports'],
    ['notification_logs.view', 'View Notification Logs', 'reports'],
    ['system_settings.manage', 'Manage System Settings', 'system'],
];

$insertPerm = $pdo->prepare(
    "INSERT INTO permissions (slug, name, group_name) VALUES (:slug, :name, :group)
     ON DUPLICATE KEY UPDATE name = :name2, group_name = :group2"
);
foreach ($permissions as [$slug, $name, $group]) {
    $insertPerm->execute(['slug' => $slug, 'name' => $name, 'group' => $group, 'name2' => $name, 'group2' => $group]);
}
echo "Seeded " . count($permissions) . " permissions.\n";

// ---------------------------------------------------------------------
// Roles
// ---------------------------------------------------------------------
$roles = [
    ['super_admin', 'Super Admin', 'Unrestricted system owner. Bypasses all permission checks.', 1],
    ['admin', 'Admin', 'Manages employees, roles, and organization-wide settings.', 0],
    ['manager', 'Manager', 'Views team and directory data; limited management access.', 0],
    ['employee', 'Employee', 'Standard employee access to own profile and directory.', 0],
];

$insertRole = $pdo->prepare(
    "INSERT INTO roles (name, slug, description, is_system) VALUES (:name, :slug, :desc, :sys)
     ON DUPLICATE KEY UPDATE name = :name2, description = :desc2"
);
foreach ($roles as [$slug, $name, $desc, $sys]) {
    $insertRole->execute(['name' => $name, 'slug' => $slug, 'desc' => $desc, 'sys' => $sys, 'name2' => $name, 'desc2' => $desc]);
}
echo "Seeded " . count($roles) . " roles.\n";

$roleIdBySlug = [];
foreach ($pdo->query("SELECT id, slug FROM roles")->fetchAll() as $row) {
    $roleIdBySlug[$row['slug']] = (int) $row['id'];
}
$permIdBySlug = [];
foreach ($pdo->query("SELECT id, slug FROM permissions")->fetchAll() as $row) {
    $permIdBySlug[$row['slug']] = (int) $row['id'];
}

$rolePermissionMap = [
    'admin' => [
        'dashboard.view', 'employees.view', 'employees.create', 'employees.edit', 'employees.activate',
        'employees.deactivate', 'departments.manage', 'designations.manage', 'email_templates.manage',
        'notifications.manage', 'announcements.manage', 'secret_santa.manage', 'reports.view',
        'audit_logs.view', 'email_logs.view', 'notification_logs.view',
    ],
    'manager' => [
        'dashboard.view', 'employees.view', 'reports.view',
    ],
    'employee' => [
        'dashboard.view', 'employees.view',
    ],
];

$assignPerm = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:rid, :pid)");
foreach ($rolePermissionMap as $roleSlug => $permSlugs) {
    foreach ($permSlugs as $permSlug) {
        if (isset($roleIdBySlug[$roleSlug], $permIdBySlug[$permSlug])) {
            $assignPerm->execute(['rid' => $roleIdBySlug[$roleSlug], 'pid' => $permIdBySlug[$permSlug]]);
        }
    }
}
echo "Assigned default role permissions.\n";

// ---------------------------------------------------------------------
// System settings
// ---------------------------------------------------------------------
$settings = [
    ['company_name', 'Adhook Media', 'string'],
    ['allowed_email_domains', '@adhookmedia.com', 'string'],
    ['otp_expiry_minutes', '10', 'integer'],
    ['otp_resend_cooldown_seconds', '60', 'integer'],
    ['otp_max_attempts', '5', 'integer'],
    ['birthday_reminder_enabled', '1', 'boolean'],
    ['anniversary_reminder_enabled', '1', 'boolean'],
    ['browser_notifications_enabled', '1', 'boolean'],
    ['email_notifications_enabled', '1', 'boolean'],
    ['default_secret_santa_min_budget', '500', 'integer'],
    ['default_secret_santa_max_budget', '1500', 'integer'],
    ['timezone', 'Asia/Kolkata', 'string'],
    ['notification_time', '09:00', 'string'],
];

$insertSetting = $pdo->prepare(
    "INSERT INTO system_settings (setting_key, setting_value, setting_type) VALUES (:k, :v, :t)
     ON DUPLICATE KEY UPDATE setting_value = IFNULL(setting_value, :v2)"
);
foreach ($settings as [$key, $value, $type]) {
    $insertSetting->execute(['k' => $key, 'v' => $value, 't' => $type, 'v2' => $value]);
}
echo "Seeded " . count($settings) . " system settings.\n";

// ---------------------------------------------------------------------
// Departments / designations (sample starter data)
// ---------------------------------------------------------------------
$departments = ['Engineering', 'Marketing', 'Sales', 'Human Resources', 'Finance', 'Operations'];
$insertDept = $pdo->prepare("INSERT IGNORE INTO departments (name) VALUES (:name)");
foreach ($departments as $name) {
    $insertDept->execute(['name' => $name]);
}
echo "Seeded departments.\n";

$designations = ['Software Engineer', 'Senior Software Engineer', 'Marketing Executive', 'Sales Executive', 'HR Executive', 'Finance Executive', 'Team Lead', 'Manager'];
$insertDesig = $pdo->prepare("INSERT IGNORE INTO designations (name) VALUES (:name)");
foreach ($designations as $name) {
    $insertDesig->execute(['name' => $name]);
}
echo "Seeded designations.\n";

// ---------------------------------------------------------------------
// Email templates
// ---------------------------------------------------------------------
$templates = [
    [
        'otp_verification', 'OTP Verification', 'Your Adhook Employee Portal verification code',
        '<p>Hello,</p><p>Your one-time verification code is:</p><h2>{{otp_code}}</h2><p>This code will expire in {{expiry_minutes}} minutes. Do not share this code with anyone.</p><p>— Adhook Employee Portal</p>',
    ],
    [
        'welcome', 'Welcome Email', 'Welcome to Adhook Employee Portal',
        '<p>Hi {{employee_name}},</p><p>Your account has been created. Please log in and complete your employee profile.</p><p>— Adhook Employee Portal</p>',
    ],
    [
        'profile_unlock', 'Profile Unlocked', 'Your employee profile has been unlocked',
        '<p>Hi {{employee_name}},</p><p>Your profile has been unlocked by the Super Admin for editing. Please review and resubmit your details.</p><p>— Adhook Employee Portal</p>',
    ],
    [
        'birthday_reminder', 'Birthday Reminder', 'Tomorrow is {{employee_name}}\'s Birthday 🎂',
        '<p>A friendly reminder that tomorrow is <strong>{{employee_name}}</strong>\'s birthday!</p>',
    ],
    [
        'birthday_today', 'Birthday Today', 'Happy Birthday {{employee_name}}! 🎉',
        '<p>Join us in wishing <strong>{{employee_name}}</strong> a very happy birthday today!</p>',
    ],
    [
        'anniversary_reminder', 'Anniversary Reminder', 'Tomorrow is {{employee_name}}\'s {{years_completed}} Year Work Anniversary 🎉',
        '<p>A friendly reminder that tomorrow marks {{years_completed}} year(s) since <strong>{{employee_name}}</strong> joined us on {{joining_date}}!</p>',
    ],
    [
        'anniversary_today', 'Anniversary Today', 'Happy {{years_completed}} Year Work Anniversary {{employee_name}}!',
        '<p>Congratulations to <strong>{{employee_name}}</strong> on completing {{years_completed}} year(s) with Adhook Media today!</p>',
    ],
];

$insertTemplate = $pdo->prepare(
    "INSERT INTO email_templates (slug, name, subject, body_html) VALUES (:slug, :name, :subject, :body)
     ON DUPLICATE KEY UPDATE name = name"
);
foreach ($templates as [$slug, $name, $subject, $body]) {
    $insertTemplate->execute(['slug' => $slug, 'name' => $name, 'subject' => $subject, 'body' => $body]);
}
echo "Seeded " . count($templates) . " email templates.\n";

echo "\nSeeding complete. Next: php database/seeders/create_super_admin.php\n";
