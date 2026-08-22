<?php

declare(strict_types=1);

namespace Security;

/**
 * ============================================================
 * THINKPLUS CLOUD
 * CSRF Protection
 * ============================================================
 *
 * Phase 3: Authentication & Security
 *
 * Founder: Joseph Mbui
 * Location: Mariakani, Kilifi, Kenya
 *
 * File:
 * security/Csrf.php
 *
 * Description:
 * Central CSRF protection for ThinkPlus Cloud forms and
 * state-changing HTTP requests.
 *
 * ============================================================
 */

class Csrf
{
    /*
    |--------------------------------------------------------------------------
    | SESSION KEYS
    |--------------------------------------------------------------------------
    */

    private const TOKEN_KEY = '_csrf_token';

    private const TOKEN_TIME_KEY = '_csrf_time';


    /*
    |--------------------------------------------------------------------------
    | TOKEN TIMEOUT
    |--------------------------------------------------------------------------
    |
    | CSRF tokens expire after one hour.
    |
    */

    private const TIMEOUT = 3600;


    /*
    |--------------------------------------------------------------------------
    | GENERATE TOKEN
    |--------------------------------------------------------------------------
    */

    public static function generate(): string
    {
        Security::startSecureSession();

        $token = Security::randomToken(32);

        $_SESSION[self::TOKEN_KEY] = $token;
        $_SESSION[self::TOKEN_TIME_KEY] = time();

        return $token;
    }


    /*
    |--------------------------------------------------------------------------
    | GET CURRENT TOKEN
    |--------------------------------------------------------------------------
    */

    public static function getToken(): string
    {
        Security::startSecureSession();

        $token = $_SESSION[self::TOKEN_KEY] ?? null;

        $time = isset($_SESSION[self::TOKEN_TIME_KEY])
            ? (int) $_SESSION[self::TOKEN_TIME_KEY]
            : 0;

        /*
         * Generate a token if none exists.
         */
        if (
            !is_string($token) ||
            $token === ''
        ) {
            return self::generate();
        }

        /*
         * Generate a new token if the existing
         * token has expired.
         */
        if (
            $time <= 0 ||
            (time() - $time) > self::TIMEOUT
        ) {
            return self::generate();
        }

        return $token;
    }


    /*
    |--------------------------------------------------------------------------
    | HTML FORM FIELD
    |--------------------------------------------------------------------------
    */

    public static function field(): string
    {
        $token = self::getToken();

        return
            '<input type="hidden" ' .
            'name="_csrf" ' .
            'value="' .
            Security::e($token) .
            '">';
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY TOKEN
    |--------------------------------------------------------------------------
    */

    public static function verify(
        ?string $token
    ): bool {

        Security::startSecureSession();

        $stored = $_SESSION[self::TOKEN_KEY] ?? null;

        $time = isset($_SESSION[self::TOKEN_TIME_KEY])
            ? (int) $_SESSION[self::TOKEN_TIME_KEY]
            : 0;

        /*
         * Token must exist.
         */
        if (
            !is_string($stored) ||
            $stored === ''
        ) {
            return false;
        }

        /*
         * Submitted token must exist.
         */
        if (
            $token === null ||
            $token === ''
        ) {
            return false;
        }

        /*
         * Token timestamp must be valid.
         */
        if ($time <= 0) {
            return false;
        }

        /*
         * Token must not be expired.
         */
        if (
            (time() - $time) > self::TIMEOUT
        ) {
            return false;
        }

        /*
         * Constant-time comparison.
         */
        return hash_equals(
            $stored,
            $token
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REQUIRE VALID CSRF TOKEN
    |--------------------------------------------------------------------------
    */

    public static function requireValid(
        ?string $token
    ): void {

        if (!self::verify($token)) {

            http_response_code(419);

            exit(
                'CSRF token mismatch. ' .
                'Please refresh the page and try again.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY CURRENT REQUEST
    |--------------------------------------------------------------------------
    |
    | GET/HEAD/OPTIONS requests are not normally state-changing.
    | POST, PUT, PATCH and DELETE requests require CSRF protection.
    |
    */

    public static function verifyRequest(): void
    {
        $method = strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );

        $protectedMethods = [
            'POST',
            'PUT',
            'PATCH',
            'DELETE'
        ];

        if (!in_array(
            $method,
            $protectedMethods,
            true
        )) {
            return;
        }

        /*
         * Standard form token.
         */
        $token = null;

        if (isset($_POST['_csrf'])) {
            $token = (string) $_POST['_csrf'];
        }

        /*
         * API/AJAX token.
         */
        if (
            $token === null &&
            isset($_SERVER['HTTP_X_CSRF_TOKEN'])
        ) {
            $token = (string)
                $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        self::requireValid($token);
    }


    /*
    |--------------------------------------------------------------------------
    | ROTATE TOKEN
    |--------------------------------------------------------------------------
    |
    | Useful after authentication or other sensitive actions.
    |
    */

    public static function rotate(): string
    {
        return self::generate();
    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE TOKEN
    |--------------------------------------------------------------------------
    */

    public static function destroy(): void
    {
        Security::startSecureSession();

        unset(
            $_SESSION[self::TOKEN_KEY],
            $_SESSION[self::TOKEN_TIME_KEY]
        );
    }
}
