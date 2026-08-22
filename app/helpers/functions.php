<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Shared Helper Functions
 * ============================================================
 *
 * Author: Joseph Mbui
 * Copyright: © 2026 ThinkPlus Cloud
 *
 * File:
 * app/helpers/functions.php
 *
 * Responsibilities:
 * - Database bootstrap
 * - Session helpers
 * - Authentication helpers
 * - Role authorization
 * - Tenant helpers
 * - HTML escaping
 * - Input helpers
 * - Flash messages
 * - Redirects
 * - CSRF bridge
 * - Password helpers
 * - Kenyan phone normalization
 * - Database transaction helpers
 * - Database health checks
 *
 * Security classes are centralized in:
 *
 * security/Security.php
 * security/Csrf.php
 * security/Audit.php
 * security/Tenant.php
 *
 * ============================================================
 */


/*
|--------------------------------------------------------------------------
| LOAD DATABASE
|--------------------------------------------------------------------------
|
| functions.php:
|
| app/helpers/functions.php
|
| db.php:
|
| app/config/db.php
|
*/

require_once dirname(__DIR__) . '/config/db.php';


/*
|--------------------------------------------------------------------------
| LOAD SECURITY CLASSES
|--------------------------------------------------------------------------
|
| These are loaded here so pages using the shared helpers can
| also use the centralized security layer.
|
*/

require_once dirname(__DIR__, 2) . '/security/Security.php';
require_once dirname(__DIR__, 2) . '/security/Csrf.php';
require_once dirname(__DIR__, 2) . '/security/Audit.php';
require_once dirname(__DIR__, 2) . '/security/Tenant.php';


/*
|--------------------------------------------------------------------------
| START SECURE SESSION
|--------------------------------------------------------------------------
|
| Security.php owns the secure session configuration.
|
*/

if (
    class_exists(\Security\Security::class)
) {
    \Security\Security::startSecureSession();
} elseif (
    session_status() === PHP_SESSION_NONE
) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| SANITIZE INPUT
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| POST INPUT
|--------------------------------------------------------------------------
*/

function post(
    string $key,
    string $default = ''
): string {
    if (!isset($_POST[$key])) {
        return $default;
    }

    return sanitize($_POST[$key]);
}


/*
|--------------------------------------------------------------------------
| GET INPUT
|--------------------------------------------------------------------------
*/

function get(
    string $key,
    string $default = ''
): string {
    if (!isset($_GET[$key])) {
        return $default;
    }

    return sanitize($_GET[$key]);
}


/*
|--------------------------------------------------------------------------
| LOGIN STATUS
|--------------------------------------------------------------------------
*/

function isLoggedIn(): bool
{
    if (
        !isset($_SESSION['user_id'])
    ) {
        return false;
    }

    if (
        !is_numeric($_SESSION['user_id'])
    ) {
        return false;
    }

    return (int) $_SESSION['user_id'] > 0;
}


/*
|--------------------------------------------------------------------------
| CURRENT USER ID
|--------------------------------------------------------------------------
*/

function currentUserId(): ?int
{
    if (!isLoggedIn()) {
        return null;
    }

    return (int) $_SESSION['user_id'];
}


/*
|--------------------------------------------------------------------------
| CURRENT SCHOOL / TENANT ID
|--------------------------------------------------------------------------
*/

