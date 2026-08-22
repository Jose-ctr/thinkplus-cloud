<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Login Handler
 * ============================================================
 *
 * Phase 3: Authentication & Security
 *
 * Founder: Joseph Mbui
 * Location: Mariakani, Kilifi, Kenya
 *
 * File:
 * app/Auth/Login.php
 *
 * Responsibilities:
 * - Authenticate users
 * - Verify CSRF tokens
 * - Verify passwords
 * - Protect against brute-force attempts
 * - Check account status
 * - Regenerate sessions after login
 * - Establish authenticated tenant context
 * - Record audit events
 * - Update last login time
 *
 * ============================================================
 */

namespace App\Auth;

use PDO;
use Throwable;
use Security\Security;
use Security\Csrf;
use Security\Audit;

class Login
{
    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    private PDO $pdo;

    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN ATTEMPT
    |--------------------------------------------------------------------------
    */

    public function attempt(
        string $email,
        string $password,
        ?string $csrfToken = null
    ): array {

        /*
         * Start secure session.
         */
        Security::startSecureSession();


        /*
         * CSRF protection.
         *
         * If a token is supplied, it must be valid.
         */
        if (
            $csrfToken === null ||
            !Csrf::verify($csrfToken)
        ) {

            try {
                Audit::log(
                    $this->pdo,
                    'csrf_failure',
                    'login',
                    null,
                    null,
                    null,
                    'CSRF verification failed during login'
                );
            } catch (Throwable $e) {
                error_log(
                    'ThinkPlus CSRF audit error: ' .
                    $e->getMessage()
                );
            }

            return [
                'success' => false,
                'message' =>
                    'Security verification failed. ' .
                    'Please refresh the page and try again.'
            ];
        }


        /*
         * Normalize email.
         */
        $email = strtolower(
            trim(
                Security::sanitize($email)
            )
        );


        /*
         * Validate required fields.
         */
        if ($email === '' || $password === '') {

            return [
                'success' => false,
                'message' =>
                    'Email and password are required.'
            ];
        }


        /*
         * Validate email.
         */
        if (!Security::isValidEmail($email)) {

            return [
                'success' => false,
                'message' =>
                    'Please enter a valid email address.'
            ];
        }


        try {

            /*
            |--------------------------------------------------------------------------
            | CHECK LOCKOUT
            |--------------------------------------------------------------------------
            */

            if ($this->isLockedOut($email)) {

                return [
                    'success' => false,
                    'message' =>
                        'Too many failed login attempts. ' .
                        'Please try again later.'
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | FIND USER
            |--------------------------------------------------------------------------
            */

            $stmt = $this->pdo->prepare(
                'SELECT
                    id,
                    public_id,
                    school_id,
                    email,
                    password_hash,
                    role,
                    status,
                    failed_attempts,
                    locked_until,
                    last_login_at,
                    created_at
                 FROM users
                 WHERE email = ?
                 LIMIT 1'
            );

            $stmt->execute([
                $email
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);


            /*
            |--------------------------------------------------------------------------
            | USER NOT FOUND
            |--------------------------------------------------------------------------
            |
            | Do not reveal whether an email exists.
            |
            */

            if (!$user) {

                $this->recordFailedAttempt($email);

                try {
                    Audit::failedLogin(
                        $this->pdo,
                        $email
                    );
                } catch (Throwable $e) {
                    error_log(
                        'ThinkPlus failed-login audit error: ' .
                        $e->getMessage()
                    );
                }

                return [
                    'success' => false,
                    'message' =>
                        'Invalid email or password.'
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | ACCOUNT STATUS
            |--------------------------------------------------------------------------
            */

            $status = strtolower(
                (string) ($user['status'] ?? '')
            );

            if ($status !== 'active') {

                try {
                    Audit::failedLogin(
                        $this->pdo,
                        $email
                    );
                } catch (Throwable $e) {
                    error_log(
                        'ThinkPlus account-status audit error: ' .
                        $e->getMessage()
                    );
                }

                return [
                    'success' => false,
                    'message' =>
                        'Your account is not active. ' .
                        'Please contact your administrator.'
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | CHECK ACCOUNT LOCK
            |--------------------------------------------------------------------------
            */

            if (
                !empty($user['locked_until']) &&
                strtotime(
                    (string) $user['locked_until']
                ) > time()
            ) {

                return [
                    'success' => false,
                    'message' =>
                        'Your account is temporarily locked. ' .
                        'Please try again later.'
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | PASSWORD VERIFICATION
            |--------------------------------------------------------------------------
            */

            $passwordHash = (string)
                ($user['password_hash'] ?? '');

            if (
                $passwordHash === '' ||
                !Security::verifyPassword(
                    $password,
                    $passwordHash
                )
            ) {

                $this->recordFailedAttempt($email);

                try {
                    Audit::failedLogin(
                        $this->pdo,
                        $email
                    );
                } catch (Throwable $e) {
                    error_log(
                        'ThinkPlus failed-login audit error: ' .
                        $e->getMessage()
                    );
                }

                return [
                    'success' => false,
                    'message' =>
                        'Invalid email or password.'
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | PASSWORD REHASH
            |--------------------------------------------------------------------------
            |
            | If PHP recommends a stronger password hash,
            | upgrade the stored hash after successful login.
            |
            */

            if (
                Security::needsRehash(
                    $passwordHash
                )
            ) {

                $newHash = Security::hashPassword(
                    $password
                );

                $rehash = $this->pdo->prepare(
                    'UPDATE users
                     SET password_hash = ?
                     WHERE id = ?
                     LIMIT 1'
                );

                $rehash->execute([
                    $newHash,
                    (int) $user['id']
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | SESSION REGENERATION
            |--------------------------------------------------------------------------
            |
            | Prevent session fixation after authentication.
            |
            */

            session_regenerate_id(true);


            /*
            |--------------------------------------------------------------------------
            | ESTABLISH AUTHENTICATED SESSION
            |--------------------------------------------------------------------------
            */

            $_SESSION['user_id'] = (int)
                $user['id'];

            $_SESSION['public_id'] =
                (string) $user['public_id'];

            $_SESSION['email'] =
                (string) $user['email'];

            $_SESSION['role'] =
                (string) $user['role'];

            /*
             * Store school ID only when the account
             * belongs to a school.
             */
            if (
                $user['school_id'] !== null &&
                $user['school_id'] !== ''
            ) {

                $_SESSION['school_id'] = (int)
                    $user['school_id'];

                /*
                 * Tenant.php can use this as the active
                 * tenant context.
                 */
                $_SESSION['_tenant_school_id'] =
                    (int) $user['school_id'];

            } else {

                unset(
                    $_SESSION['school_id'],
                    $_SESSION['_tenant_school_id']
                );
            }


            /*
            |--------------------------------------------------------------------------
            | LOGIN TIMESTAMP
            |--------------------------------------------------------------------------
            */

            $_SESSION['authenticated_at'] = time();


            /*
            |--------------------------------------------------------------------------
            | CLEAR FAILED LOGIN ATTEMPTS
            |--------------------------------------------------------------------------
            */

            $clear = $this->pdo->prepare(
                'UPDATE users
                 SET
                    failed_attempts = 0,
                    locked_until = NULL,
                    last_login_at = NOW()
                 WHERE id = ?
                 LIMIT 1'
            );

            $clear->execute([
                (int) $user['id']
            ]);


            /*
            |--------------------------------------------------------------------------
            | ROTATE CSRF TOKEN
            |--------------------------------------------------------------------------
            |
            | A fresh CSRF token is issued after authentication.
            |
            */

            Csrf::rotate();


            /*
            |--------------------------------------------------------------------------
            | AUDIT LOGIN
            |--------------------------------------------------------------------------
            */

            try {
                Audit::login(
                    $this->pdo,
                    (int) $user['id']
                );
            } catch (Throwable $e) {
                error_log(
                    'ThinkPlus login audit error: ' .
                    $e->getMessage()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | REDIRECT
            |--------------------------------------------------------------------------
            */

            $redirect = $this->getRedirectUrl(
                (string) $user['role']
            );


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return [
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => $redirect,
                'user' => [
                    'id' => (int) $user['id'],
                    'public_id' =>
                        (string) $user['public_id'],
                    'school_id' =>
                        $user['school_id'] !== null
                            ? (int) $user['school_id']
                            : null,
                    'email' =>
                        (string) $user['email'],
                    'role' =>
                        (string) $user['role'],
                    'status' =>
                        (string) $user['status']
                ]
            ];

        } catch (Throwable $e) {

            /*
             * Never expose database errors to users.
             */
            error_log(
                'ThinkPlus Login error: ' .
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' =>
                    'Login failed. Please try again.'
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK LOCKOUT
    |--------------------------------------------------------------------------
    */

    private function isLockedOut(
        string $email
    ): bool {

        try {

            $stmt = $this->pdo->prepare(
                'SELECT locked_until
                 FROM users
                 WHERE email = ?
                 LIMIT 1'
            );

            $stmt->execute([
                $email
            ]);

            $lockedUntil = $stmt->fetchColumn();

            if (
                $lockedUntil === false ||
                $lockedUntil === null ||
                $lockedUntil === ''
            ) {
                return false;
            }

            $timestamp = strtotime(
                (string) $lockedUntil
            );

            if ($timestamp === false) {
                return false;
            }

            return $timestamp > time();

        } catch (Throwable $e) {

            error_log(
                'ThinkPlus lockout check error: ' .
                $e->getMessage()
            );

            /*
             * Fail closed for authentication safety.
             */
            return true;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RECORD FAILED ATTEMPT
    |--------------------------------------------------------------------------
    */

    private function recordFailedAttempt(
        string $email
    ): void {

        try {

            /*
             * Only update an existing account.
             *
             * Unknown email addresses are not inserted
             * into the users table.
             */

            $stmt = $this->pdo->prepare(
                'UPDATE users
                 SET
                    failed_attempts =
                        COALESCE(failed_attempts, 0) + 1,
                    locked_until =
                        CASE
                            WHEN COALESCE(failed_attempts, 0) + 1 >= ?
                            THEN DATE_ADD(
                                NOW(),
                                INTERVAL ? MINUTE
                            )
                            ELSE locked_until
                        END
                 WHERE email = ?
                 LIMIT 1'
            );

            $stmt->execute([
                self::MAX_ATTEMPTS,
                self::LOCKOUT_MINUTES,
                $email
            ]);

        } catch (Throwable $e) {

            error_log(
                'ThinkPlus failed attempt error: ' .
                $e->getMessage()
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECT BY ROLE
    |--------------------------------------------------------------------------
    */

    private function getRedirectUrl(
        string $role
    ): string {

        return match ($role) {

            'super_admin' =>
                '/super-admin/dashboard.php',

            'school_admin' =>
                '/dashboard.php',

            'admin' =>
                '/dashboard.php',

            'teacher' =>
                '/teacher/dashboard.php',

            'accountant' =>
                '/accountant/dashboard.php',

            'parent' =>
                '/parent/dashboard.php',

            'student' =>
                '/student/dashboard.php',

            default =>
                '/dashboard.php'
        };
    }


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(): void
    {
        Security::startSecureSession();

        $userId = $_SESSION['user_id'] ?? null;


        /*
         * Record logout before destroying session.
         */
        if (
            is_numeric($userId) &&
            (int) $userId > 0
        ) {

            try {

                Audit::logout(
                    $this->pdo,
                    (int) $userId
                );

            } catch (Throwable $e) {

                error_log(
                    'ThinkPlus logout audit error: ' .
                    $e->getMessage()
                );
            }
        }


        /*
         * Remove authentication data.
         */
        $_SESSION = [];


        /*
         * Remove session cookie.
         */
        if (
            ini_get('session.use_cookies')
        ) {

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
                        $params['samesite'] ?? 
'Lax'
                ]
            );
        }


        /*
         * Destroy server-side session.
         */
        if (
            session_status() ===
            PHP_SESSION_ACTIVE
        ) {
            session_destroy();
        }
    }
}
