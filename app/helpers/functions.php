<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Shared Helper Functions
 * ============================================================
 *
 * File:
 * app/helpers/functions.php
 *
 * Purpose:
 * - Shared application helpers
 * - Authentication helpers
 * - Input helpers
 * - Flash messages
 * - HTML escaping
 * - CSRF wrappers
 * - Kenyan phone normalization
 * - Validation
 * - Money formatting
 * - Database transactions
 * - Logout
 *
 * Security responsibilities remain in:
 *
 * security/Security.php
 * security/Csrf.php
 * security/Audit.php
 * security/Tenant.php
 *
 * Database connection:
 *
 * app/config/db.php
 *
 * ============================================================
 */


/*
|--------------------------------------------------------------------------
| LOAD DATABASE
|--------------------------------------------------------------------------
|
| app/helpers/functions.php
| app/config/db.php
|
*/

require_once dirname(__DIR__) . '/config/db.php';


/*
|--------------------------------------------------------------------------
| LOAD SECURITY CLASSES
|--------------------------------------------------------------------------
|
| These paths assume:
|
| security/
|   Security.php
|   Csrf.php
|   Audit.php
|   Tenant.php
|
*/

require_once dirname(__DIR__, 2) . '/security/Security.php';
require_once dirname(__DIR__, 2) . '/security/Csrf.php';


/*
|--------------------------------------------------------------------------
| IMPORT SECURITY CLASSES
|--------------------------------------------------------------------------
*/

use Security\Security;
use Security\Csrf;


/*
|--------------------------------------------------------------------------
| SECURE SESSION
|--------------------------------------------------------------------------
|
| Let Security.php control session configuration.
|
*/

Security::startSecureSession();


/*
|--------------------------------------------------------------------------
| ESCAPE HTML OUTPUT
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {

    function e(?string $value): string
    {
        return htmlspecialchars(
            $value ?? '',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}


/*
|--------------------------------------------------------------------------
| SANITIZE INPUT
|--------------------------------------------------------------------------
*/

if (!function_exists('sanitize')) {

    function sanitize(mixed $data): string
    {
        if (is_array($data)) {
            return '';
        }

        return trim(
            strip_tags(
                (string) $data
            )
        );
    }
}


/*
|--------------------------------------------------------------------------
| POST INPUT
|--------------------------------------------------------------------------
*/

if (!function_exists('post')) {

    function post(
        string $key,
        string $default = ''
    ): string {

        if (!isset($_POST[$key])) {
            return $default;
        }

        return sanitize($_POST[$key]);
    }
}


/*
|--------------------------------------------------------------------------
| GET INPUT
|--------------------------------------------------------------------------
*/

if (!function_exists('get')) {

    function get(
        string $key,
        string $default = ''
    ): string {

        if (!isset($_GET[$key])) {
            return $default;
        }

        return sanitize($_GET[$key]);
    }
}


/*
|--------------------------------------------------------------------------
| LOGIN STATUS
|--------------------------------------------------------------------------
*/

