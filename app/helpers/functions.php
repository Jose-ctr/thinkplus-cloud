<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Shared Application Helpers
 * ============================================================
 *
 * Phase 3: Authentication & Security
 *
 * File:
 * app/helpers/functions.php
 *
 * Purpose:
 * Central collection of small, reusable application helpers.
 *
 * Security-sensitive operations remain delegated to:
 *
 * security/Security.php
 * security/Csrf.php
 * security/Tenant.php
 * security/Audit.php
 *
 * PHP: 8.2+
 *
 * ============================================================
 */

use PDO;
use Throwable;
use Security\Security;
use Security\Csrf;
use Security\Tenant;


/*
|--------------------------------------------------------------------------
| OUTPUT / HTML HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {

    /**
     * Escape a value for safe HTML output.
     */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!is_scalar($value)) {
            return '';
        }

        return Security::e((string) $value);
    }
}


if (!function_exists('old')) {

    /**
     * Retrieve a previously submitted form value.
     */
    function old(
        string $key,
        mixed $default = ''
    ): mixed {

        Security::startSecureSession();

        $old = $_SESSION['_old'] ?? [];

        if (!is_array($old)) {
            return $default;
        }

        return array_key_exists($key, $old)
            ? $old[$key]
            : $default;
    }
}


if (!function_exists('set_old')) {

    /**
     * Store safe form values for redisplay.
     *
     * Sensitive password fields are always removed.
     *
     * @param array<string,mixed> $data
     */
    function set_old(array $data): void
    {
        Security::startSecureSession();

        $sensitive = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'new_password_confirmation',
            'password_confirmation',
            'token',
            'csrf_token',
            '_csrf',
        ];

        foreach ($sensitive as $field) {
            unset($data[$field]);
        }

        $_SESSION['_old'] = $data;
    }
}


if (!function_exists('clear_old')) {

    /**
     * Clear stored form values.
     */
    function clear_old(): void
    {
        Security::startSecureSession();

        unset($_SESSION['_old']);
    }
}


/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

if (!function_exists('flash')) {

    /**
     * Store a flash message.
     */
    function flash(
        string $key,
        string $message
    ): void {

        Security::startSecureSession();

        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }

        $_SESSION['_flash'][$key] = $message;
    }
}


if (!function_exists('get_flash')) {

    /**
     * Retrieve and remove a flash message.
     */
    function get_flash(
        string $key,
        mixed $default = null
    ): mixed {

        Security::startSecureSession();

        $flash = $_SESSION['_flash'] ?? [];

        if (
            !is_array($flash) ||
            !array_key_exists($key, $flash)
        ) {
            return $default;
        }

        $message = $flash[$key];

        unset($_SESSION['_flash'][$key]);

        return $message;
    }
}


if (!function_exists('has_flash')) {

    /**
     * Determine whether a flash message exists.
     */
    function has_flash(string $key): bool
    {
        Security::startSecureSession();

        return isset($_SESSION['_flash'])
            && is_array($_SESSION['_flash'])
            && array_key_exists(
                $key,
                $_SESSION['_flash']
            );
    }
}


/*
|--------------------------------------------------------------------------
| URL HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('base_url')) {

    /**
     * Generate the application base URL.
     *
     * APP_URL should preferably be configured in .env.
     */
    function base_url(string $path = ''): string
    {
        $configured = getenv('APP_URL');

        if (
            $configured !== false &&
            trim($configured) !== ''
        ) {
            $base = rtrim(
                trim($configured),
                '/'
            );
        } else {

            $https =
                !empty($_SERVER['HTTPS']) &&
                strtolower(
                    (string) $_SERVER['HTTPS']
                ) !== 'off';

            $scheme = $https
                ? 'https'
                : 'http';

            $host = $_SERVER['HTTP_HOST']
                ?? 'localhost';

            /*
             * Never allow a malformed host header to
             * become part of generated application URLs.
             */
            $host = preg_replace(
                '/[^a-zA-Z0-9.\-:\[\]]/',
                '',
                (string) $host
            );

            if ($host === '') {
                $host = 'localhost';
            }

            $base =
                $scheme .
                '://' .
                $host;
        }

        $path = ltrim(
            trim($path),
            '/'
        );

        if ($path === '') {
            return $base . '/';
        }

        return $base . '/' . $path;
    }
}


