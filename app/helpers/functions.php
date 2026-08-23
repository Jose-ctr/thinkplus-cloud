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
 * - Tenant helpers
 * - Logout
 *
 * Security responsibilities remain in:
 *
 * security/Security.php
 * security/Csrf.php
 * security/Tenant.php
 * security/Audit.php
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
*/

require_once dirname(__DIR__, 2) . '/security/Security.php';
require_once dirname(__DIR__, 2) . '/security/Csrf.php';
require_once dirname(__DIR__, 2) . '/security/Tenant.php';


/*
|--------------------------------------------------------------------------
| IMPORT SECURITY CLASSES
|--------------------------------------------------------------------------
*/

use Security\Security;
use Security\Csrf;
use Security\Tenant;


/*
|--------------------------------------------------------------------------
| START SECURE SESSION
|--------------------------------------------------------------------------
|
| Security.php owns the session configuration.
|
*/

Security::startSecureSession();


/*
|--------------------------------------------------------------------------
| HTML ESCAPE
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {

    function e(?string $value): string
    {
        return Security::e($value);
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
        if (is_array($data) || is_object($data)) {
            return '';
        }

        return Security::sanitize(
            (string) $data
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

        if (!array_key_exists($key, $_POST)) {
            return $default;
        }

        return sanitize($_POST[$key]);
    }
}


/*
|--------------------------------------------------------------------------
| RAW POST INPUT
|--------------------------------------------------------------------------
|
| Useful when a value must not be modified by strip_tags()
| before validation.
|
*/

if (!function_exists('postRaw')) {

    function postRaw(
        string $key,
        mixed $default = null
    ): mixed {

        return array_key_exists($key, $_POST)
            ? $_POST[$key]
            : $default;
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

        if (!array_key_exists($key, $_GET)) {
            return $default;
        }

        return sanitize($_GET[$key]);
    }
}


/*
|--------------------------------------------------------------------------
| RAW GET INPUT
|--------------------------------------------------------------------------
*/

if (!function_exists('getRaw')) {

    function getRaw(
        string $key,
        mixed $default = null
    ): mixed {

        return array_key_exists($key, $_GET)
            ? $_GET[$key]
            : $default;
    }
}


/*
|--------------------------------------------------------------------------
| REQUEST METHOD
|--------------------------------------------------------------------------
*/

if (!function_exists('requestMethod')) {

    function requestMethod(): string
    {
        return strtoupper(
            (string) (
                $_SERVER['REQUEST_METHOD'] ?? 'GET'
            )
        );
    }
}


/*
|--------------------------------------------------------------------------
| IS POST REQUEST
|--------------------------------------------------------------------------
*/

if (!function_exists('isPost')) {

    function isPost(): bool
    {
        return requestMethod() === 'POST';
    }
}


/*
|--------------------------------------------------------------------------
| IS GET REQUEST
|--------------------------------------------------------------------------
*/

