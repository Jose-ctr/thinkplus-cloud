<?php
declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Tenant Security
 * ============================================================
 *
 * Phase 3: Authentication & Security
 *
 * File:
 * security/Tenant.php
 *
 * Description:
 * Centralized multi-tenant security for ThinkPlus Cloud.
 *
 * Every school operates as an independent tenant.
 * Tenant access is determined from the authenticated user,
 * never from an untrusted school_id supplied by a request.
 *
 * ============================================================
 */

namespace Security;

use PDO;
use RuntimeException;
use Throwable;

class Tenant
{
    /**
     * Session key used to store the active school ID.
     */
    private const SESSION_KEY = '_tenant_school_id';


    /*
    |--------------------------------------------------------------------------
    | GET CURRENT SCHOOL ID
    |--------------------------------------------------------------------------
    */

    public static function id(): ?int
    {
        Security::startSecureSession();

        /*
         * Prefer the authenticated user's school_id.
         * This prevents a user from changing the tenant by
         * modifying a request parameter.
         */
        $userSchoolId = self::authenticatedUserSchoolId();

        if ($userSchoolId !== null) {
            $_SESSION[self::SESSION_KEY] = $userSchoolId;

            return $userSchoolId;
        }

        /*
         * If there is no authenticated user, there is no
         * active school tenant.
         */
        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | REQUIRE CURRENT TENANT
    |--------------------------------------------------------------------------
    */

    public static function requireId(): int
    {
        $schoolId = self::id();

        if ($schoolId === null || $schoolId <= 0) {
            http_response_code(403);

            exit('Tenant access denied.');
        }

        return $schoolId;
    }


    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED USER SCHOOL
    |--------------------------------------------------------------------------
    */

    private static function authenticatedUserSchoolId(): ?int
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $userId = filter_var(
            $_SESSION['user_id'],
            FILTER_VALIDATE_INT
        );

        if ($userId === false || $userId <= 0) {
            return null;
        }

        $pdo = self::database();

        if ($pdo === null) {
            return null;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT school_id
                 FROM users
                 WHERE id = ?
                 AND status = ?
                 LIMIT 1'
            );

            $stmt->execute([
                $userId,
                'active'
            ]);

            $schoolId = $stmt->fetchColumn();

            if ($schoolId === false || $schoolId === null) {
                return null;
            }

            $schoolId = filter_var(
                $schoolId,
                FILTER_VALIDATE_INT
            );

            if ($schoolId === false || $schoolId <= 0) {
                return null;
            }

            return (int) $schoolId;

        } catch (Throwable $e) {

            error_log(
                'ThinkPlus Tenant user lookup error: ' .
                $e->getMessage()
            );

            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK TENANT
    |--------------------------------------------------------------------------
    */

    public static function check(?int $schoolId = null): bool
    {
        $currentSchoolId = self::id();

        if ($currentSchoolId === null) {
            return false;
        }

        /*
         * If no school ID is supplied, simply verify that
         * the authenticated user has a valid tenant.
         */
        if ($schoolId === null) {
            return true;
        }

        return $currentSchoolId === $schoolId;
    }


    /*
    |--------------------------------------------------------------------------
    | REQUIRE TENANT
    |--------------------------------------------------------------------------
    */

    public static function require(?int $schoolId = null): void
    {
        if (!self::check($schoolId)) {
            http_response_code(403);

            exit('You are not authorized to access this school.');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE REQUESTED SCHOOL
    |--------------------------------------------------------------------------
    */

    public static function validateRequestedSchool(
        mixed $requestedSchoolId
    ): int
    {
        $currentSchoolId = self::requireId();

        /*
         * Never trust the requested school_id.
         * If supplied, it must match the authenticated tenant.
         */
        if ($requestedSchoolId === null || $requestedSchoolId === '') {
            return $currentSchoolId;
        }

        $requestedSchoolId = filter_var(
            $requestedSchoolId,
            FILTER_VALIDATE_INT
        );

        if (
            $requestedSchoolId === false ||
            $requestedSchoolId <= 0
        ) {
            http_response_code(403);

            exit('Invalid tenant.');
        }

        if ((int) $requestedSchoolId !== $currentSchoolId) {
            self::deny();
        }

        return $currentSchoolId;
    }


    /*
    |--------------------------------------------------------------------------
    | APPLY TENANT TO QUERY PARAMETERS
    |--------------------------------------------------------------------------
    */

    public static function queryParams(
        array $params = []
    ): array {
        $params['school_id'] = self::requireId();

        return $params;
    }


    /*
    |--------------------------------------------------------------------------
    | TENANT WHERE CLAUSE
    |--------------------------------------------------------------------------
    */

    public static function where(
        string $column = 'school_id'
    ): array {
        return [
            'sql' => $column . ' = :tenant_school_id',
            'params' => [
                ':tenant_school_id' => self::requireId()
            ]
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY RECORD TENANT
    |--------------------------------------------------------------------------
    */

    public static function owns(
        string $table,
        int $recordId,
        string $idColumn = 'id',
        string $tenantColumn = 'school_id'
    ): bool {
        if ($recordId <= 0) {
            return false;
        }

        /*
         * Only allow safe SQL identifier characters.
         * Table and column names cannot be bound with PDO.
         */
        if (
            !self::validIdentifier($table) ||
            !self::validIdentifier($idColumn) ||
            !self::validIdentifier($tenantColumn)
        ) {
            return false;
        }

        $pdo = self::database();

        if ($pdo === null) {
            return false;
        }

        try {
            $sql = sprintf(
                'SELECT 1
                 FROM `%s`
                 WHERE `%s` = ?
                 AND `%s` = ?
                 LIMIT 1',
                $table,
                $idColumn,
                $tenantColumn
            );

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $recordId,
                self::requireId()
            ]);

            return $stmt->fetchColumn() !== false;

        } catch (Throwable $e) {

            error_log(
                'ThinkPlus Tenant ownership check error: ' .
                $e->getMessage()
            );

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REQUIRE RECORD OWNERSHIP
    |--------------------------------------------------------------------------
    */

    public static function requireOwnership(
        string $table,
        int $recordId,
        string $idColumn = 'id',
        string $tenantColumn = 'school_id'
    ): void {
        if (
            !self::owns(
                $table,
                $recordId,
                $idColumn,
                $tenantColumn
            )
        ) {
            self::deny();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GET TENANT-SCOPED SQL
    |--------------------------------------------------------------------------
    */

    public static function scope(
        string $sql,
        string $column = 'school_id'
    ): array {
        $tenant = self::where($column);

        return [
            'sql' => $sql . ' AND ' . $tenant['sql'],
            'params' => $tenant['params']
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SET ACTIVE TENANT
    |--------------------------------------------------------------------------
    |
    | This method is intentionally restricted.
    |
    | A normal school user must NOT be able to switch to
    | another school simply by supplying a school ID.
    |
    */

    public static function set(int $schoolId): void
    {
        if ($schoolId <= 0) {
            self::deny();
        }

        $currentSchoolId = self::authenticatedUserSchoolId();

        if (
            $currentSchoolId === null ||
            $currentSchoolId !== $schoolId
        ) {
            self::deny();
        }

        Security::startSecureSession();

        $_SESSION[self::SESSION_KEY] = $schoolId;
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR TENANT
    |--------------------------------------------------------------------------
    */

    public static function clear(): void
    {
        Security::startSecureSession();

        unset(
            $_SESSION[self::SESSION_KEY]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | IS AUTHENTICATED TENANT
    |--------------------------------------------------------------------------
    */

    public static function authenticated(): bool
    {
        return self::id() !== null;
    }


    /*
    |--------------------------------------------------------------------------
    | DENY ACCESS
    |--------------------------------------------------------------------------
    */

    public static function deny(
        string $message = 'Tenant access denied.'
    ): never {
        http_response_code(403);

        exit($message);
    }


    /*
    |--------------------------------------------------------------------------
    | DATABASE CONNECTION
    |--------------------------------------------------------------------------
    */

    private static function database(): ?PDO
    {
        global $pdo;

        if (
            isset($pdo) &&
            $pdo instanceof PDO
        ) {
            return $pdo;
        }

        /*
         * Try the project's database configuration.
         */
        $databaseFile = dirname(__DIR__) . '/config/database.php';

        if (is_file($databaseFile)) {
            require_once $databaseFile;
        }

        global $pdo;

        if (
            isset($pdo) &&
            $pdo instanceof PDO
        ) {
            return $pdo;
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE SQL IDENTIFIER
    |--------------------------------------------------------------------------
    */

    private static function validIdentifier(
        string $identifier
    ): bool {
        return preg_match(
            '/^[A-Za-z_][A-Za-z0-9_]*$/',
            $identifier
        ) === 1;
    }
}
