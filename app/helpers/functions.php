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

if (!function_exists('tenant_owns')) {

    /**
     * Determine whether a record belongs to the current tenant.
     */
    function tenant_owns(
        string $table,
        int $recordId,
        string $idColumn = 'id',
        string $tenantColumn = 'school_id'
    ): bool {

        return Tenant::owns(
            $table,
            $recordId,
            $idColumn,
            $tenantColumn
        );
    }
}


if (!function_exists('require_tenant_ownership')) {

    /**
     * Require ownership of a tenant-scoped record.
     */
    function require_tenant_ownership(
        string $table,
        int $recordId,
        string $idColumn = 'id',
        string $tenantColumn = 'school_id'
    ): void {

        Tenant::requireOwnership(
            $table,
            $recordId,
            $idColumn,
            $tenantColumn
        );
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
     */
    function valid_email(string $email): bool
    {
        return Security::isValidEmail(
            trim($email)
        );
    }
}


if (!function_exists('password_errors')) {

    /**
     * Validate password requirements.
     *
     * @return array<int,string>
     */
    function password_errors(
        string $password
    ): array {

        return Security::validatePassword(
            $password
        );
    }
}


if (!function_exists('valid_password')) {

    /**
     * Determine whether a password passes validation.
     */
    function valid_password(
        string $password
    ): bool {

        return password_errors($password) === [];
    }
}


if (!function_exists('required')) {

    /**
     * Determine whether a value is non-empty.
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
            return $value !== [];
        }

        return true;
    }
}


if (!function_exists('valid_int_id')) {

    /**
     * Validate a positive internal integer ID.
     */
    function valid_int_id(mixed $value): bool
    {
        $id = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        return $id !== false &&
            $id > 0;
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
     * @param array<int|string,mixed> $params
     */
    function db_execute(
        PDO $pdo,
        string $sql,
        array $params = []
    ): \PDOStatement {

        $statement = $pdo->prepare($sql);

        $statement->execute($params);

        return $statement;
    }
}


if (!function_exists('db_fetch')) {

    /**
     * Execute a query and return one row.
     *
     * @return array<string,mixed>|null
     *
     * @param array<int|string,mixed> $params
     */
    function db_fetch(
        PDO $pdo,
        string $sql,
        array $params = []
    ): ?array {

        $statement = db_execute(
            $pdo,
            $sql,
            $params
        );

        $row = $statement->fetch(
            PDO::FETCH_ASSOC
        );

        return $row === false
            ? null
            : $row;
    }
}


if (!function_exists('db_fetch_all')) {

    /**
     * Execute a query and return all rows.
     *
     * @return array<int,array<string,mixed>>
     *
     * @param array<int|string,mixed> $params
     */
    function db_fetch_all(
        PDO $pdo,
        string $sql,
        array $params = []
    ): array {

        $statement = db_execute(
            $pdo,
            $sql,
            $params
        );

        return $statement->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
}


if (!function_exists('db_fetch_column')) {

    /**
     * Execute a query and return one column.
     *
     * @param array<int|string,mixed> $params
     */
    function db_fetch_column(
        PDO $pdo,
        string $sql,
        array $params = [],
        int $column = 0
    ): mixed {

        $statement = db_execute(
            $pdo,
            $sql,
            $params
        );

        $value = $statement->fetchColumn(
            $column
        );

        return $value === false
            ? null
            : $value;
    }
}


if (!function_exists('db_exists')) {

    /**
     * Determine whether a query returns at least one row.
     *
     * @param array<int|string,mixed> $params
     */
    function db_exists(
        PDO $pdo,
        string $sql,
        array $params = []
    ): bool {

        return db_fetch(
            $pdo,
            $sql,
            $params
        ) !== null;
    }
}


/*
|--------------------------------------------------------------------------
| TENANT DATABASE HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('tenant_db_fetch')) {

    /**
     * Fetch one tenant-scoped record.
     *
     * The caller supplies the tenant condition in SQL.
     * The tenant ID is always obtained from Tenant.php.
     *
     * @return array<string,mixed>|null
     */
    function tenant_db_fetch(
        PDO $pdo,
        string $sql,
        array $params = []
    ): ?array {

        $tenantId = Tenant::requireId();

        $params['tenant_school_id'] =
            $tenantId;

        return db_fetch(
            $pdo,
            $sql,
            $params
        );
    }
}


/*
|--------------------------------------------------------------------------
| JSON RESPONSE HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('json_response')) {

    /**
     * Return a JSON response and terminate execution.
     *
     * @param array<string,mixed>|list<mixed> $data
     * @return never
     */
    function json_response(
        array $data,
        int $status = 200
    ): never {

        if (
            $status < 100 ||
            $status > 599
        ) {
            $status = 200;
        }

        http_response_code($status);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        header(
            'X-Content-Type-Options: nosniff'
        );

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_INVALID_UTF8_SUBSTITUTE
        );

        exit;
    }
}