if (!function_exists('url')) {

    /**
     * Generate an application URL.
     */
    function url(string $path = ''): string
    {
        return base_url($path);
    }
}


if (!function_exists('asset')) {

    /**
     * Generate an application asset URL.
     */
    function asset(string $path): string
    {
        return base_url(
            'assets/' . ltrim($path, '/')
        );
    }
}


if (!function_exists('is_safe_local_url')) {

    /**
     * Determine whether a URL is safe for a local redirect.
     *
     * Allows:
     *   /dashboard.php
     *   /students.php?id=1
     *
     * Rejects:
     *   //evil.example
     *   javascript:
     *   data:
     *   external hosts
     */
    function is_safe_local_url(
        string $url
    ): bool {

        $url = trim($url);

        if ($url === '') {
            return false;
        }

        /*
         * Reject control characters.
         */
        if (
            preg_match(
                '/[\x00-\x1F\x7F]/',
                $url
            )
        ) {
            return false;
        }

        /*
         * Absolute-path local URL.
         */
        if (str_starts_with($url, '/')) {
            return !str_starts_with($url, '//');
        }

        /*
         * Reject scheme-like URLs.
         */
        if (
            preg_match(
                '/^[a-z][a-z0-9+\-.]*:/i',
                $url
            )
        ) {
            return false;
        }

        /*
         * Relative paths are permitted.
         */
        return true;
    }
}


/*
|--------------------------------------------------------------------------
| REDIRECT HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect')) {

    /**
     * Perform a safe local redirect.
     *
     * @return never
     */
    function redirect(
        string $path,
        int $status = 302
    ): never {

        if (
            $status < 300 ||
            $status > 399
        ) {
            $status = 302;
        }

        if (!is_safe_local_url($path)) {
            $path = '/';
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        header(
            'Location: ' . $path,
            true,
            $status
        );

        exit;
    }
}


if (!function_exists('back')) {

    /**
     * Return to a safe previous page.
     *
     * @return never
     */
    function back(
        string $fallback = '/'
    ): never {

        $referer =
            $_SERVER['HTTP_REFERER'] ?? '';

        if (
            is_string($referer) &&
            is_safe_local_url($referer)
        ) {
            redirect($referer);
        }

        redirect($fallback);
    }
}


/*
|--------------------------------------------------------------------------
| REQUEST HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('request_method')) {

    /**
     * Return the current HTTP method.
     */
    function request_method(): string
    {
        return strtoupper(
            (string) (
                $_SERVER['REQUEST_METHOD']
                ?? 'GET'
            )
        );
    }
}


if (!function_exists('is_get')) {

    function is_get(): bool
    {
        return request_method() === 'GET';
    }
}


if (!function_exists('is_post')) {

    function is_post(): bool
    {
        return request_method() === 'POST';
    }
}


if (!function_exists('is_put')) {

    function is_put(): bool
    {
        return request_method() === 'PUT';
    }
}


if (!function_exists('is_patch')) {

    function is_patch(): bool
    {
        return request_method() === 'PATCH';
    }
}


if (!function_exists('is_delete')) {

    function is_delete(): bool
    {
        return request_method() === 'DELETE';
    }
}


if (!function_exists('is_ajax')) {

    /**
     * Detect a conventional AJAX request.
     */
    function is_ajax(): bool
    {
        return strtolower(
            (string) (
                $_SERVER['HTTP_X_REQUESTED_WITH']
                ?? ''
            )
        ) === 'xmlhttprequest';
    }
}