if (!function_exists('isLoggedIn')) {

    function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id'])
            && is_numeric($_SESSION['user_id'])
            && (int) $_SESSION['user_id'] > 0;
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT USER ID
|--------------------------------------------------------------------------
*/

if (!function_exists('currentUserId')) {

    function currentUserId(): ?int
    {
        if (!isLoggedIn()) {
            return null;
        }

        return (int) $_SESSION['user_id'];
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT USER
|--------------------------------------------------------------------------
*/

if (!function_exists('currentUser')) {

    function currentUser(): ?array
    {
        global $pdo;

        $userId = currentUserId();

        if ($userId === null) {
            return null;
        }

        if (
            !isset($pdo) ||
            !$pdo instanceof PDO
        ) {
            return null;
        }

        try {

            $stmt = $pdo->prepare(
                'SELECT
                    id,
                    public_id,
                    school_id,
                    name,
                    email,
                    phone,
                    role,
                    status,
                    last_login_at,
                    created_at
                 FROM users
                 WHERE id = ?
                 AND status = ?
                 LIMIT 1'
            );

            $stmt->execute([
                $userId,
                'active'
            ]);

            $user = $stmt->fetch(
                PDO::FETCH_ASSOC
            );

            return $user ?: null;

        } catch (Throwable $e) {

            error_log(
                'ThinkPlus currentUser error: ' .
                $e->getMessage()
            );

            return null;
        }
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT ROLE
|--------------------------------------------------------------------------
*/

if (!function_exists('currentRole')) {

    function currentRole(): ?string
    {
        $user = currentUser();

        if (!$user) {
            return null;
        }

        return isset($user['role'])
            ? (string) $user['role']
            : null;
    }
}


/*
|--------------------------------------------------------------------------
| ROLE CHECK
|--------------------------------------------------------------------------
*/

if (!function_exists('hasRole')) {

    function hasRole(
        string|array $roles
    ): bool {

        $role = currentRole();

        if ($role === null) {
            return false;
        }

        if (is_string($roles)) {
            return $role === $roles;
        }

        return in_array(
            $role,
            $roles,
            true
        );
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT SCHOOL ID
|--------------------------------------------------------------------------
*/

if (!function_exists('currentSchoolId')) {

    function currentSchoolId(): ?int
    {
        $user = currentUser();

        if (!$user) {
            return null;
        }

        if (
            !isset($user['school_id']) ||
            $user['school_id'] === null
        ) {
            return null;
        }

        $schoolId = filter_var(
            $user['school_id'],
            FILTER_VALIDATE_INT
        );

        if ($schoolId === false || $schoolId <= 0) {
            return null;
        }

        return (int) $schoolId;
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT SCHOOL ID FROM SESSION
|--------------------------------------------------------------------------
|
| Useful when the authenticated tenant has already
| been established.
|
*/

if (!function_exists('sessionSchoolId')) {

    function sessionSchoolId(): ?int
    {
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
}


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect')) {

    function redirect(string $url): never
    {
        header(
            'Location: ' . $url,
            true,
            302
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE LOGIN
|--------------------------------------------------------------------------
*/

if (!function_exists('requireLogin')) {

    function requireLogin(
        string $loginUrl = '/login.php'
    ): void {

        if (!isLoggedIn()) {

            setFlash(
                'Please log in to continue.',
                'warning'
            );

            redirect($loginUrl);
        }
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE ROLE
|--------------------------------------------------------------------------
*/

if (!function_exists('requireRole')) {

    function requireRole(
        string|array $roles,
        string $redirectUrl = '/dashboard.php'
    ): void {

        requireLogin();

        if (!hasRole($roles)) {

            setFlash(
                'You do not have permission to access this page.',
                'danger'
            );

            redirect($redirectUrl);
        }
    }
}


/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

if (!function_exists('setFlash')) {

    function setFlash(
        string $message,
        string $type = 'success'
    ): void {

        $allowedTypes = [
            'success',
            'danger',
            'warning',
            'info'
        ];

        if (!in_array(
            $type,
            $allowedTypes,
            true
        )) {
            $type = 'info';
        }

        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
}


/*
|--------------------------------------------------------------------------
| SHOW FLASH MESSAGE
|--------------------------------------------------------------------------
*/

if (!function_exists('showMessage')) {

    function showMessage(): void
    {
        if (!isset($_SESSION['message'])) {
            return;
        }

        $message = e(
            (string) $_SESSION['message']
        );

        $type = $_SESSION['message_type']
            ?? 'success';

        $allowedTypes = [
            'success',
            'danger',
            'warning',
            'info'
        ];

        if (!in_array(
            $type,
            $allowedTypes,
            true
        )) {
            $type = 'info';
        }

        echo
            '<div class="alert alert-' .
            e($type) .
            '" role="alert">' .
            $message .
            '</div>';

        unset(
            $_SESSION['message'],
            $_SESSION['message_type']
        );
    }
}


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
|
| IMPORTANT:
| This delegates to Security\Csrf.
|
| Do NOT create another independent CSRF token here.
|
*/

if (!function_exists('csrfToken')) {

    function csrfToken(): string
    {
        return Csrf::getToken();
    }
}


/*
|--------------------------------------------------------------------------
| CSRF FORM FIELD
|--------------------------------------------------------------------------
*/

if (!function_exists('csrfField')) {

    function csrfField(): string
    {
        return Csrf::field();
    }
}


/*
|--------------------------------------------------------------------------
| VERIFY CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('verifyCsrfToken')) {

    function verifyCsrfToken(
        ?string $token
    ): bool {

        return Csrf::verify($token);
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE CSRF
|--------------------------------------------------------------------------
*/

if (!function_exists('requireCsrf')) {

    function requireCsrf(): void
    {
        Csrf::verifyRequest();
    }
}


/*
|--------------------------------------------------------------------------
| PASSWORD HASHING
|--------------------------------------------------------------------------
|
| Delegate to Security.php.
|
*/

if (!function_exists('hashPassword')) {

    function hashPassword(
        string $password
    ): string {

        return Security::hashPassword(
            $password
        );
    }
}


/*
|--------------------------------------------------------------------------
| PASSWORD VERIFICATION
|--------------------------------------------------------------------------
*/

if (!function_exists('verifyPassword')) {

    function verifyPassword(
        string $password,
        string $hash
    ): bool {

        return Security::verifyPassword(
            $password,
            $hash
        );
    }
}


/*
|--------------------------------------------------------------------------
| PASSWORD REHASH CHECK
|--------------------------------------------------------------------------
*/

if (!function_exists('passwordNeedsRehash')) {

    function passwordNeedsRehash(
        string $hash
    ): bool {

        return Security::needsRehash(
            $hash
        );
    }
}


/*
|--------------------------------------------------------------------------
| EMAIL VALIDATION
|--------------------------------------------------------------------------
*/

if (!function_exists('validEmail')) {

    function validEmail(
        string $email
    ): bool {

        return Security::isValidEmail(
            $email
        );
    }
}


/*
|--------------------------------------------------------------------------
| KENYAN PHONE NORMALIZATION
|--------------------------------------------------------------------------
*/

if (!function_exists('normalizeKenyanPhone')) {

    function normalizeKenyanPhone(
        string $phone
    ): string {

        $phone = preg_replace(
            '/[\s\-()]/',
            '',
            trim($phone)
        );

        if ($phone === null) {
            return '';
        }

        /*
         * +254712345678
         * +254112345678
         */
        if (preg_match(
            '/^\+254(7\d{8}|1\d{8})$/',
            $phone
        )) {
            return $phone;
        }

        /*
         * 254712345678
         * 254112345678
         */
        if (preg_match(
            '/^254(7\d{8}|1\d{8})$/',
            $phone
        )) {
            return '+' . $phone;
        }

        /*
         * 0712345678
         * 0112345678
         */
        if (preg_match(
            '/^0(7\d{8}|1\d{8})$/',
            $phone
        )) {
            return '+254' . substr(
                $phone,
                1
            );
        }

        return '';
    }
}


/*
|--------------------------------------------------------------------------
| INTEGER VALIDATION
|--------------------------------------------------------------------------
*/

if (!function_exists('intValue')) {

    function intValue(
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


/*
|--------------------------------------------------------------------------
| MONEY FORMAT
|--------------------------------------------------------------------------
*/

if (!function_exists('money')) {

    function money(
        float|int|string $amount
    ): string {

        return number_format(
            (float) $amount,
            2,
            '.',
            ','
        );
    }
}


/*
|--------------------------------------------------------------------------
| DATABASE TRANSACTION
|--------------------------------------------------------------------------
*/

if (!function_exists('beginTransaction')) {

    function beginTransaction(): void
    {
        global $pdo;

        if (
            isset($pdo) &&
            $pdo instanceof PDO &&
            !$pdo->inTransaction()
        ) {
            $pdo->beginTransaction();
        }
    }
}


if (!function_exists('commitTransaction')) {

    function commitTransaction(): void
    {
        global $pdo;

        if (
            isset($pdo) &&
            $pdo instanceof PDO &&
            $pdo->inTransaction()
        ) {
            $pdo->commit();
        }
    }
}


if (!function_exists('rollbackTransaction')) {

    function rollbackTransaction(): void
    {
        global $pdo;

        if (
            isset($pdo) &&
            $pdo instanceof PDO &&
            $pdo->inTransaction()
        ) {
            $pdo->rollBack();
        }
    }
}


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

if (!function_exists('logoutUser')) {

    function logoutUser(): void
    {
        Security::startSecureSession();

        /*
         * Clear all session data.
         */
        $_SESSION = [];

        /*
         * Remove session cookie.
         */
        if (ini_get('session.use_cookies')) {

            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' =>
                        time() - 42000,

                    'path' =>
                        $params['path'],

                    'domain' =>
                        $params['domain'],

                    'secure' =>
                        $params['secure'],

                    'httponly' =>
                        $params['httponly'],

                    'samesite' =>
                        $params['samesite'] ?? 'Lax'
                ]
            );
        }

        /*
         * Destroy session.
         */
        if (
            session_status() ===
            PHP_SESSION_ACTIVE
        ) {
            session_destroy();
        }
    }
}


/*
|--------------------------------------------------------------------------
| DATABASE HEALTH CHECK
|--------------------------------------------------------------------------
*/

if (!function_exists('databaseIsHealthy')) {

    function databaseIsHealthy(): bool
    {
        global $pdo;

        if (
            !isset($pdo) ||
            !$pdo instanceof PDO
        ) {
            return false;
        }

        try {

            $stmt = $pdo->query(
                'SELECT 1'
            );

            return $stmt !== false;

        } catch (Throwable $e) {

            error_log(
                'ThinkPlus database health check failed: ' .
                $e->getMessage()
            );

            return false;
        }
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT USER HAS SCHOOL
|--------------------------------------------------------------------------
*/

if (!function_exists('hasSchool')) {

    function hasSchool(): bool
    {
        return currentSchoolId() !== null;
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE SCHOOL
|--------------------------------------------------------------------------
|
| Useful for pages that must belong to a school tenant.
|
*/

if (!function_exists('requireSchool')) {

    function requireSchool(
        string $redirectUrl = '/dashboard.php'
    ): void {

        requireLogin();

        if (currentSchoolId() === null) {

            setFlash(
                'No school tenant is associated with this account.',
                'danger'
            );

            redirect($redirectUrl);
        }
    }
}
