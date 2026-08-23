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
 * Responsibilities:
 * - HTML escaping
 * - URL helpers
 * - Redirect helpers
 * - Request helpers
 * - Authentication shortcuts
 * - CSRF helpers
 * - Tenant helpers
 * - Validation helpers
 * - Input helpers
 * - Flash messages
 * - JSON responses
 * - Date/time formatting
 * - Money formatting
 * - Pagination helpers
 * - Safe array access
 *
 * Security-sensitive operations remain delegated to:
 *
 * security/Security.php
 * security/Csrf.php
 * security/Tenant.php
 * security/Audit.php
 *
 * ============================================================
 */

use PDO;
use Security\Security;
use Security\Csrf;
use Security\Tenant;

if (!function_exists('e')) {

    /**
     * Escape a value for safe HTML output.
     *
     * @param mixed $value
     * @return string
     */
    function e(mixed $value): string
    {
        return Security::e(
            is_scalar($value) || $value === null
                ? (string) $value
                : ''
        );
    }
}


if (!function_exists('old')) {

    /**
     * Retrieve an old form value.
     *
     * Primarily useful when form data has been placed
     * into the session after a failed submission.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
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
     * Store old form values in the session.
     *
     * Password fields should never be stored.
     *
     * @param array<string,mixed> $data
     * @return void
     */
    function set_old(array $data): void
    {
        Security::startSecureSession();

        unset(
            $data['password'],
            $data['password_confirmation'],
            $data['current_password'],
            $data['new_password'],
            $data['new_password_confirmation']
        );

        $_SESSION['_old'] = $data;
    }
}