/*
|--------------------------------------------------------------------------
| INPUT HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('input')) {

    /**
     * Retrieve POST input first, then GET input.
     *
     * For security-sensitive operations, prefer
     * input_post() so the source is explicit.
     */
    function input(
        string $key,
        mixed $default = null
    ): mixed {

        if (
            array_key_exists(
                $key,
                $_POST
            )
        ) {
            return $_POST[$key];
        }

        if (
            array_key_exists(
                $key,
                $_GET
            )
        ) {
            return $_GET[$key];
        }

        return $default;
    }
}


if (!function_exists('input_post')) {

    /**
     * Retrieve POST input only.
     */
    function input_post(
        string $key,
        mixed $default = null
    ): mixed {

        return array_key_exists(
            $key,
            $_POST
        )
            ? $_POST[$key]
            : $default;
    }
}


if (!function_exists('input_get')) {

    /**
     * Retrieve GET input only.
     */
    function input_get(
        string $key,
        mixed $default = null
    ): mixed {

        return array_key_exists(
            $key,
            $_GET
        )
            ? $_GET[$key]
            : $default;
    }
}


if (!function_exists('input_string')) {

    /**
     * Retrieve a request value as a string.
     */
    function input_string(
        string $key,
        string $default = ''
    ): string {

        $value = input(
            $key,
            $default
        );

        return is_string($value)
            ? trim($value)
            : $default;
    }
}


if (!function_exists('input_post_string')) {

    /**
     * Retrieve POST input as a string.
     */
    function input_post_string(
        string $key,
        string $default = ''
    ): string {

        $value = input_post(
            $key,
            $default
        );

        return is_string($value)
            ? trim($value)
            : $default;
    }
}


if (!function_exists('input_get_string')) {

    /**
     * Retrieve GET input as a string.
     */
    function input_get_string(
        string $key,
        string $default = ''
    ): string {

        $value = input_get(
            $key,
            $default
        );

        return is_string($value)
            ? trim($value)
            : $default;
    }
}


if (!function_exists('input_int')) {

    /**
     * Retrieve an integer input.
     */
    function input_int(
        string $key,
        int $default = 0
    ): int {

        return Security::intValue(
            input($key),
            $default
        );
    }
}


if (!function_exists('input_post_int')) {

    /**
     * Retrieve a POST integer.
     */
    function input_post_int(
        string $key,
        int $default = 0
    ): int {

        return Security::intValue(
            input_post($key),
            $default
        );
    }
}


if (!function_exists('input_get_int')) {

    /**
     * Retrieve a GET integer.
     */
    function input_get_int(
        string $key,
        int $default = 0
    ): int {

        return Security::intValue(
            input_get($key),
            $default
        );
    }
}


/*
|--------------------------------------------------------------------------
| AUTHENTICATION HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('auth_check')) {

    /**
     * Determine whether a user is authenticated.
     */
    function auth_check(): bool
    {
        return Security::isLoggedIn();
    }
}


if (!function_exists('auth_id')) {

    /**
     * Return authenticated internal user ID.
     */
    function auth_id(): ?int
    {
        return Security::userId();
    }
}


if (!function_exists('auth_role')) {

    /**
     * Return authenticated user role.
     */
    function auth_role(): ?string
    {
        return Security::role();
    }
}


if (!function_exists('auth_school_id')) {

    /**
     * Return the authenticated user's school ID.
     *
     * Security::schoolId() reads the authenticated
     * session context.
     */
    function auth_school_id(): ?int
    {
        return Security::schoolId();
    }
}


if (!function_exists('has_role')) {

    /**
     * Determine whether the current user has a role.
     *
     * @param string|array<int,string> $roles
     */
    function has_role(
        string|array $roles
    ): bool {

        return Security::hasRole($roles);
    }
}


if (!function_exists('require_login')) {

    /**
     * Require authentication.
     */
    function require_login(
        string $loginUrl = '/login.php'
    ): void {

        Security::requireLogin(
            $loginUrl
        );
    }
}


if (!function_exists('require_role')) {

    /**
     * Require one or more roles.
     *
     * @param string|array<int,string> $roles
     */
    function require_role(
        string|array $roles,
        string $redirectUrl = '/dashboard.php'
    ): void {

        Security::requireRole(
            $roles,
            $redirectUrl
        );
    }
}


