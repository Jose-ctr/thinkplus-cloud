<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Security Foundation
 * ============================================================
 *
 * Phase 3: Authentication & Security
 *
 * Founder: Joseph Mbui
 * Location: Mariakani, Kilifi, Kenya
 *
 * File:
 * security/Security.php
 *
 * Description:
 * Central security utilities for password handling,
 * secure sessions, token generation, input validation,
 * authorization helpers and security headers.
 *
 * ============================================================
 */

namespace Security;

use PDO;
use Throwable;

class Security
{
    /*
    |--------------------------------------------------------------------------
    | PASSWORD HASHING
    |--------------------------------------------------------------------------
    */

    public static function hashPassword(
        string $password
    ): string {
        return password_hash(
            $password,
            PASSWORD_DEFAULT
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD VERIFICATION
    |--------------------------------------------------------------------------
    */

    public static function verifyPassword(
        string $password,
        string $hash
    ): bool {
        return password_verify(
            $password,
            $hash
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD REHASH CHECK
    |--------------------------------------------------------------------------
    */

    public static function needsRehash(
        string $hash
    ): bool {
        return password_needs_rehash(
            $hash,
            PASSWORD_DEFAULT
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SECURE SESSION
    |--------------------------------------------------------------------------
    */

    public static function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
        );

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        session_start();

        /*
         * Regenerate the session ID once after the
         * session has been established.
         */
        if (!isset($_SESSION['__initiated'])) {
            session_regenerate_id(true);

            $_SESSION['__initiated'] = true;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SESSION REGENERATION
    |--------------------------------------------------------------------------
    */

    public static function regenerateSession(): void
    {
        self::startSecureSession();

        session_regenerate_id(true);
    }


    /*
    |--------------------------------------------------------------------------
    | RANDOM TOKEN
    |--------------------------------------------------------------------------
    */

    public static function randomToken(
        int $length = 32
    ): string {

        if ($length < 16) {
            $length = 16;
        }

        return bin2hex(
            random_bytes($length)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PUBLIC UUID
    |--------------------------------------------------------------------------
    |
    | Generates UUID v4 style identifiers for public-facing
    | records without exposing internal numeric IDs.
    |
    */

    public static function publicId(): string
    {
        $data = random_bytes(16);

        /*
         * UUID version 4.
         */
        $data[6] = chr(
            (ord($data[6]) & 0x0f) | 0x40
        );

        /*
         * UUID variant.
         */
        $data[8] = chr(
            (ord($data[8]) & 0x3f) | 0x80
        );

        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(
                bin2hex($data),
                4
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HTML ESCAPE
    |--------------------------------------------------------------------------
    */

    public static function e(
        ?string $value
    ): string {
        return htmlspecialchars(
            $value ?? '',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INPUT SANITIZATION
    |--------------------------------------------------------------------------
    */

    public static function sanitize(
        string $value
    ): string {
        return trim(
            strip_tags($value)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EMAIL VALIDATION
    |--------------------------------------------------------------------------
    */

    public static function isValidEmail(
        string $email
    ): bool {
        return filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        ) !== false;
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD VALIDATION
    |--------------------------------------------------------------------------
    */

    public static function validatePassword(
        string $password
    ): array {

        $errors = [];

        if (strlen($password) < 8) {
            $errors[] =
                'Password must be at least 8 characters.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] =
                'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] =
                'Password must contain at least one number.';
        }

        return $errors;
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN STATUS
    |--------------------------------------------------------------------------
    */

    public static function isLoggedIn(): bool
    {
        self::startSecureSession();

        return isset($_SESSION['user_id'])
            && is_numeric($_SESSION['user_id'])
            && (int) $_SESSION['user_id'] > 0;
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT USER ID
    |--------------------------------------------------------------------------
    */

    public static function userId(): ?int
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return (int) $_SESSION['user_id'];
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT SCHOOL ID
    |--------------------------------------------------------------------------
    */

    public static function schoolId(): ?int
    {
        self::startSecureSession();

        if (
            !isset($_SESSION['school_id']) ||
            !is_numeric($_SESSION['school_id'])
        ) {
            return null;
        }

        $schoolId = (int) $_SESSION['school_id'];

        return $schoolId > 0
            ? $schoolId
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT ROLE
    |--------------------------------------------------------------------------
    */

    public static function role(): ?string
    {
        self::startSecureSession();

        if (
            !isset($_SESSION['role']) ||
            !is_string($_SESSION['role'])
        ) {
            return null;
        }

        return $_SESSION['role'];
    }


    /*
    |--------------------------------------------------------------------------
    | ROLE CHECK
    |--------------------------------------------------------------------------
    */

    public static function hasRole(
        string|array $roles
    ): bool {

        $currentRole = self::role();

        if ($currentRole === null) {
            return false;
        }

        if (is_string($roles)) {
            return $currentRole === $roles;
        }

        return in_array(
            $currentRole,
            $roles,
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REQUIRE GUEST
    |--------------------------------------------------------------------------
    */

    public static function requireGuest(): void
    {
        self::startSecureSession();

        if (
            isset($_SESSION['user_id']) &&
            is_numeric($_SESSION['user_id']) &&
            (int) $_SESSION['user_id'] > 0
        ) {
            header(
                'Location: /dashboard.php',
                true,
                302
            );

            exit;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REQUIRE AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    public static function requireLogin(
        string $loginUrl = '/login.php'
    ): void {

        self::startSecureSession();

        if (!self::isLoggedIn()) {

            $_SESSION['intended_url'] =
                $_SERVER['REQUEST_URI'] ?? '/';

            header(
                'Location: ' . $loginUrl,
                true,
                302
            );

            exit;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REQUIRE ROLE
    |--------------------------------------------------------------------------
    */

    public static function requireRole(
        string|array $roles,
        string $redirectUrl = '/dashboard.php'
    ): void {

        self::requireLogin();

        if (!self::hasRole($roles)) {

            http_response_code(403);

            header(
                'Location: ' . $redirectUrl,
                true,
                302
            );

            exit;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SET AUTHENTICATION SESSION
    |--------------------------------------------------------------------------
    */

    public static function authenticate(
        int $userId,
        ?int $schoolId,
        string $role
    ): void {

        if ($userId <= 0) {
            throw new \InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        if ($role === '') {
            throw new \InvalidArgumentException(
                'User role is required.'
            );
        }

        self::startSecureSession();

        /*
         * Prevent session fixation after successful login.
         */
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        $_SESSION['school_id'] = $schoolId;
        $_SESSION['role'] = $role;
        $_SESSION['authenticated_at'] = time();
        $_SESSION['last_activity'] = time();
    }


    /*
    |--------------------------------------------------------------------------
    | SESSION ACTIVITY
    |--------------------------------------------------------------------------
    */

    public static function refreshActivity(): void
    {
        self::startSecureSession();

        $_SESSION['last_activity'] = time();
    }


    /*
    |--------------------------------------------------------------------------
    | SESSION TIMEOUT
    |--------------------------------------------------------------------------
    */

    public static function enforceSessionTimeout(
        int $timeout = 7200
    ): void {

        self::startSecureSession();

        if (!self::isLoggedIn()) {
            return;
        }

        $lastActivity =
            isset($_SESSION['last_activity'])
                ? (int) $_SESSION['last_activity']
                : time();

        if (
            $timeout > 0 &&
            (time() - $lastActivity) > $timeout
        ) {
            self::logout();

            header(
                'Location: /login.php?expired=1',
                true,
                302
            );

            exit;
        }

        self::refreshActivity();
    }


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public static function logout(): void
    {
        self::startSecureSession();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' =>
                        $params['samesite'] ?? 'Strict'
                ]
            );
        }

        session_destroy();
    }


    /*
    |--------------------------------------------------------------------------
    | SECURITY HEADERS
    |--------------------------------------------------------------------------
    */

    public static function securityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header(
            'X-Content-Type-Options: nosniff'
        );

        header(
            'X-Frame-Options: SAMEORIGIN'
        );

        header(
            'Referrer-Policy: strict-origin-when-cross-origin'
        );

        header(
            'Permissions-Policy: geolocation=(), microphone=(), camera=()'
        );

        header(
            'Content-Security-Policy: ' .
            "default-src 'self'; " .
            "script-src 'self'; " .
            "style-src 'self' 'unsafe-inline'; " .
            "img-src 'self' data:; " .
            "font-src 'self'; " .
            "connect-src 'self'; " .
            "frame-ancestors 'self';"
        );

        /*
         * Only send HSTS when HTTPS is active.
         */
        $secure = (
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
        );

        if ($secure) {
            header(
                'Strict-Transport-Security: ' .
                'max-age=31536000; includeSubDomains'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DATABASE HEALTH CHECK
    |--------------------------------------------------------------------------
    */

    public static function databaseHealthy(
        PDO $pdo
    ): bool {

        try {

            $stmt = $pdo->query(
                'SELECT 1'
            );

            return $stmt !== false;

        } catch (Throwable $e) {

            error_log(
                'ThinkPlus security database check failed: ' .
                $e->getMessage()
            );

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SAFE INTEGER
    |--------------------------------------------------------------------------
    */

    public static function intValue(
        mixed $value,
        int $default = 0
    ): int {

        $result = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if ($result === false) {
            return $default;
        }

        return (int) $result;
    }
}