if (!function_exists('clear_old')) {

    /**
     * Remove old form values.
     *
     * @return void
     */
    function clear_old(): void
    {
        Security::startSecureSession();

        unset($_SESSION['_old']);
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
     * APP_URL may be supplied through the environment.
     *
     * @param string $path
     * @return string
     */
    function base_url(string $path = ''): string
    {
        $base = getenv('APP_URL');

        if (
            $base === false ||
            trim($base) === ''
        ) {
            $https = (
                !empty($_SERVER['HTTPS']) &&
                $_SERVER['HTTPS'] !== 'off'
            );

            $scheme = $https
                ? 'https'
                : 'http';

            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

            $base = $scheme . '://' . $host;
        }

        $base = rtrim(
            trim($base),
            '/'
        );

        $path = trim(
            $path,
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
     *
     * @param string $path
     * @return string
     */
    function url(string $path = ''): string
    {
        return base_url($path);
    }
}


if (!function_exists('asset')) {

    /**
     * Generate an asset URL.
     *
     * @param string $path
     * @return string
     */
    function asset(string $path): string
    {
        return base_url(
            'assets/' . ltrim($path, '/')
        );
    }
}


/*
|--------------------------------------------------------------------------
| REDIRECT HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect')) {

    /**
     * Redirect to a relative application path.
     *
     * @param string $path
     * @param int $status
     * @return never
     */
    function redirect(
        string $path,
        int $status = 302
    ): never {

        if ($status < 300 || $status > 399) {
            $status = 302;
        }

        /*
         * Only permit local application redirects.
         */
        if (
            preg_match(
                '#^https?://#i',
                $path
            )
        ) {
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
     * Return the user to the previous page.
     *
     * @param string $fallback
     * @return never
     */
    function back(
        string $fallback = '/'
    ): never {

        $referer =
            $_SERVER['HTTP_REFERER'] ?? '';

        if (
            $referer !== '' &&
            is_safe_local_url($referer)
        ) {
            header(
                'Location: ' . $referer,
                true,
                302
            );

            exit;
        }

        redirect($fallback);
    }
}


if (!function_exists('is_safe_local_url')) {

    /**
     * Determine whether a URL is local to the current host.
     *
     * @param string $url
     * @return bool
     */
    function is_safe_local_url(
        string $url
    ): bool {

        if ($url === '') {
            return false;
        }

        /*
         * Relative URLs are local.
         */
        if (str_starts_with($url, '/')) {
            return !str_starts_with(
                $url,
                '//'
            );
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return false;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';

        if (
            !isset($parts['host']) ||
            $host === ''
        ) {
            return false;
        }

        return strcasecmp(
            (string) $parts['host'],
            $host
        ) === 0;
    }
}


/*
|--------------------------------------------------------------------------
| REQUEST HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('request_method')) {

    /**
     * Return the current HTTP request method.
     *
     * @return string
     */
    function request_method(): string
    {
        return strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );
    }
}


if (!function_exists('is_get')) {

    /**
     * Determine whether the request is GET.
     *
     * @return bool
     */
    function is_get(): bool
    {
        return request_method() === 'GET';
    }
}


if (!function_exists('is_post')) {

    /**
     * Determine whether the request is POST.
     *
     * @return bool
     */
    function is_post(): bool
    {
        return request_method() === 'POST';
    }
}


if (!function_exists('is_ajax')) {

    /**
     * Determine whether the request appears to be AJAX.
     *
     * @return bool
     */
    function is_ajax(): bool
    {
        return strtolower(
            $_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''
        ) === 'xmlhttprequest';
    }
}


if (!function_exists('input')) {

    /**
     * Safely retrieve a request input value.
     *
     * POST values are preferred over GET values.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function input(
        string $key,
        mixed $default = null
    ): mixed {

        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }

        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }

        return $default;
    }
}


if (!function_exists('input_string')) {

    /**
     * Retrieve a request value as a trimmed string.
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    function input_string(
        string $key,
        string $default = ''
    ): string {

        $value = input(
            $key,
            $default
        );

        if (!is_string($value)) {
            return $default;
        }

        return trim($value);
    }
}


if (!function_exists('input_int')) {

    /**
     * Retrieve a request value as an integer.
     *
     * @param string $key
     * @param int $default
     * @return int
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


/*
|--------------------------------------------------------------------------
| AUTHENTICATION HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('auth_check')) {

    /**
     * Determine whether a user is authenticated.
     *
     * @return bool
     */
    function auth_check(): bool
    {
        return Security::isLoggedIn();
    }
}


if (!function_exists('auth_id')) {

    /**
     * Return the authenticated user's internal ID.
     *
     * @return int|null
     */
    function auth_id(): ?int
    {
        return Security::userId();
    }
}


if (!function_exists('auth_role')) {

    /**
     * Return the authenticated user's role.
     *
     * @return string|null
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
     * @return int|null
     */
    function auth_school_id(): ?int
    {
        return Security::schoolId();
    }
}


if (!function_exists('has_role')) {

    /**
     * Determine whether the authenticated user has
     * one of the supplied roles.
     *
     * @param string|array<int,string> $roles
     * @return bool
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
     *
     * @param string $loginUrl
     * @return void
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
     * @param string $redirectUrl
     * @return void
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
     * Require the current visitor to be unauthenticated.
     *
     * @return void
     */
    function require_guest(): void
    {
        Security::requireGuest();
    }
}


/*
|--------------------------------------------------------------------------
| CSRF HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('csrf_token')) {

    /**
     * Generate or retrieve the current CSRF token.
     *
     * @return string
     */
    function csrf_token(): string
    {
        Security::startSecureSession();

        /*
         * Support the Csrf class used by ThinkPlus.
         *
         * The token() method is preferred when available.
         */
        if (
            method_exists(
                Csrf::class,
                'token'
            )
        ) {
            return (string) Csrf::token();
        }

        if (
            method_exists(
                Csrf::class,
                'getToken'
            )
        ) {
            return (string) Csrf::getToken();
        }

        /*
         * Fallback is deliberately session-bound and
         * cryptographically random.
         */
        if (
            !isset($_SESSION['_csrf_token']) ||
            !is_string($_SESSION['_csrf_token']) ||
            $_SESSION['_csrf_token'] === ''
        ) {
            $_SESSION['_csrf_token'] =
                bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}


if (!function_exists('csrf_field')) {

    /**
     * Generate a hidden CSRF form field.
     *
     * @return string
     */
    function csrf_field(): string
    {
        /*
         * Prefer the project's Csrf implementation.
         */
        if (
            method_exists(
                Csrf::class,
                'field'
            )
        ) {
            return (string) Csrf::field();
        }

        if (
            method_exists(
                Csrf::class,
                'input'
            )
        ) {
            return (string) Csrf::input();
        }

        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            e(csrf_token())
        );
    }
}


if (!function_exists('verify_csrf')) {

    /**
     * Verify a CSRF token.
     *
     * @param string|null $token
     * @return bool
     */
    function verify_csrf(
        ?string $token = null
    ): bool {

        if ($token === null) {
            $token = input_string(
                'csrf_token'
            );
        }

        if ($token === '') {
            return false;
        }

        if (
            method_exists(
                Csrf::class,
                'verify'
            )
        ) {
            return Csrf::verify($token);
        }

        /*
         * Fallback for installations where the Csrf class
         * has not yet exposed verify().
         */
        Security::startSecureSession();

        $sessionToken =
            $_SESSION['_csrf_token'] ?? null;

        if (
            !is_string($sessionToken) ||
            $sessionToken === ''
        ) {
            return false;
        }

        return hash_equals(
            $sessionToken,
            $token
        );
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
     * Tenant.php remains the authoritative tenant
     * security layer.
     *
     * @return int|null
     */
    function tenant_id(): ?int
    {
        /*
         * Prefer Tenant.php when its API is available.
         */
        foreach (
            [
                'schoolId',
                'id',
                'currentSchoolId',
                'current'
            ] as $method
        ) {

            if (
                method_exists(
                    Tenant::class,
                    $method
                )
            ) {
                try {

                    $value = Tenant::$method();

                    if (
                        is_numeric($value) &&
                        (int) $value > 0
                    ) {
                        return (int) $value;
                    }

                } catch (Throwable $e) {
                    /*
                     * Fall through to Security.
                     */
                }
            }
        }

        return Security::schoolId();
    }
}


if (!function_exists('require_tenant')) {

    /**
     * Require an authenticated tenant context.
     *
     * @return int
     */
    function require_tenant(): int
    {
        require_login();

        $schoolId = tenant_id();

        if ($schoolId === null) {

            http_response_code(403);

            exit(
                'No active school tenant is available.'
            );
        }

        return $schoolId;
    }
}


if (!function_exists('tenant_query')) {

    /**
     * Add the authenticated tenant condition to SQL.
     *
     * This helper does NOT execute SQL.
     *
     * Example:
     *
     * [$sql, $params] = tenant_query(
     *     'SELECT * FROM students WHERE status = ?',
     *     ['active']
     * );
     *
     * The resulting SQL contains:
     *
     * school_id = ?
     *
     * Tenant ID is always appended to the parameters.
     *
     * @param string $sql
     * @param array<int,mixed> $params
     * @param string $column
     * @return array{0:string,1:array<int,mixed>}
     */
    function tenant_query(
        string $sql,
        array $params = [],
        string $column = 'school_id'
    ): array {

        $schoolId = require_tenant();

        /*
         * Basic identifier protection.
         */
        if (
            !preg_match(
                '/^[A-Za-z_][A-Za-z0-9_]*$/',
                $column
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid tenant column.'
            );
        }

        $sql = trim($sql);

        if ($sql === '') {
            throw new InvalidArgumentException(
                'SQL query cannot be empty.'
            );
        }

        /*
         * Add tenant condition.
         *
         * The helper is intended for SELECT/UPDATE/DELETE
         * statements where a WHERE clause can safely be added.
         */
        if (
            preg_match(
                '/\bWHERE\b/i',
                $sql
            )
        ) {
            $sql .= ' AND ' . $column . ' = ?';
        } else {
            $sql .= ' WHERE ' . $column . ' = ?';
        }

        $params[] = $schoolId;

        return [
            $sql,
            $params
        ];
    }
}


/*
|--------------------------------------------------------------------------
| VALIDATION HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('valid_email')) {

    /**
     * Validate an email address.
     *
     * @param string $email
     * @return bool
     */
    function valid_email(string $email): bool
    {
        return Security::isValidEmail(
            trim($email)
        );
    }
}


if (!function_exists('required')) {

    /**
     * Determine whether a value is non-empty.
     *
     * @param mixed $value
     * @return bool
     */
    function required(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return true;
    }
}


if (!function_exists('valid_password')) {

    /**
     * Validate a password using the application's
     * password policy.
     *
     * @param string $password
     * @return array<int,string>
     */
    function valid_password(
        string $password
    ): array {
        return Security::validatePassword(
            $password
        );
    }
}


if (!function_exists('valid_id')) {

    /**
     * Determine whether a value is a positive integer ID.
     *
     * @param mixed $value
     * @return bool
     */
    function valid_id(mixed $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        ) !== false;
    }
}


if (!function_exists('sanitize')) {

    /**
     * Sanitize a simple text value.
     *
     * This is NOT an HTML escaping replacement.
     * Use e() when displaying output.
     *
     * @param string $value
     * @return string
     */
    function sanitize(string $value): string
    {
        return Security::sanitize(
            $value
        );
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
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    function flash(
        string $type,
        string $message
    ): void {

        Security::startSecureSession();

        if (
            !isset($_SESSION['_flash']) ||
            !is_array($_SESSION['_flash'])
        ) {
            $_SESSION['_flash'] = [];
        }

        $_SESSION['_flash'][$type] = $message;
    }
}


if (!function_exists('get_flash')) {

    /**
     * Retrieve and remove a flash message.
     *
     * @param string $type
     * @param mixed $default
     * @return mixed
     */
    function get_flash(
        string $type,
        mixed $default = null
    ): mixed {

        Security::startSecureSession();

        $messages =
            $_SESSION['_flash'] ?? [];

        if (!is_array($messages)) {
            return $default;
        }

        if (!array_key_exists($type, $messages)) {
            return $default;
        }

        $message = $messages[$type];

        unset(
            $_SESSION['_flash'][$type]
        );

        return $message;
    }
}


if (!function_exists('has_flash')) {

    /**
     * Determine whether a flash message exists.
     *
     * @param string $type
     * @return bool
     */
    function has_flash(string $type): bool
    {
        Security::startSecureSession();

        return isset(
            $_SESSION['_flash'][$type]
        );
    }
}


/*
|--------------------------------------------------------------------------
| JSON RESPONSES
|--------------------------------------------------------------------------
*/

if (!function_exists('json_response')) {

    /**
     * Return a JSON response and terminate execution.
     *
     * @param mixed $data
     * @param int $status
     * @return never
     */
    function json_response(
        mixed $data,
        int $status = 200
    ): never {

        if (!headers_sent()) {

            http_response_code(
                $status
            );

            header(
                'Content-Type: application/json; charset=UTF-8'
            );

            header(
                'X-Content-Type-Options: nosniff'
            );
        }

        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            $json = json_encode([
                'success' => false,
                'message' =>
                    'Unable to encode response.'
            ]);
        }

        echo $json;

        exit;
    }
}


if (!function_exists('json_success')) {

    /**
     * Return a successful JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return never
     */
    function json_success(
        mixed $data = null,
        string $message = 'Success.',
        int $status = 200
    ): never {

        json_response(
            [
                'success' => true,
                'message' => $message,
                'data' => $data
            ],
            $status
        );
    }
}


if (!function_exists('json_error')) {

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param int $status
     * @param mixed $errors
     * @return never
     */
    function json_error(
        string $message,
        int $status = 400,
        mixed $errors = null
    ): never {

        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        json_response(
            $response,
            $status
        );
    }
}


/*
|--------------------------------------------------------------------------
| SAFE ARRAY HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('array_get')) {

    /**
     * Safely retrieve an array value.
     *
     * Supports dot notation:
     *
     * array_get($data, 'user.email')
     *
     * @param array<mixed> $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function array_get(
        array $array,
        string $key,
        mixed $default = null
    ): mixed {

        if ($key === '') {
            return $array;
        }

        $segments = explode(
            '.',
            $key
        );

        $value = $array;

        foreach ($segments as $segment) {

            if (
                !is_array($value) ||
                !array_key_exists(
                    $segment,
                    $value
                )
            ) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}


/*
|--------------------------------------------------------------------------
| DATE / TIME HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('format_date')) {

    /**
     * Format a date for display.
     *
     * @param string|null $date
     * @param string $format
     * @param string $default
     * @return string
     */
    function format_date(
        ?string $date,
        string $format = 'd M Y',
        string $default = '-'
    ): string {

        if (
            $date === null ||
            trim($date) === ''
        ) {
            return $default;
        }

        try {

            return (new DateTimeImmutable(
                $date
            ))->format($format);

        } catch (Throwable $e) {

            return $default;
        }
    }
}


if (!function_exists('format_datetime')) {

    /**
     * Format a date/time for display.
     *
     * @param string|null $datetime
     * @param string $format
     * @param string $default
     * @return string
     */
    function format_datetime(
        ?string $datetime,
        string $format = 'd M Y, H:i',
        string $default = '-'
    ): string {

        return format_date(
            $datetime,
            $format,
            $default
        );
    }
}


/*
|--------------------------------------------------------------------------
| MONEY HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('money')) {

    /**
     * Format a monetary amount.
     *
     * Defaults to Kenyan Shillings.
     *
     * @param int|float|string|null $amount
     * @param string $currency
     * @return string
     */
    function money(
        int|float|string|null $amount,
        string $currency = 'KES'
    ): string {

        if (
            $amount === null ||
            !is_numeric($amount)
        ) {
            $amount = 0;
        }

        return $currency . ' ' .
            number_format(
                (float) $amount,
                2,
                '.',
                ','
            );
    }
}