function currentSchoolId(): ?int
{
    /*
     * Prefer the authenticated school_id.
     */
    if (
        isset($_SESSION['school_id']) &&
        is_numeric($_SESSION['school_id'])
    ) {
        $schoolId = (int) $_SESSION['school_id'];

        if ($schoolId > 0) {
            return $schoolId;
        }
    }

    /*
     * Fall back to the active tenant.
     */
    if (
        isset($_SESSION['_tenant_school_id']) &&
        is_numeric($_SESSION['_tenant_school_id'])
    ) {
        $schoolId = (int) $_SESSION['_tenant_school_id'];

        if ($schoolId > 0) {
            return $schoolId;
        }
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| CURRENT USER
|--------------------------------------------------------------------------
*/

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
                failed_attempts,
                locked_until,
                last_login_at,
                created_at,
                updated_at
             FROM users
             WHERE id = ?
             LIMIT 1'
        );

        $stmt->execute([
            $userId
        ]);

        $user = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if (!$user) {
            return null;
        }

        /*
         * Do not treat inactive users as authenticated.
         */
        if (
            isset($user['status']) &&
            $user['status'] !== 'active'
        ) {
            return null;
        }

        return $user;

    } catch (Throwable $e) {

        error_log(
            'ThinkPlus currentUser error: ' .
            $e->getMessage()
        );

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| CURRENT ROLE
|--------------------------------------------------------------------------
*/

function currentRole(): ?string
{
    /*
     * Session role is useful and avoids an unnecessary
     * database query when it is already authenticated.
     */
    if (
        isset($_SESSION['role']) &&
        is_string($_SESSION['role']) &&
        $_SESSION['role'] !== ''
    ) {
        return $_SESSION['role'];
    }

    $user = currentUser();

    if (!$user) {
        return null;
    }

    return isset($user['role'])
        ? (string) $user['role']
        : null;
}


/*
|--------------------------------------------------------------------------
| ROLE CHECK
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| TENANT CHECK
|--------------------------------------------------------------------------
|
| Prevent application code from accidentally operating
| outside the authenticated school.
|
*/

function belongsToCurrentSchool(
    int $schoolId
): bool {

    $currentSchoolId =
        currentSchoolId();

    if ($currentSchoolId === null) {
        return false;
    }

    return $currentSchoolId === $schoolId;
}


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

function redirect(
    string $url
): never {

    header(
        'Location: ' . $url,
        true,
        302
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| REQUIRE LOGIN
|--------------------------------------------------------------------------
*/

function requireLogin(
    string $loginUrl = '/login.php'
): void {

    if (isLoggedIn()) {
        return;
    }

    setFlash(
        'Please log in to continue.',
        'warning'
    );

    redirect($loginUrl);
}


/*
|--------------------------------------------------------------------------
| REQUIRE ROLE
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

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

    if (
        !in_array(
            $type,
            $allowedTypes,
            true
        )
    ) {
        $type = 'info';
    }

    $_SESSION['message'] =
        $message;

    $_SESSION['message_type'] =
        $type;
}


/*
|--------------------------------------------------------------------------
| SHOW FLASH MESSAGE
|--------------------------------------------------------------------------
*/

function showMessage(): void
{
    if (
        !isset($_SESSION['message'])
    ) {
        return;
    }

    $message = e(
        (string) $_SESSION['message']
    );

    $type =
        $_SESSION['message_type'] ??
        'success';

    $allowedTypes = [
        'success',
        'danger',
        'warning',
        'info'
    ];

    if (
        !in_array(
            $type,
            $allowedTypes,
            true
        )
    ) {
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


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Do not create a second CSRF implementation here.
|
| The canonical implementation is:
|
| security/Csrf.php
|
*/

function csrfToken(): string
{
    return \Security\Csrf::getToken();
}


/*
|--------------------------------------------------------------------------
| CSRF FORM FIELD
|--------------------------------------------------------------------------
*/

function csrfField(): string
{
    return \Security\Csrf::field();
}


/*
|--------------------------------------------------------------------------
| VERIFY CSRF TOKEN
|--------------------------------------------------------------------------
*/

function verifyCsrfToken(
    ?string $token
): bool {

    return \Security\Csrf::verify(
        $token
    );
}


/*
|--------------------------------------------------------------------------
| REQUIRE CSRF
|--------------------------------------------------------------------------
*/

function requireCsrf(): void
{
    $token = null;

    /*
     * Standard HTML form.
     */
    if (
        isset($_POST['_csrf'])
    ) {
        $token =
            (string) $_POST['_csrf'];
    }

    /*
     * AJAX/API request.
     */
    if (
        $token === null &&
        isset($_SERVER['HTTP_X_CSRF_TOKEN'])
    ) {
        $token =
            (string) $_SERVER[
                'HTTP_X_CSRF_TOKEN'
            ];
    }

    \Security\Csrf::requireValid(
        $token
    );
}


/*
|--------------------------------------------------------------------------
| VERIFY CURRENT REQUEST
|--------------------------------------------------------------------------
*/

function verifyCsrfRequest(): void
{
    \Security\Csrf::verifyRequest();
}


/*
|--------------------------------------------------------------------------
| PASSWORD HASH
|--------------------------------------------------------------------------
|
| Security.php is the canonical security layer.
|
*/

function hashPassword(
    string $password
): string {

    return \Security\Security::hashPassword(
        $password
    );
}


/*
|--------------------------------------------------------------------------
| PASSWORD VERIFICATION
|--------------------------------------------------------------------------
*/

function verifyPassword(
    string $password,
    string $hash
): bool {

    return \Security\Security::verifyPassword(
        $password,
        $hash
    );
}


/*
|--------------------------------------------------------------------------
| PASSWORD REHASH CHECK
|--------------------------------------------------------------------------
*/

function passwordNeedsRehash(
    string $hash
): bool {

    return \Security\Security::needsRehash(
        $hash
    );
}


/*
|--------------------------------------------------------------------------
| EMAIL VALIDATION
|--------------------------------------------------------------------------
*/

function validEmail(
    string $email
): bool {

    return \Security\Security::isValidEmail(
        $email
    );
}


/*
|--------------------------------------------------------------------------
| KENYAN PHONE NORMALIZATION
|--------------------------------------------------------------------------
*/

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
     */
    if (
        preg_match(
            '/^\+254(7\d{8}|1\d{8})$/',
            $phone
        )
    ) {
        return $phone;
    }

    /*
     * 254712345678
     */
    if (
        preg_match(
            '/^254(7\d{8}|1\d{8})$/',
            $phone
        )
    ) {
        return '+' . $phone;
    }

    /*
     * 0712345678 / 0112345678
     */
    if (
        preg_match(
            '/^0(7\d{8}|1\d{8})$/',
            $phone
        )
    ) {
        return '+254' .
            substr(
                $phone,
                1
            );
    }

    return '';
}


/*
|--------------------------------------------------------------------------
| INTEGER VALUE
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| MONEY FORMAT
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| DATABASE TRANSACTION
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| COMMIT TRANSACTION
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| ROLLBACK TRANSACTION
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| LOGOUT USER
|--------------------------------------------------------------------------
|
| Prefer the centralized Security logout implementation.
|
*/

function logoutUser(): void
{
    \Security\Security::logout();
}


/*
|--------------------------------------------------------------------------
| DATABASE HEALTH CHECK
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| DATABASE ACCESSOR
|--------------------------------------------------------------------------
*/

function database(): PDO
{
    global $pdo;

    if (
        !isset($pdo) ||
        !$pdo instanceof PDO
    ) {
        throw new RuntimeException(
            'ThinkPlus database connection is unavailable.'
        );
    }

    return $pdo;
}
