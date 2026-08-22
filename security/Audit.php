<?php
declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Audit Logging
 * ============================================================
 *
 * Phase 3: Authentication & Security
 *
 * File:
 * security/Audit.php
 *
 * Description:
 * Centralized audit logging for security-sensitive and
 * administrative actions.
 *
 * Matches:
 * database/schema.sql v3.0
 *
 * ============================================================
 */

namespace Security;

use PDO;
use Throwable;

class Audit
{
    /*
    |--------------------------------------------------------------------------
    | MAIN AUDIT LOGGER
    |--------------------------------------------------------------------------
    */

    public static function log(
        PDO $pdo,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): bool {
        try {
            Security::startSecureSession();

            /*
             * Authenticated user.
             */
            $userId = null;

            if (
                isset($_SESSION['user_id']) &&
                is_numeric($_SESSION['user_id'])
            ) {
                $userId = (int) $_SESSION['user_id'];
            }

            /*
             * Tenant MUST come from Tenant.php.
             * Never trust a school_id supplied by a request.
             */
            $schoolId = Tenant::id();

            /*
             * Safely encode audit values.
             */
            $oldJson = self::encodeValues($oldValues);
            $newJson = self::encodeValues($newValues);

            /*
             * Client information.
             */
            $ipAddress = self::clientIp();

            $userAgent = substr(
                (string) (
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ),
                0,
                500
            );

            $stmt = $pdo->prepare(
                'INSERT INTO audit_logs (
                    public_id,
                    school_id,
                    user_id,
                    action,
                    entity_type,
                    entity_id,
                    old_values,
                    new_values,
                    description,
                    ip_address,
                    user_agent,
                    created_at
                ) VALUES (
                    :public_id,
                    :school_id,
                    :user_id,
                    :action,
                    :entity_type,
                    :entity_id,
                    :old_values,
                    :new_values,
                    :description,
                    :ip_address,
                    :user_agent,
                    NOW()
                )'
            );

            return $stmt->execute([
                ':public_id' => Security::publicId(),
                ':school_id' => $schoolId,
                ':user_id' => $userId,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':old_values' => $oldJson,
                ':new_values' => $newJson,
                ':description' => $description,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent
            ]);

        } catch (Throwable $e) {

            /*
             * Audit failure must never expose database details
             * to the user.
             */
            error_log(
                'ThinkPlus Audit failed: ' .
                $e->getMessage()
            );

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    public static function login(
        PDO $pdo,
        int $userId
    ): bool {
        return self::log(
            $pdo,
            'login',
            'user',
            $userId,
            null,
            null,
            'User logged in'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public static function logout(
        PDO $pdo,
        int $userId
    ): bool {
        return self::log(
            $pdo,
            'logout',
            'user',
            $userId,
            null,
            null,
            'User logged out'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FAILED LOGIN
    |--------------------------------------------------------------------------
    */

    public static function failedLogin(
        PDO $pdo,
        string $email
    ): bool {
        /*
         * Store only the email involved in the attempt.
         *
         * NEVER store:
         * - passwords
         * - password hashes
         * - session tokens
         * - CSRF tokens
         */
        return self::log(
            $pdo,
            'failed_login',
            'user',
            null,
            null,
            [
                'email' => $email
            ],
            'Failed login attempt'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public static function create(
        PDO $pdo,
        string $type,
        int $id,
        array $data
    ): bool {
        return self::log(
            $pdo,
            'create',
            $type,
            $id,
            null,
            $data,
            "Created {$type} #{$id}"
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public static function update(
        PDO $pdo,
        string $type,
        int $id,
        array $old,
        array $new
    ): bool {
        return self::log(
            $pdo,
            'update',
            $type,
            $id,
            $old,
            $new,
            "Updated {$type} #{$id}"
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public static function delete(
        PDO $pdo,
        string $type,
        int $id,
        ?array $old = null
    ): bool {
        return self::log(
            $pdo,
            'delete',
            $type,
            $id,
            $old,
            null,
            "Deleted {$type} #{$id}"
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD CHANGE
    |--------------------------------------------------------------------------
    */

    public static function passwordChanged(
        PDO $pdo,
        int $userId
    ): bool {
        return self::log(
            $pdo,
            'password_changed',
            'user',
            $userId,
            null,
            null,
            'User password changed'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PERMISSION CHANGE
    |--------------------------------------------------------------------------
    */

    public static function permissionChanged(
        PDO $pdo,
        int $userId,
        array $old = [],
        array $new = []
    ): bool {
        return self::log(
            $pdo,
            'permission_changed',
            'user',
            $userId,
            $old,
            $new,
            "Permissions changed for user #{$userId}"
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SECURITY EVENT
    |--------------------------------------------------------------------------
    */

    public static function securityEvent(
        PDO $pdo,
        string $description,
        ?int $userId = null
    ): bool {
        return self::log(
            $pdo,
            'security_event',
            'security',
            $userId,
            null,
            null,
            $description
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT
    |--------------------------------------------------------------------------
    */

    public static function payment(
        PDO $pdo,
        int $paymentId,
        array $data = []
    ): bool {
        return self::log(
            $pdo,
            'payment',
            'payment',
            $paymentId,
            null,
            $data,
            "Payment recorded #{$paymentId}"
        );
    }


    /*
    |--------------------------------------------------------------------------
    | JSON ENCODING
    |--------------------------------------------------------------------------
    */

    private static function encodeValues(
        ?array $values
    ): ?string {
        if ($values === null) {
            return null;
        }

        try {
            return json_encode(
                $values,
                JSON_THROW_ON_ERROR |
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );

        } catch (Throwable $e) {

            error_log(
                'ThinkPlus Audit JSON error: ' .
                $e->getMessage()
            );

            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CLIENT IP
    |--------------------------------------------------------------------------
    */

    private static function clientIp(): string
    {
        /*
         * Do not blindly trust X-Forwarded-For.
         *
         * The direct REMOTE_ADDR is safer unless the application
         * is explicitly configured to trust a reverse proxy.
         */
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (
            filter_var(
                $ip,
                FILTER_VALIDATE_IP
            ) !== false
        ) {
            return $ip;
        }

        return '0.0.0.0';
    }
}