/*
|--------------------------------------------------------------------------
| TEXT HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('str_limit')) {

    /**
     * Limit a string to a maximum length.
     *
     * @param string $value
     * @param int $limit
     * @param string $suffix
     * @return string
     */
    function str_limit(
        string $value,
        int $limit = 100,
        string $suffix = '...'
    ): string {

        $value = trim($value);

        if ($limit <= 0) {
            return '';
        }

        if (
            function_exists('mb_strlen') &&
            mb_strlen($value) <= $limit
        ) {
            return $value;
        }

        if (
            !function_exists('mb_strlen') &&
            strlen($value) <= $limit
        ) {
            return $value;
        }

        $available =
            max(
                0,
                $limit - strlen($suffix)
            );

        if (function_exists('mb_substr')) {

            return mb_substr(
                $value,
                0,
                $available
            ) . $suffix;
        }

        return substr(
            $value,
            0,
            $available
        ) . $suffix;
    }
}


if (!function_exists('human_name')) {

    /**
     * Normalize a person's display name.
     *
     * @param string|null $name
     * @return string
     */
    function human_name(
        ?string $name
    ): string {

        $name = trim(
            (string) $name
        );

        if ($name === '') {
            return '';
        }

        return ucwords(
            strtolower($name)
        );
    }
}


