<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\SystemSetting;
use App\Models\RateLimit;
use App\Services\OtpService;
use App\Services\MailService;
use App\Services\AuditService;

final class AuthController extends Controller
{
    // ---------------------------------------------------------------
    // Signup
    // ---------------------------------------------------------------
    public function showSignup(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/signup', ['title' => 'Sign Up'], 'layouts/auth');
    }

    public function signup(): void
    {
        $this->verifyCsrf();

        if (!(new RateLimit())->attempt($_SERVER['REMOTE_ADDR'] ?? 'unknown', 'signup', 5, 3600)) {
            set_flash('error', 'Too many signup attempts. Please try again later.');
            $this->redirect('/signup');
        }

        $fullName = trim((string) $this->input('full_name', ''));
        $email = mb_strtolower(trim((string) $this->input('official_email', '')));
        $password = (string) $this->input('password', '');
        $confirm = (string) $this->input('confirm_password', '');

        Session::set('_old', ['full_name' => $fullName, 'official_email' => $email]);

        if ($fullName === '' || $email === '' || $password === '') {
            set_flash('error', 'All fields are required.');
            $this->redirect('/signup');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Please enter a valid email address.');
            $this->redirect('/signup');
        }
        if (!$this->isOfficialDomain($email)) {
            set_flash('error', 'Registration is only allowed using your official Adhook company email address.');
            $this->redirect('/signup');
        }
        if (strlen($password) < 8) {
            set_flash('error', 'Password must be at least 8 characters.');
            $this->redirect('/signup');
        }
        if ($password !== $confirm) {
            set_flash('error', 'Passwords do not match.');
            $this->redirect('/signup');
        }

        $userModel = new User();
        $existing = $userModel->findByEmail($email);

        if ($existing && $existing['status'] !== 'pending_verification') {
            set_flash('error', 'An account with this email already exists. Please log in.');
            $this->redirect('/login');
        }

        if ($existing) {
            // Re-signup while still pending verification: refresh password, resend OTP.
            $userModel->update((int) $existing['id'], [
                'full_name' => $fullName,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            ]);
        } else {
            $userId = $userModel->insert([
                'full_name' => $fullName,
                'official_email' => $email,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'status' => 'pending_verification',
            ]);
            (new EmployeeProfile())->createForUser($userId);
        }

        (new OtpService())->generateAndSend($email, 'signup');

        Session::set('otp_pending_email', $email);
        set_flash('success', 'We sent a 6-digit verification code to your official email.');
        $this->redirect('/verify-otp');
    }

    private function isOfficialDomain(string $email): bool
    {
        $domains = (new SystemSetting())->allowedEmailDomains();
        foreach ($domains as $domain) {
            if (str_ends_with($email, mb_strtolower($domain))) {
                return true;
            }
        }
        return false;
    }

    // ---------------------------------------------------------------
    // OTP verification
    // ---------------------------------------------------------------
    public function showVerifyOtp(): void
    {
        $email = Session::get('otp_pending_email');
        if (!$email) {
            $this->redirect('/signup');
        }
        $otpService = new OtpService();
        $this->view('auth/verify_otp', [
            'title' => 'Verify Email',
            'email' => $email,
            'resendCooldown' => $otpService->resendCooldownSeconds(),
        ], 'layouts/auth');
    }

