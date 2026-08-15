<?php
declare(strict_types=1);

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\RateLimitMiddleware;

/** @var Router $router */

$auth = [fn() => AuthMiddleware::handle()];
$superAdmin = [fn() => AuthMiddleware::handle(), PermissionMiddleware::requireSuperAdmin()];
$perm = fn(string $slug) => [fn() => AuthMiddleware::handle(), PermissionMiddleware::require($slug)];

// ---------------------------------------------------------------------
// Public
// ---------------------------------------------------------------------
$router->get('/', 'HomeController@index');

$router->get('/signup', 'AuthController@showSignup');
$router->post('/signup', 'AuthController@signup', [RateLimitMiddleware::require('signup_ip', 15, 3600)]);
$router->get('/verify-otp', 'AuthController@showVerifyOtp');
$router->post('/verify-otp', 'AuthController@verifyOtp');
$router->post('/resend-otp', 'AuthController@resendOtp');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login', [RateLimitMiddleware::require('login_ip_guard', 40, 600)]);
$router->post('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// ---------------------------------------------------------------------
// Authenticated (any logged-in employee)
// ---------------------------------------------------------------------
$router->get('/dashboard', 'DashboardController@index', $auth);

$router->get('/profile', 'ProfileController@show', $auth);
$router->get('/profile/edit', 'ProfileController@edit', $auth);
$router->post('/profile/edit', 'ProfileController@update', $auth);
$router->get('/profile/preview', 'ProfileController@preview', $auth);
$router->post('/profile/submit', 'ProfileController@submit', $auth);

$router->get('/directory', 'EmployeeController@directory', $auth);

$router->get('/calendar', 'CalendarController@page', $auth);
$router->get('/calendar/feed', 'CalendarController@feed', $auth);

$router->get('/notifications', 'NotificationController@page', $auth);
$router->get('/api/notifications', 'NotificationController@list', $auth);
$router->post('/api/notifications/{id}/read', 'NotificationController@markRead', $auth);
$router->post('/api/notifications/read-all', 'NotificationController@markAllRead', $auth);

$router->get('/api/push/vapid-key', 'PushController@vapidPublicKey', $auth);
$router->post('/api/push/subscribe', 'PushController@subscribe', $auth);
$router->post('/api/push/unsubscribe', 'PushController@unsubscribe', $auth);

$router->get('/announcements', 'AnnouncementController@employeeIndex', $auth);

$router->get('/secret-santa', 'SecretSantaController@index', $auth);
$router->post('/secret-santa/opt-in', 'SecretSantaController@optIn', $auth);
$router->post('/secret-santa/opt-out', 'SecretSantaController@optOut', $auth);
$router->post('/secret-santa/preferences', 'SecretSantaController@updatePreferences', $auth);
$router->get('/secret-santa/wishlist', 'SecretSantaController@recipientWishlist', $auth);
$router->post('/secret-santa/message', 'SecretSantaController@sendMessage', $auth);
$router->get('/secret-santa/inbox', 'SecretSantaController@inbox', $auth);

// ---------------------------------------------------------------------
// Admin / management (permission-gated server-side — never trust the UI)
// ---------------------------------------------------------------------
$router->get('/admin/employees', 'EmployeeController@adminIndex', $perm('employees.view'));
$router->get('/admin/employees/{id}', 'EmployeeController@adminShow', $perm('employees.view'));
$router->get('/admin/employees/{id}/edit', 'EmployeeController@adminEdit', $perm('employees.edit'));
$router->post('/admin/employees/{id}/edit', 'EmployeeController@adminUpdate', $perm('employees.edit'));
$router->post('/admin/employees/{id}/unlock', 'EmployeeController@unlock', $perm('employees.unlock'));
$router->post('/admin/employees/{id}/lock', 'EmployeeController@lock', $perm('employees.lock'));
$router->post('/admin/employees/{id}/activate', 'EmployeeController@activate', $perm('employees.activate'));
$router->post('/admin/employees/{id}/deactivate', 'EmployeeController@deactivate', $perm('employees.deactivate'));
$router->post('/admin/employees/{id}/lock-account', 'EmployeeController@lockAccount', $perm('employees.deactivate'));
$router->post('/admin/employees/{id}/delete', 'EmployeeController@delete', $perm('employees.delete'));
$router->get('/admin/employees/{id}/roles', 'RoleController@assignForm', $perm('roles.edit'));
$router->post('/admin/employees/{id}/roles', 'RoleController@assignUpdate', $perm('roles.edit'));

$router->get('/admin/roles', 'RoleController@index', $perm('roles.view'));
$router->get('/admin/roles/create', 'RoleController@create', $perm('roles.create'));
$router->post('/admin/roles', 'RoleController@store', $perm('roles.create'));
$router->get('/admin/roles/{id}/edit', 'RoleController@edit', $perm('roles.edit'));
$router->post('/admin/roles/{id}/edit', 'RoleController@update', $perm('roles.edit'));
$router->post('/admin/roles/{id}/delete', 'RoleController@delete', $perm('roles.delete'));

$router->get('/admin/departments', 'DepartmentController@index', $perm('departments.manage'));
$router->post('/admin/departments', 'DepartmentController@store', $perm('departments.manage'));
$router->post('/admin/departments/{id}/edit', 'DepartmentController@update', $perm('departments.manage'));
$router->post('/admin/departments/{id}/delete', 'DepartmentController@delete', $perm('departments.manage'));

$router->get('/admin/designations', 'DesignationController@index', $perm('designations.manage'));
$router->post('/admin/designations', 'DesignationController@store', $perm('designations.manage'));
$router->post('/admin/designations/{id}/edit', 'DesignationController@update', $perm('designations.manage'));
$router->post('/admin/designations/{id}/delete', 'DesignationController@delete', $perm('designations.manage'));

$router->get('/admin/email-templates', 'EmailTemplateController@index', $perm('email_templates.manage'));
$router->get('/admin/email-templates/{id}/edit', 'EmailTemplateController@edit', $perm('email_templates.manage'));
$router->post('/admin/email-templates/{id}/edit', 'EmailTemplateController@update', $perm('email_templates.manage'));

$router->get('/admin/settings', 'SettingsController@index', $perm('system_settings.manage'));
$router->post('/admin/settings', 'SettingsController@update', $perm('system_settings.manage'));

$router->get('/admin/audit-logs', 'AuditLogController@index', $perm('audit_logs.view'));
$router->get('/admin/email-logs', 'AuditLogController@emailLogs', $perm('email_logs.view'));

$router->get('/admin/announcements', 'AnnouncementController@index', $perm('announcements.manage'));
$router->post('/admin/announcements', 'AnnouncementController@store', $perm('announcements.manage'));
$router->post('/admin/announcements/{id}/delete', 'AnnouncementController@delete', $perm('announcements.manage'));

$router->get('/admin/secret-santa', 'SecretSantaController@adminIndex', $perm('secret_santa.manage'));
$router->get('/admin/secret-santa/create', 'SecretSantaController@adminCreate', $perm('secret_santa.manage'));
$router->post('/admin/secret-santa', 'SecretSantaController@adminStore', $perm('secret_santa.manage'));
$router->get('/admin/secret-santa/{id}/edit', 'SecretSantaController@adminEdit', $perm('secret_santa.manage'));
$router->post('/admin/secret-santa/{id}/edit', 'SecretSantaController@adminUpdate', $perm('secret_santa.manage'));
$router->post('/admin/secret-santa/{id}/close-registration', 'SecretSantaController@adminCloseRegistration', $perm('secret_santa.manage'));
$router->post('/admin/secret-santa/{id}/generate-matching', 'SecretSantaController@adminGenerateMatching', $perm('secret_santa.manage'));
$router->get('/admin/secret-santa/{id}/reveal', 'SecretSantaController@adminEmergencyRevealForm', $superAdmin);
$router->post('/admin/secret-santa/{id}/reveal', 'SecretSantaController@adminEmergencyReveal', $superAdmin);