/*
|--------------------------------------------------------------------------
| PAGINATION HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('paginate')) {

    /**
     * Calculate pagination information.
     *
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @return array{
     *     total:int,
     *     per_page:int,
     *     current_page:int,
     *     last_page:int,
     *     offset:int,
     *     has_previous:bool,
     *     has_next:bool
     * }
     */
    function paginate(
        int $total,
        int $perPage = 20,
        int $currentPage = 1
    ): array {

        $total = max(
            0,
            $total
        );

        $perPage = max(
            1,
            $perPage
        );

        $lastPage = max(
            1,
            (int) ceil(
                $total / $perPage
            )
        );

        $currentPage = max(
            1,
            min(
                $currentPage,
                $lastPage
            )
        );

        return [
            'total' =>
                $total,

            'per_page' =>
                $perPage,

            'current_page' =>
                $currentPage,

            'last_page' =>
                $lastPage,

            'offset' =>
                ($currentPage - 1) * $perPage,

            'has_previous' =>
                $currentPage > 1,

            'has_next' =>
                $currentPage < $lastPage
        ];
    }
}


if (!function_exists('pagination_url')) {

    /**
     * Generate a pagination URL while preserving
     * existing query parameters.
     *
     * @param int $page
     * @param string|null $path
     * @return string
     */
    function pagination_url(
        int $page,
        ?string $path = null
    ): string {

        $page = max(
            1,
            $page
        );

        $path ??=
            parse_url(
                $_SERVER['REQUEST_URI'] ?? '/',
                PHP_URL_PATH
            ) ?: '/';

        $query =
            $_GET;

        $query['page'] = $page;

        return $path . '?' .
            http_build_query(
                $query
            );
    }
}


