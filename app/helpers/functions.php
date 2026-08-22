<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Helper Functions
 * ============================================================
 *
 * Author: Joseph Mbui
 * Copyright: © 2026 ThinkPlus Cloud
 *
 * File:
 * app/helpers/functions.php
 *
 * Description:
 * Shared authentication, security, validation, session,
 * formatting and database helper functions.
 *
 * ============================================================
 */


/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
|
| functions.php is located at:
|
| app/helpers/functions.php
|
| Therefore the project root is two levels above this file.
|
*/

require_once dirname(__DIR__, 2) . '/config/database.php';


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML OUTPUT
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
    return isset($_SESSION['user_id'])
        && is_numeric($_SESSION['user_id'])
        && (int) $_SESSION['user_id'] > 0;
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

    if (!isset($pdo) || !$pdo instanceof PDO) {
        return null;
    }

    try {

        $stmt = $pdo->prepare(
            'SELECT
                id,
                school_id,
                role,
                name,
                email,
                phone,
                status,
                last_login_at,
                created_at
             FROM users
             WHERE id = ?
             AND status = "active"
             LIMIT 1'
        );

        $stmt->execute([
            $userId
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;

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

function hasRole(string|array $roles): bool
{
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
| CURRENT SCHOOL ID
|--------------------------------------------------------------------------
*/

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

    return (int) $user['school_id'];
}


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

function redirect(string $url): never
{
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

    if (!isLoggedIn()) {

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


/*
|--------------------------------------------------------------------------
| SHOW FLASH MESSAGE
|--------------------------------------------------------------------------
*/

function showMessage(): void
{
    if (!isset($_SESSION['message'])) {
        return;
    }

    $message = e(
        (string) $_SESSION['message']
    );

    $type = $_SESSION['message_type'] ?? 'success';

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


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

function csrfToken(): string
{
    if (
        empty($_SESSION['csrf_token']) ||
        !is_string($_SESSION['csrf_token'])
    ) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}


/*
|--------------------------------------------------------------------------
| CSRF FORM FIELD
|--------------------------------------------------------------------------
*/

function csrfField(): string
{
    return
        '<input type="hidden" name="csrf_token" value="' .
        e(csrfToken()) .
        '">';
}


/*
|--------------------------------------------------------------------------
| VERIFY CSRF TOKEN
|--------------------------------------------------------------------------
*/

function verifyCsrfToken(
    ?string $token
): bool {

    if (
        $token === null ||
        !isset($_SESSION['csrf_token']) ||
        !is_string($_SESSION['csrf_token'])
    ) {
        return false;
    }

    return hash_equals(
        $_SESSION['csrf_token'],
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
    $token = isset($_POST['csrf_token'])
        ? (string) $_POST['csrf_token']
        : null;

    if (!verifyCsrfToken($token)) {

        http_response_code(419);

        exit(
            'Security verification failed. Please go back and try again.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| PASSWORD HASHING
|--------------------------------------------------------------------------
*/

function hashPassword(string $password): string
{
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

function verifyPassword(
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
| EMAIL VALIDATION
|--------------------------------------------------------------------------
*/

function validEmail(string $email): bool
{
    return filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ) !== false;
}


/*
|--------------------------------------------------------------------------
| KENYAN PHONE NORMALIZATION
|--------------------------------------------------------------------------
*/

function normalizeKenyanPhone(string $phone): string
{
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
    if (preg_match(
        '/^\+254(7\d{8}|1\d{8})$/',
        $phone
    )) {
        return $phone;
    }

    /*
     * 254712345678
     */
    if (preg_match(
        '/^254(7\d{8}|1\d{8})$/',
        $phone
    )) {
        return '+' . $phone;
    }

    /*
     * 0712345678 / 0112345678
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


/*
|--------------------------------------------------------------------------
| INTEGER VALIDATION
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
| LOGOUT
|--------------------------------------------------------------------------
*/

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]
        );
    }

    session_destroy();
}


/*
|--------------------------------------------------------------------------
| DATABASE HEALTH CHECK
|--------------------------------------------------------------------------
*/

function databaseIsHealthy(): bool
{
    global $pdo;

    if (!isset($pdo) || !$pdo instanceof PDO) {
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
