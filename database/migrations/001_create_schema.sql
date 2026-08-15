-- =====================================================================
-- Adhook Employee Portal - Core Schema
-- MySQL 8+ | InnoDB | utf8mb4
-- Run in order: 001, 002, 003 ... via migrate.php or manually.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ---------------------------------------------------------------------
-- system_settings: key/value store, configurable by Super Admin
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('string','integer','boolean','json') NOT NULL DEFAULT 'string',
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    updated_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- roles / permissions / role_permissions / user_roles
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    description VARCHAR(255) NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0, -- system roles (super_admin) cannot be deleted
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_role_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL,
    name VARCHAR(150) NOT NULL,
    group_name VARCHAR(60) NOT NULL DEFAULT 'general',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_permission_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- departments / designations
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_department_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS designations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_designation_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    KEY idx_designation_department (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- users (login/auth identity)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    official_email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_super_admin TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending_verification','active','inactive','locked') NOT NULL DEFAULT 'pending_verification',
    email_verified_at DATETIME NULL,
    profile_status ENUM('not_started','in_progress','submitted_locked') NOT NULL DEFAULT 'not_started',
    profile_unlocked TINYINT(1) NOT NULL DEFAULT 0,
    failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_login_at DATETIME NULL,
    last_login_ip VARCHAR(64) NULL,
    remember_token VARCHAR(100) NULL,
    password_reset_token VARCHAR(255) NULL,
    password_reset_expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uq_users_email (official_email),
    KEY idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- employee_profiles
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employee_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    employee_code VARCHAR(30) NULL,
    profile_photo_path VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male','female','other','prefer_not_to_say') NULL,
    mobile_number VARCHAR(20) NULL,
    personal_email VARCHAR(190) NULL,
    current_address TEXT NULL,
    emergency_contact_name VARCHAR(150) NULL,
    emergency_contact_number VARCHAR(20) NULL,
    date_of_joining DATE NULL,
    department_id INT UNSIGNED NULL,
    designation_id INT UNSIGNED NULL,
    reporting_manager_id INT UNSIGNED NULL,
    employment_type ENUM('full_time','part_time','contract','intern') NULL,
    work_location VARCHAR(150) NULL,
    employment_status ENUM('active','on_leave','resigned','terminated') NOT NULL DEFAULT 'active',
    is_locked TINYINT(1) NOT NULL DEFAULT 0,
    submitted_at DATETIME NULL,
    unlocked_at DATETIME NULL,
    unlocked_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ep_user (user_id),
    UNIQUE KEY uq_ep_employee_code (employee_code),
    KEY idx_ep_dob (date_of_birth),
    KEY idx_ep_doj (date_of_joining),
    KEY idx_ep_department (department_id),
    KEY idx_ep_designation (designation_id),
    KEY idx_ep_status (employment_status),
    CONSTRAINT fk_ep_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ep_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    CONSTRAINT fk_ep_designation FOREIGN KEY (designation_id) REFERENCES designations(id) ON DELETE SET NULL,
    CONSTRAINT fk_ep_manager FOREIGN KEY (reporting_manager_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- otp_verifications
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    purpose ENUM('signup','password_reset','login_2fa') NOT NULL DEFAULT 'signup',
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 5,
    is_used TINYINT(1) NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    last_sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resend_count INT UNSIGNED NOT NULL DEFAULT 0,
    ip_address VARCHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_otp_email_purpose (email, purpose),
    KEY idx_otp_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- rate_limits (generic sliding-window limiter for login/signup/otp)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(190) NOT NULL,
    action VARCHAR(50) NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 1,
    window_started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rl_key_action (`key`, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- notifications (in-app notification center)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(60) NOT NULL,
    title VARCHAR(190) NOT NULL,
    body VARCHAR(500) NULL,
    url VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_notif_user_read (user_id, is_read),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- push_subscriptions (Web Push API)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    endpoint VARCHAR(500) NOT NULL,
    p256dh VARCHAR(255) NOT NULL,
    auth VARCHAR(255) NOT NULL,
    user_agent VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME NULL,
    UNIQUE KEY uq_push_endpoint (endpoint(255)),
    KEY idx_push_user (user_id),
    CONSTRAINT fk_push_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- email_templates / email_logs / email_queue
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL,
    name VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html MEDIUMTEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    updated_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_template_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_queue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(190) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html MEDIUMTEXT NOT NULL,
    template_slug VARCHAR(80) NULL,
    status ENUM('pending','processing','sent','failed') NOT NULL DEFAULT 'pending',
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 3,
    error_message VARCHAR(500) NULL,
    scheduled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_eq_status (status, scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(190) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    template_slug VARCHAR(80) NULL,
    status ENUM('sent','failed') NOT NULL,
    error_message VARCHAR(500) NULL,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_el_recipient (recipient_email),
    KEY idx_el_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- celebration_logs (dedupe birthday / anniversary sends)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS celebration_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    event_type ENUM('birthday_reminder','birthday_today','anniversary_reminder','anniversary_today') NOT NULL,
    event_year INT UNSIGNED NOT NULL, -- calendar year this event applies to, prevents dup across years
    channel ENUM('email','push','in_app') NOT NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_celebration (user_id, event_type, event_year, channel),
    CONSTRAINT fk_cl_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- audit_logs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT UNSIGNED NULL,
    subject_user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    field_name VARCHAR(100) NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    ip_address VARCHAR(64) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_al_subject (subject_user_id),
    KEY idx_al_actor (actor_user_id),
    KEY idx_al_action (action),
    KEY idx_al_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- announcements
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS announcements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    body TEXT NOT NULL,
    category ENUM('holiday','notice','event','general') NOT NULL DEFAULT 'general',
    event_date DATE NULL,
    notify_email TINYINT(1) NOT NULL DEFAULT 0,
    notify_push TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_ann_date (event_date),
    CONSTRAINT fk_ann_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- secret_santa_events / participants / preferences / assignments / messages
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS secret_santa_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    event_year YEAR NOT NULL,
    registration_deadline DATE NOT NULL,
    gift_exchange_date DATE NOT NULL,
    min_budget DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_budget DECIMAL(10,2) NOT NULL DEFAULT 0,
    rules TEXT NULL,
    allow_inactive_employees TINYINT(1) NOT NULL DEFAULT 0,
    avoid_previous_year_pairing TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('draft','active','registration_closed','matched','completed','cancelled') NOT NULL DEFAULT 'draft',
    matched_at DATETIME NULL,
    matched_by INT UNSIGNED NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_ss_year (event_year),
    KEY idx_ss_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS secret_santa_participants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    opted_in TINYINT(1) NOT NULL DEFAULT 1,
    opted_in_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    opted_out_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ssp_event_user (event_id, user_id),
    CONSTRAINT fk_ssp_event FOREIGN KEY (event_id) REFERENCES secret_santa_events(id) ON DELETE CASCADE,
    CONSTRAINT fk_ssp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- one evergreen preference profile per user, reused across events (updatable any time)
CREATE TABLE IF NOT EXISTS secret_santa_preferences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    things_i_like TEXT NULL,
    things_i_dislike TEXT NULL,
    favourite_categories VARCHAR(255) NULL,
    favourite_colours VARCHAR(255) NULL,
    preferred_brands VARCHAR(255) NULL,
    wishlist TEXT NULL,
    budget_preference VARCHAR(100) NULL,
    additional_note TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ssprefs_user (user_id),
    CONSTRAINT fk_ssprefs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- santa_user_id -> recipient_user_id, generated once and locked.
-- No column here is ever exposed as "who gave me a gift" in any query.
CREATE TABLE IF NOT EXISTS secret_santa_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    santa_user_id INT UNSIGNED NOT NULL,
    recipient_user_id INT UNSIGNED NOT NULL,
    revealed_by_admin TINYINT(1) NOT NULL DEFAULT 0,
    revealed_at DATETIME NULL,
    revealed_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ssa_event_santa (event_id, santa_user_id),
    UNIQUE KEY uq_ssa_event_recipient (event_id, recipient_user_id),
    KEY idx_ssa_event (event_id),
    CONSTRAINT fk_ssa_event FOREIGN KEY (event_id) REFERENCES secret_santa_events(id) ON DELETE CASCADE,
    CONSTRAINT fk_ssa_santa FOREIGN KEY (santa_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ssa_recipient FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_ssa_not_self CHECK (santa_user_id <> recipient_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS secret_santa_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    assignment_id INT UNSIGNED NOT NULL,
    sender_user_id INT UNSIGNED NOT NULL, -- never returned via API responses to the recipient
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ssm_assignment (assignment_id),
    CONSTRAINT fk_ssm_event FOREIGN KEY (event_id) REFERENCES secret_santa_events(id) ON DELETE CASCADE,
    CONSTRAINT fk_ssm_assignment FOREIGN KEY (assignment_id) REFERENCES secret_santa_assignments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- previous-year pairing memory used only to avoid repeat matches; no reveal implications
CREATE TABLE IF NOT EXISTS secret_santa_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_year YEAR NOT NULL,
    santa_user_id INT UNSIGNED NOT NULL,
    recipient_user_id INT UNSIGNED NOT NULL,
    KEY idx_ssh_year (event_year),
    KEY idx_ssh_santa (santa_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