/*
|--------------------------------------------------------------------------
| DATABASE HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('db_execute')) {

    /**
     * Execute a prepared SQL statement.
     *
     * @param PDO $pdo
     * @param string $sql
     * @param array<int|string,mixed> $params
     * @return PDOStatement
     */
    function db_execute(
        PDO $pdo,
        string $sql,
        array $params = []
    ): PDOStatement {

        $stmt = $pdo->prepare(
            $sql
        );

        $stmt->execute(
            $params
        );

        return $stmt;
    }
}


if (!function_exists('db_fetch')) {

    /**
     * Fetch one database row.
     *
     * @param PDO $pdo
     * @param string $sql
     * @param array<int|string,mixed> $params
     * @return array<string,mixed>|null
     */
    function db_fetch(
        PDO $pdo,
        string $sql,
        array $params = []
    ): ?array {

        $stmt = db_execute(
            $pdo,
            $sql,
            $params
        );

        $row = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $row !== false
            ? $row
            : null;
    }
}


if (!function_exists('db_fetch_all')) {

    /**
     * Fetch all database rows.
     *
     * @param PDO $pdo
     * @param string $sql
     * @param array<int|string,mixed> $params
     * @return array<int,array<string,mixed>>
     */
    function db_fetch_all(
        PDO $pdo,
        string $sql,
        array $params = []
    ): array {

        $stmt = db_execute(
            $pdo,
            $sql,
            $params
        );

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
}


/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

if (!function_exists('security_headers')) {

    /**
     * Apply application security headers.
     *
     * @return void
     */
    function security_headers(): void
    {
        Security::securityHeaders();
    }
}


/*
|--------------------------------------------------------------------------
| APPLICATION INITIALIZATION
|--------------------------------------------------------------------------
*/

if (!function_exists('app_security')) {

    /**
     * Initialize the common security layer for a request.
     *
     * This should normally be called once from the front
     * controller or entry point.
     *
     * @return void
     */
    function app_security(): void
    {
        Security::startSecureSession();

        Security::securityHeaders();

        Security::enforceSessionTimeout();
    }
}


/*
|--------------------------------------------------------------------------
| ERROR HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('abort')) {

    /**
     * Abort the current request.
     *
     * @param int $status
     * @param string $message
     * @return never
     */
    function abort(
        int $status = 500,
        string $message = 'An error occurred.'
    ): never {

        $status = max(
            100,
            min(
                $status,
                599
            )
        );

        http_response_code(
            $status
        );

        echo e($message);

        exit;
    }
}