    public function verifyOtp(): void
    {
        $this->verifyCsrf();
        $email = Session::get('otp_pending_email');
        if (!$email) {
            $this->redirect('/signup');
        }

        $key = $email;
        if (!(new RateLimit())->attempt($key, 'otp_verify', 10, 600)) {
            set_flash('error', 'Too many verification attempts. Please try again later.');
            $this->redirect('/verify-otp');
        }

        $code = trim((string) $this->input('otp_code', ''));
        $result = (new OtpService())->verify($email, 'signup', $code);

        if (!$result['success']) {
            $message = match ($result['reason']) {
                'expired' => 'This code has expired. Please request a new one.',
                'max_attempts' => 'Too many incorrect attempts. Please request a new code.',
                'invalid' => 'Incorrect verification code.',
                default => 'No active verification code found. Please request a new one.',
            };
            set_flash('error', $message);
            $this->redirect('/verify-otp');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user) {
            $this->redirect('/signup');
        }

        $userModel->update((int) $user['id'], [
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        // Assign default "employee" role on first verification.
        if (empty($userModel->roles((int) $user['id']))) {
            $roleModel = new \App\Models\Role();
            $employeeRole = $roleModel->findBySlug('employee');
            if ($employeeRole) {
                $userModel->assignRole((int) $user['id'], (int) $employeeRole['id'], null);
            }
        }

        AuditService::log('user.email_verified', (int) $user['id']);
        Session::remove('otp_pending_email');

        Auth::login($userModel->find((int) $user['id']));
        set_flash('success', 'Email verified! Please complete your employee profile.');
        $this->redirect('/profile/edit');
    }

    public function resendOtp(): void
    {
        $this->verifyCsrf();
        $email = Session::get('otp_pending_email');
        if (!$email) {
            $this->redirect('/signup');
        }

        if (!(new RateLimit())->attempt($email, 'otp_resend', 5, 3600)) {
            set_flash('error', 'Too many resend requests. Please try again later.');
            $this->redirect('/verify-otp');
        }

        $result = (new OtpService())->generateAndSend($email, 'signup');
        if ($result === OtpService::RESULT_COOLDOWN) {
            set_flash('error', 'Please wait before requesting another code.');
        } else {
            set_flash('success', 'A new verification code has been sent.');
        }
        $this->redirect('/verify-otp');
    }

    // ---------------------------------------------------------------
    // Login / Logout
    // ---------------------------------------------------------------
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login', ['title' => 'Login'], 'layouts/auth');
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email = mb_strtolower(trim((string) $this->input('official_email', '')));
        $password = (string) $this->input('password', '');
        Session::set('_old', ['official_email' => $email]);

        $ipKey = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!(new RateLimit())->attempt($ipKey, 'login_ip', 20, 600) || !(new RateLimit())->attempt($email, 'login_email', 8, 600)) {
            set_flash('error', 'Too many login attempts. Please try again later.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || $user['deleted_at'] !== null) {
            set_flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        if ($user['status'] === 'locked' || ($user['locked_until'] && strtotime($user['locked_until']) > time())) {
            set_flash('error', 'Your account is locked. Please contact the Super Admin.');
            $this->redirect('/login');
        }

        if ($user['status'] === 'inactive') {
            set_flash('error', 'Your account has been deactivated. Please contact the Super Admin.');
            $this->redirect('/login');
        }

        if ($user['status'] === 'pending_verification') {
            Session::set('otp_pending_email', $email);
            set_flash('error', 'Please verify your email before logging in.');
            $this->redirect('/verify-otp');
        }

        if (!password_verify($password, $user['password_hash'])) {
            $attempts = (int) $user['failed_login_attempts'] + 1;
            $update = ['failed_login_attempts' => $attempts];
            if ($attempts >= 5) {
                $update['locked_until'] = (new \DateTime('+15 minutes'))->format('Y-m-d H:i:s');
            }
            $userModel->update((int) $user['id'], $update);
            set_flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        $userModel->update((int) $user['id'], [
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        Auth::login($user);
        AuditService::log('user.login', (int) $user['id']);

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        if (Auth::check()) {
            AuditService::log('user.logout', Auth::id());
        }
        Auth::logout();
        $this->redirect('/login');
    }

    // ---------------------------------------------------------------
    // Password reset
    // ---------------------------------------------------------------
    public function showForgotPassword(): void
    {
        $this->view('auth/forgot_password', ['title' => 'Forgot Password'], 'layouts/auth');
    }

    public function forgotPassword(): void
    {
        $this->verifyCsrf();
        $email = mb_strtolower(trim((string) $this->input('official_email', '')));

        if (!(new RateLimit())->attempt($email, 'password_reset_request', 5, 3600)) {
            set_flash('success', 'If that email exists, a reset link has been sent.');
            $this->redirect('/forgot-password');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        // Always show the same message to avoid leaking whether an account exists.
        if ($user && $user['status'] === 'active') {
            $token = bin2hex(random_bytes(32));
            $userModel->update((int) $user['id'], [
                'password_reset_token' => hash('sha256', $token),
                'password_reset_expires_at' => (new \DateTime('+30 minutes'))->format('Y-m-d H:i:s'),
            ]);
            $link = url('/reset-password?token=' . $token . '&email=' . urlencode($email));
            MailService::sendNow($email, 'Reset your Adhook Employee Portal password',
                "<p>Click the link below to reset your password. This link expires in 30 minutes.</p><p><a href=\"{$link}\">{$link}</a></p><p>If you did not request this, you can ignore this email.</p>",
                'password_reset');
        }

        set_flash('success', 'If that email exists, a reset link has been sent.');
        $this->redirect('/forgot-password');
    }

    public function showResetPassword(array $params): void
    {
        $this->view('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $this->input('token', ''),
            'email' => $this->input('email', ''),
        ], 'layouts/auth');
    }

    public function resetPassword(): void
    {
        $this->verifyCsrf();
        $email = mb_strtolower(trim((string) $this->input('email', '')));
        $token = (string) $this->input('token', '');
        $password = (string) $this->input('password', '');
        $confirm = (string) $this->input('confirm_password', '');

        if (strlen($password) < 8 || $password !== $confirm) {
            set_flash('error', 'Passwords must match and be at least 8 characters.');
            $this->redirect('/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !$user['password_reset_token'] || !hash_equals($user['password_reset_token'], hash('sha256', $token))) {
            set_flash('error', 'Invalid or expired reset link.');
            $this->redirect('/forgot-password');
        }
        if (!$user['password_reset_expires_at'] || strtotime($user['password_reset_expires_at']) < time()) {
            set_flash('error', 'This reset link has expired.');
            $this->redirect('/forgot-password');
        }

        $userModel->update((int) $user['id'], [
            'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        AuditService::log('user.password_reset', (int) $user['id']);
        set_flash('success', 'Your password has been reset. Please log in.');
        $this->redirect('/login');
    }
}