if (!function_exists('json_success')) {

    /**
     * Return a successful JSON response.
     *
     * @param array<string,mixed> $data
     * @return never
     */
    function json_success(
        array $data = [],
        int $status = 200
    ): never {

        json_response(
            array_merge(
                [
                    'success' => true
                ],
                $data
            ),
            $status
        );
    }
}


if (!function_exists('json_error')) {

    /**
     * Return an error JSON response.
     *
     * @param array<string,mixed> $data
     * @return never
     */
    function json_error(
        string $message,
        int $status = 400,
        array $data = []
    ): never {

        json_response(
            array_merge(
                [
                    'success' => false,
                    'message' => $message
                ],
                $data
            ),
            $status
        );
    }
}


if (!function_exists('json_created')) {

    /**
     * Return a 201 Created response.
     *
     * @param array<string,mixed> $data
     * @return never
     */
    function json_created(
        array $data = []
    ): never {

        json_success(
            $data,
            201
        );
    }
}


if (!function_exists('json_no_content')) {

    /**
     * Return a 204 No Content response.
     *
     * @return never
     */
    function json_no_content(): never
    {
        http_response_code(204);

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| DATE / TIME HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('format_date')) {

    /**
     * Format a date value.
     */
    function format_date(
        ?string $date,
        string $format = 'd M Y'
    ): string {

        if (
            $date === null ||
            trim($date) === ''
        ) {
            return '';
        }

        try {

            return (
                new DateTimeImmutable($date)
            )->format($format);

        } catch (Throwable) {

            return '';
        }
    }
}


if (!function_exists('format_datetime')) {

    /**
     * Format a date/time value.
     */
    function format_datetime(
        ?string $dateTime,
        string $format = 'd M Y H:i'
    ): string {

        return format_date(
            $dateTime,
            $format
        );
    }
}


if (!function_exists('format_time')) {

    /**
     * Format a time value.
     */
    function format_time(
        ?string $time,
        string $format = 'H:i'
    ): string {

        if (
            $time === null ||
            trim($time) === ''
        ) {
            return '';
        }

        try {

            return (
                new DateTimeImmutable($time)
            )->format($format);

        } catch (Throwable) {

            return '';
        }
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
     * Default currency is Kenyan Shillings.
     */
    function money(
        int|float|string|null $amount,
        string $currency = 'KES'
    ): string {

        if (
            $amount === null ||
            $amount === ''
        ) {
            $amount = 0;
        }

        if (!is_numeric($amount)) {
            $amount = 0;
        }

        return $currency .
            ' ' .
            number_format(
                (float) $amount,
                2,
                '.',
                ','
            );
    }
}


if (!function_exists('format_number')) {

    /**
     * Format a numeric value.
     */
    function format_number(
        int|float|string|null $value,
        int $decimals = 0
    ): string {

        if (
            $value === null ||
            !is_numeric($value)
        ) {
            return '0';
        }

        $decimals = max(
            0,
            $decimals
        );

        return number_format(
            (float) $value,
            $decimals,
            '.',
            ','
        );
    }
}


/*
|--------------------------------------------------------------------------
| STRING HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('str_limit')) {

    /**
     * Limit a string to a maximum length.
     */
    function str_limit(
        string $value,
        int $limit = 100,
        string $suffix = '...'
    ): string {

        if ($limit <= 0) {
            return '';
        }

        if (
            function_exists('mb_strlen') &&
            function_exists('mb_substr')
        ) {

            if (
                mb_strlen(
                    $value,
                    'UTF-8'
                ) <= $limit
            ) {
                return $value;
            }

            $suffixLength =
                mb_strlen(
                    $suffix,
                    'UTF-8'
                );

            if ($suffixLength >= $limit) {
                return mb_substr(
                    $suffix,
                    0,
                    $limit,
                    'UTF-8'
                );
            }

            return mb_substr(
                $value,
                0,
                $limit - $suffixLength,
                'UTF-8'
            ) . $suffix;
        }

        if (strlen($value) <= $limit) {
            return $value;
        }

        $suffixLength = strlen($suffix);

        if ($suffixLength >= $limit) {
            return substr(
                $suffix,
                0,
                $limit
            );
        }

        return substr(
            $value,
            0,
            $limit - $suffixLength
        ) . $suffix;
    }
}

if (!function_exists('str_slug')) {

    /**
     * Generate a simple URL-safe slug.
     */
    function str_slug(
        string $value
    ): string {

        $value = trim(
            strtolower($value)
        );

        if (
            function_exists('iconv')
        ) {
            $converted = iconv(
                'UTF-8',
                'ASCII//TRANSLIT//IGNORE',
                $value
            );

            if ($converted !== false) {
                $value = $converted;
            }
        }

        $value = preg_replace(
            '/[^a-z0-9]+/i',
            '-',
            $value
        );

        $value = trim(
            (string) $value,
            '-'
        );

        return strtolower($value);
    }
}


/*
|--------------------------------------------------------------------------
| ARRAY HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('array_get')) {

    /**
     * Retrieve a nested array value using dot notation.
     *
     * Example:
     * array_get($data, 'user.profile.name')
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


if (!function_exists('array_only')) {

    /**
     * Return only selected keys.
     *
     * @param array<string,mixed> $array
     * @param array<int,string> $keys
     *
     * @return array<string,mixed>
     */
    function array_only(
        array $array,
        array $keys
    ): array {

        return array_intersect_key(
            $array,
            array_flip($keys)
        );
    }
}