if (!function_exists('isGet')) {

    function isGet(): bool
    {
        return requestMethod() === 'GET';
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
        return Security::isLoggedIn();
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
        return Security::userId();
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
        return Security::role();
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

        return Security::hasRole($roles);
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT SCHOOL / TENANT ID
|--------------------------------------------------------------------------
|
| Tenant.php determines the effective tenant from the
| authenticated user. Do not trust school_id from requests.
|
*/

if (!function_exists('currentSchoolId')) {

    function currentSchoolId(): ?int
    {
        return Tenant::id();
    }
}


/*
|--------------------------------------------------------------------------
| ACTIVE TENANT ID
|--------------------------------------------------------------------------
*/

if (!function_exists('tenantId')) {

    function tenantId(): ?int
    {
        return Tenant::id();
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE TENANT
|--------------------------------------------------------------------------
*/

if (!function_exists('requireTenant')) {

    function requireTenant(): int
    {
        return Tenant::requireId();
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE SCHOOL
|--------------------------------------------------------------------------
*/

if (!function_exists('requireSchool')) {

    function requireSchool(
        string $redirectUrl = '/dashboard.php'
    ): void {

        requireLogin();

        if (!Tenant::authenticated()) {

            setFlash(
                'No school tenant is associated with this account.',
                'danger'
            );

            redirect($redirectUrl);
        }
    }
}


/*
|--------------------------------------------------------------------------
| HAS SCHOOL
|--------------------------------------------------------------------------
*/

if (!function_exists('hasSchool')) {

    function hasSchool(): bool
    {
        return Tenant::authenticated();
    }
}


/*
|--------------------------------------------------------------------------
| VALIDATE REQUESTED SCHOOL
|--------------------------------------------------------------------------
|
| If a request supplies school_id, Tenant.php verifies that
| it belongs to the authenticated user's tenant.
|
*/

if (!function_exists('validateSchoolId')) {

    function validateSchoolId(
        mixed $requestedSchoolId = null
    ): int {

        return Tenant::validateRequestedSchool(
            $requestedSchoolId
        );
    }
}


/*
|--------------------------------------------------------------------------
| TENANT OWNERSHIP CHECK
|--------------------------------------------------------------------------
*/

if (!function_exists('tenantOwns')) {

    function tenantOwns(
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


/*
|--------------------------------------------------------------------------
| REQUIRE TENANT OWNERSHIP
|--------------------------------------------------------------------------
*/

if (!function_exists('requireTenantOwnership')) {

    function requireTenantOwnership(
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
| TENANT QUERY PARAMETERS
|--------------------------------------------------------------------------
*/

if (!function_exists('tenantQueryParams')) {

    function tenantQueryParams(
        array $params = []
    ): array {

        return Tenant::queryParams($params);
    }
}


/*
|--------------------------------------------------------------------------
| TENANT WHERE CLAUSE
|--------------------------------------------------------------------------
*/

if (!function_exists('tenantWhere')) {

    function tenantWhere(
        string $column = 'school_id'
    ): array {

        return Tenant::where($column);
    }
}


/*
|--------------------------------------------------------------------------
| TENANT SQL SCOPE
|--------------------------------------------------------------------------
|
| Example:
|
| $scope = tenantScope(
|     'SELECT * FROM students WHERE deleted_at IS NULL'
| );
|
| $stmt = $pdo->prepare($scope['sql']);
| $stmt->execute($scope['params']);
|
*/

if (!function_exists('tenantScope')) {

    function tenantScope(
        string $sql,
        string $column = 'school_id'
    ): array {

        return Tenant::scope(
            $sql,
            $column
        );
    }
}


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect')) {

    function redirect(
        string $url,
        int $status = 302
    ): never {

        if (
            !preg_match(
                '#^/[A-Za-z0-9_\-./?=&%]*$#',
                $url
            )
        ) {
            $url = '/';
        }

        header(
            'Location: ' . $url,
            true,
            $status
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

        if (Security::isLoggedIn()) {
            return;
        }

        Security::startSecureSession();

        $_SESSION['intended_url'] =
            $_SERVER['REQUEST_URI'] ?? '/';

        setFlash(
            'Please log in to continue.',
            'warning'
        );

        redirect($loginUrl);
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

        Security::requireRole(
            $roles,
            $redirectUrl
        );
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE GUEST
|--------------------------------------------------------------------------
*/

if (!function_exists('requireGuest')) {

    function requireGuest(): void
    {
        Security::requireGuest();
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
| GET FLASH MESSAGE
|--------------------------------------------------------------------------
*/

if (!function_exists('getFlash')) {

    function getFlash(): ?array
    {
        if (!isset($_SESSION['message'])) {
            return null;
        }

        $message = (string)
            $_SESSION['message'];

        $type = (string) (
            $_SESSION['message_type'] ?? 'info'
        );

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

        unset(
            $_SESSION['message'],
            $_SESSION['message_type']
        );

        return [
            'message' => $message,
            'type' => $type
        ];
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
        $flash = getFlash();

        if ($flash === null) {
            return;
        }

        echo
            '<div class="alert alert-' .
            e($flash['type']) .
            '" role="alert">' .
            e($flash['message']) .
            '</div>';
    }
}


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
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
| REQUIRE SPECIFIC CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('requireValidCsrf')) {

    function requireValidCsrf(
        ?string $token
    ): void {

        Csrf::requireValid($token);
    }
}


/*
|--------------------------------------------------------------------------
| ROTATE CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('rotateCsrf')) {

    function rotateCsrf(): string
    {
        return Csrf::rotate();
    }
}


/*
|--------------------------------------------------------------------------
| DESTROY CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('destroyCsrf')) {

    function destroyCsrf(): void
    {
        Csrf::destroy();
    }
}


/*
|--------------------------------------------------------------------------
| PASSWORD HASHING
|--------------------------------------------------------------------------
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
| PASSWORD VALIDATION
|--------------------------------------------------------------------------
*/

if (!function_exists('validatePassword')) {

    function validatePassword(
        string $password
    ): array {

        return Security::validatePassword(
            $password
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
| INTEGER VALUE
|--------------------------------------------------------------------------
*/

if (!function_exists('intValue')) {

    function intValue(
        mixed $value,
        int $default = 0
    ): int {

        return Security::intValue(
            $value,
            $default
        );
    }
}


/*
|--------------------------------------------------------------------------
| KENYAN PHONE NORMALIZATION
|--------------------------------------------------------------------------
|
| Supported:
|
| 0712345678
| 0112345678
| 254712345678
| 254112345678
| +254712345678
| +254112345678
|
| Returns E.164-style +254... or an empty string if invalid.
|
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
| VALIDATE KENYAN PHONE
|--------------------------------------------------------------------------
*/

if (!function_exists('validKenyanPhone')) {

    function validKenyanPhone(
        string $phone
    ): bool {

        return normalizeKenyanPhone($phone) !== '';
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
| KENYAN CURRENCY FORMAT
|--------------------------------------------------------------------------
*/

if (!function_exists('kes')) {

    function kes(
        float|int|string $amount
    ): string {

        return 'KES ' . money($amount);
    }
}


/*
|--------------------------------------------------------------------------
| DATABASE TRANSACTION - BEGIN
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


/*
|--------------------------------------------------------------------------
| DATABASE TRANSACTION - COMMIT
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| DATABASE TRANSACTION - ROLLBACK
|--------------------------------------------------------------------------
*/

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
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

if (!function_exists('database')) {

    function database(): ?PDO
    {
        global $pdo;

        if (
            isset($pdo) &&
            $pdo instanceof PDO
        ) {
            return $pdo;
        }

        return null;
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
        $pdo = database();

        if ($pdo === null) {
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
| LOGOUT USER
|--------------------------------------------------------------------------
|
| This helper performs a complete local logout.
| Authentication-specific audit logging belongs in
| App\Auth\Login::logout() / Audit.php.
|
*/

if (!function_exists('logoutUser')) {

    function logoutUser(): void
    {
        Security::logout();
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT REQUEST URI
|--------------------------------------------------------------------------
*/

if (!function_exists('currentUrl')) {

    function currentUrl(): string
    {
        return (string) (
            $_SERVER['REQUEST_URI'] ?? '/'
        );
    }
}


/*
|--------------------------------------------------------------------------
| INTENDED URL
|--------------------------------------------------------------------------
*/

if (!function_exists('intendedUrl')) {

    function intendedUrl(
        string $default = '/dashboard.php'
    ): string {

        $url = $_SESSION['intended_url'] ?? null;

        unset($_SESSION['intended_url']);

        if (
            !is_string($url) ||
            $url === '' ||
            !str_starts_with($url, '/') ||
            str_starts_with($url, '//')
        ) {
            return $default;
        }

        return $url;
    }
}


/*
|--------------------------------------------------------------------------
| JSON RESPONSE
|--------------------------------------------------------------------------
*/

if (!function_exists('jsonResponse')) {

    function jsonResponse(
        array $data,
        int $status = 200
    ): never {

        if (!headers_sent()) {

            http_response_code($status);

            header(
                'Content-Type: application/json; charset=UTF-8'
            );

            header(
                'X-Content-Type-Options: nosniff'
            );
        }

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_INVALID_UTF8_SUBSTITUTE
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

if (!function_exists('securityHeaders')) {

    function securityHeaders(): void
    {
        Security::securityHeaders();
    }
}
