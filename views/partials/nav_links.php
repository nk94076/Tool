<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$is = fn(string $prefix) => str_starts_with($path, $prefix) ? 'active' : '';
?>
<ul class="nav flex-column nav-list">
  <li class="nav-item"><a class="nav-link <?= $is('/dashboard') ?>" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
  <li class="nav-item"><a class="nav-link <?= $is('/directory') ?>" href="/directory"><i class="bi bi-people"></i> Directory</a></li>
  <li class="nav-item"><a class="nav-link <?= $is('/profile') ?>" href="/profile"><i class="bi bi-person-badge"></i> My Profile</a></li>
  <li class="nav-item"><a class="nav-link <?= $is('/calendar') ?>" href="/calendar"><i class="bi bi-calendar3"></i> Calendar</a></li>
  <li class="nav-item"><a class="nav-link <?= $is('/announcements') ?>" href="/announcements"><i class="bi bi-megaphone"></i> Announcements</a></li>
  <li class="nav-item"><a class="nav-link <?= $is('/secret-santa') ?>" href="/secret-santa"><i class="bi bi-gift"></i> Secret Santa</a></li>

  <?php if (can('employees.view') || can('roles.view') || can('departments.manage') || can('designations.manage') || can('email_templates.manage') || can('announcements.manage') || can('secret_santa.manage') || can('audit_logs.view') || can('system_settings.manage')): ?>
    <li class="nav-section">Administration</li>
  <?php endif; ?>

  <?php if (can('employees.view')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/employees') ?>" href="/admin/employees"><i class="bi bi-person-lines-fill"></i> Manage Employees</a></li>
  <?php endif; ?>
  <?php if (can('roles.view')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/roles') ?>" href="/admin/roles"><i class="bi bi-shield-lock"></i> Roles &amp; Permissions</a></li>
  <?php endif; ?>
  <?php if (can('departments.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/departments') ?>" href="/admin/departments"><i class="bi bi-diagram-3"></i> Departments</a></li>
  <?php endif; ?>
  <?php if (can('designations.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/designations') ?>" href="/admin/designations"><i class="bi bi-briefcase"></i> Designations</a></li>
  <?php endif; ?>
  <?php if (can('secret_santa.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/secret-santa') ?>" href="/admin/secret-santa"><i class="bi bi-gift-fill"></i> Secret Santa Events</a></li>
  <?php endif; ?>
  <?php if (can('announcements.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/announcements') ?>" href="/admin/announcements"><i class="bi bi-megaphone-fill"></i> Manage Announcements</a></li>
  <?php endif; ?>
  <?php if (can('email_templates.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/email-templates') ?>" href="/admin/email-templates"><i class="bi bi-envelope-paper"></i> Email Templates</a></li>
  <?php endif; ?>
  <?php if (can('audit_logs.view')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/audit-logs') ?>" href="/admin/audit-logs"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
  <?php endif; ?>
  <?php if (can('email_logs.view')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/email-logs') ?>" href="/admin/email-logs"><i class="bi bi-envelope-check"></i> Email Logs</a></li>
  <?php endif; ?>
  <?php if (can('system_settings.manage')): ?>
    <li class="nav-item"><a class="nav-link <?= $is('/admin/settings') ?>" href="/admin/settings"><i class="bi bi-gear"></i> System Settings</a></li>
  <?php endif; ?>
</ul>