if (!function_exists('array_except')) {

    /**
     * Remove selected keys from an array.
     *
     * @param array<string,mixed> $array
     * @param array<int,string> $keys
     *
     * @return array<string,mixed>
     */
    function array_except(
        array $array,
        array $keys
    ): array {

        foreach ($keys as $key) {
            unset($array[$key]);
        }

        return $array;
    }
}


/*
|--------------------------------------------------------------------------
| PAGINATION HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('pagination_url')) {

    /**
     * Generate a pagination URL while preserving
     * existing query parameters.
     */
    function pagination_url(
        int $page,
        string $parameter = 'page'
    ): string {

        $page = max(
            1,
            $page
        );

        $path =
            $_SERVER['PHP_SELF']
            ?? '/';

        $query = $_GET;

        $query[$parameter] =
            $page;

        $queryString =
            http_build_query(
                $query
            );

        return $path .
            (
                $queryString !== ''
                    ? '?' . $queryString
                    : ''
            );
    }
}


/*
|--------------------------------------------------------------------------
| ENVIRONMENT HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('env_value')) {

    /**
     * Read and normalize an environment value.
     *
     * Supported booleans:
     * true, false, yes, no, on, off, 1, 0
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

        $lower = strtolower($value);

        return match ($lower) {

            'true',
            'yes',
            'on',
            '1'
                => true,

            'false',
            'no',
            'off',
            '0'
                => false,

            'null',
            '(null)'
                => null,

            default
                => $value,
        };
    }
}


if (!function_exists('app_env')) {

    /**
     * Return APP_ENV.
     */
    function app_env(
        string $default = 'production'
    ): string {

        $value = env_value(
            'APP_ENV',
            $default
        );

        return is_string($value)
            ? $value
            : $default;
    }
}


if (!function_exists('app_debug')) {

    /**
     * Determine whether application debug mode is enabled.
     */
    function app_debug(): bool
    {
        return (bool) env_value(
            'APP_DEBUG',
            false
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
     * Apply ThinkPlus security headers.
     *
     * Delegates the implementation to Security.php.
     */
    function security_headers(): void
    {
        Security::securityHeaders();
    }
}


/*
|--------------------------------------------------------------------------
| SAFE IDENTIFIERS
|--------------------------------------------------------------------------
*/

if (!function_exists('public_id')) {