if (!function_exists('abort_unless')) {

    /**
     * Abort unless a condition is true.
     *
     * @param bool $condition
     * @param int $status
     * @param string $message
     * @return void
     */
    function abort_unless(
        bool $condition,
        int $status = 403,
        string $message = 'Forbidden.'
    ): void {

        if (!$condition) {
            abort(
                $status,
                $message
            );
        }
    }
}


/*
|--------------------------------------------------------------------------
| ENVIRONMENT HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('env_value')) {

    /**
     * Read an environment variable with a default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env_value(
        string $key,
        mixed $default = null
    ): mixed {

        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        $value = trim($value);

        if ($value === '') {
            return $default;
        }

        return match (strtolower($value)) {

            'true',
            '(true)' =>
                true,

            'false',
            '(false)' =>
                false,

            'null',
            '(null)' =>
                null,

            default =>
                $value
        };
    }
}


/*
|--------------------------------------------------------------------------
| UNIQUE PUBLIC IDENTIFIER
|--------------------------------------------------------------------------
*/

if (!function_exists('public_id')) {

    /**
     * Generate a secure public identifier.
     *
     * @return string
     */
    function public_id(): string
    {
        return Security::publicId();
    }
}


/*
|--------------------------------------------------------------------------
| REQUEST TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('random_token')) {

    /**
     * Generate a secure random token.
     *
     * @param int $length
     * @return string
     */
    function random_token(
        int $length = 32
    ): string {
        return Security::randomToken(
            $length
        );
    }
}