if (!function_exists('require_guest')) {

    /**
     * Require an unauthenticated visitor.
     */
    function require_guest(): void
    {
        Security::requireGuest();
    }
}


if (!function_exists('session_timeout')) {

    /**
     * Enforce authentication session inactivity timeout.
     */
    function session_timeout(
        int $seconds = 7200
    ): void {

        Security::enforceSessionTimeout(
            $seconds
        );
    }
}


/*
|--------------------------------------------------------------------------
| CSRF HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('csrf_token')) {

    /**
     * Return the current CSRF token.
     */
    function csrf_token(): string
    {
        return Csrf::getToken();
    }
}


if (!function_exists('csrf_field')) {

    /**
     * Generate the standard ThinkPlus CSRF form field.
     *
     * The project's Csrf class uses:
     * name="_csrf"
     */
    function csrf_field(): string
    {
        return Csrf::field();
    }
}


if (!function_exists('verify_csrf')) {

    /**
     * Verify a supplied CSRF token.
     */
    function verify_csrf(
        ?string $token = null
    ): bool {

        if ($token === null) {

            $value = $_POST['_csrf']
                ?? $_SERVER['HTTP_X_CSRF_TOKEN']
                ?? null;

            $token = is_string($value)
                ? $value
                : null;
        }

        return Csrf::verify($token);
    }
}


if (!function_exists('require_csrf')) {

    /**
     * Require a valid CSRF token.
     *
     * Sends the response handled by Csrf.php.
     */
    function require_csrf(
        ?string $token = null
    ): void {

        if ($token === null) {

            $value = $_POST['_csrf']
                ?? $_SERVER['HTTP_X_CSRF_TOKEN']
                ?? null;

            $token = is_string($value)
                ? $value
                : null;
        }

        Csrf::requireValid($token);
    }
}


if (!function_exists('csrf_request')) {

    /**
     * Protect the current state-changing request.
     */
    function csrf_request(): void
    {
        Csrf::verifyRequest();
    }
}


if (!function_exists('csrf_rotate')) {

    /**
     * Rotate the CSRF token.
     */
    function csrf_rotate(): string
    {
        return Csrf::rotate();
    }
}


/*
|--------------------------------------------------------------------------
| TENANT HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('tenant_id')) {

    /**
     * Return the authenticated tenant/school ID.
     *
     * Tenant.php is authoritative.
     */
    function tenant_id(): ?int
    {
        return Tenant::id();
    }
}


if (!function_exists('require_tenant')) {

    /**
     * Require a valid authenticated tenant.
     */
    function require_tenant(): int
    {
        return Tenant::requireId();
    }
}


if (!function_exists('tenant_check')) {

    /**
     * Verify tenant access.
     */
    function tenant_check(
        ?int $schoolId = null
    ): bool {

        return Tenant::check(
            $schoolId
        );
    }
}


if (!function_exists('require_tenant_access')) {

    /**
     * Require access to the supplied tenant.
     */
    function require_tenant_access(
        ?int $schoolId = null
    ): void {

        Tenant::require(
            $schoolId
        );
    }
}


if (!function_exists('tenant_school_id')) {

    /**
     * Validate a requested school ID against the
     * authenticated tenant.
     */
    function tenant_school_id(
        mixed $requestedSchoolId = null
    ): int {

        return Tenant::validateRequestedSchool(
            $requestedSchoolId
        );
    }
}


if (!function_exists('tenant_query_params')) {

    /**
     * Add the authenticated school ID to query parameters.
     *
     * Never use a school ID directly from $_GET or $_POST.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    function tenant_query_params(
        array $params = []
    ): array {

        return Tenant::queryParams(
            $params
        );
    }
}


if (!function_exists('tenant_where')) {

    /**
     * Return the standard tenant WHERE condition.
     *
     * @return array{
     *     sql:string,
     *     params:array<string,int>
     * }
     */
    function tenant_where(
        string $column = 'school_id'
    ): array {

        return Tenant::where(
            $column
        );
    }
}


 
