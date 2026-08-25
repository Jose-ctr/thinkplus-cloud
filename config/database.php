<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Secure Database Configuration Layer
 * ============================================================
 *
 * Author: Joseph Mbui
 * Copyright: © 2026 ThinkPlus Cloud
 *
 * File:
 * config/database.php
 *
 * Purpose:
 * Secure, production-ready PDO database configuration for
 * the ThinkPlus Cloud multi-tenant SaaS platform.
 *
 * PHP:
 * 8.2+
 *
 * Security:
 * - Environment-based credentials
 * - Secure PDO configuration
 * - Native prepared statements
 * - Optional SSL/TLS
 * - Controlled connection retries
 * - Safe production error handling
 * - Backward-compatible $pdo connection
 * - Legacy DB_* constants
 *
 * IMPORTANT:
 * - Never hard-code production credentials.
 * - Never commit .env.
 * - Never expose database exceptions to users.
 * - Tenant authorization belongs to the security layer.
 *
 * ============================================================
 */


/*
|--------------------------------------------------------------------------
| Environment Loader
|--------------------------------------------------------------------------
|
| System environment variables always have priority.
| A local .env file is supported as a development fallback.
|
*/

function envValue(
    string $key,
    string|int|bool|null $default = null,
    string $type = 'string'
): string|int|bool|null {

    /*
     * ----------------------------------------------------------
     * 1. Check real environment variables first
     * ----------------------------------------------------------
     */

    $value = getenv($key);

    if ($value !== false) {
        return castEnvValue(
            $value,
            $type
        );
    }


    /*
     * ----------------------------------------------------------
     * 2. Load local .env only when necessary
     * ----------------------------------------------------------
     */

    static $env = null;

    if ($env === null) {

        $env = [];

        $envFile = dirname(__DIR__) . '/.env';

        if (
            is_file($envFile) &&
            is_readable($envFile)
        ) {

            $lines = file(
                $envFile,
                FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
            );

            if ($lines !== false) {

                foreach ($lines as $line) {

                    $line = trim($line);

                    /*
                     * Ignore empty lines and comments.
                     */

                    if (
                        $line === '' ||
                        str_starts_with($line, '#')
                    ) {
                        continue;
                    }

                    /*
                     * Require an assignment.
                     */

                    if (!str_contains($line, '=')) {
                        continue;
                    }

                    [
                        $name,
                        $envValue
                    ] = explode(
                        '=',
                        $line,
                        2
                    );

                    $name = trim($name);
                    $envValue = trim($envValue);

                    /*
                     * Validate environment variable name.
                     */

                    if (
                        !preg_match(
                            '/^[A-Z_][A-Z0-9_]*$/i',
                            $name
                        )
                    ) {
                        continue;
                    }

                    /*
                     * Remove matching quotes.
                     */

                    $length = strlen($envValue);

                    if ($length >= 2) {

                        $first = $envValue[0];
                        $last = $envValue[$length - 1];

                        if (
                            (
                                $first === '"' &&
                                $last === '"'
                            ) ||
                            (
                                $first === "'" &&
                                $last === "'"
                            )
                        ) {
                            $envValue = substr(
                                $envValue,
                                1,
                                -1
                            );
                        }
                    }

                    $env[$name] = $envValue;
                }
            }
        }
    }


    /*
     * ----------------------------------------------------------
     * 3. Use .env value if available
     * ----------------------------------------------------------
     */

    if (array_key_exists($key, $env)) {

        return castEnvValue(
            $env[$key],
            $type
        );
    }


    /*
     * ----------------------------------------------------------
     * 4. Return default
     * ----------------------------------------------------------
     */

    return $default;
}


/*
|--------------------------------------------------------------------------
| Environment Type Casting
|--------------------------------------------------------------------------
*/

function castEnvValue(
    string $value,
    string $type
): string|int|bool {

    return match ($type) {

        'int' => (int) $value,

        'bool' => in_array(
            strtolower(trim($value)),
            [
                '1',
                'true',
                'yes',
                'on'
            ],
            true
        ),

        default => $value,
    };
}
/*
|--------------------------------------------------------------------------
| Application Environment
|--------------------------------------------------------------------------
*/

$appEnv = (string) envValue(
    'APP_ENV',
    'development'
);


/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
*/

$dbHost = (string) envValue(
    'DB_HOST',
    '127.0.0.1'
);

$dbPort = (int) envValue(
    'DB_PORT',
    3306,
    'int'
);

$dbName = (string) envValue(
    'DB_NAME',
    'thinkplus_cloud'
);

$dbUser = (string) envValue(
    'DB_USER',
    'root'
);

$dbPass = (string) envValue(
    'DB_PASS',
    ''
);

$dbCharset = (string) envValue(
    'DB_CHARSET',
    'utf8mb4'
);


/*
|--------------------------------------------------------------------------
| Connection Settings
|--------------------------------------------------------------------------
*/

$dbTimeout = (int) envValue(
    'DB_TIMEOUT',
    10,
    'int'
);

$dbMaxRetries = (int) envValue(
    'DB_MAX_RETRIES',
    3,
    'int'
);

$dbRetryDelay = (int) envValue(
    'DB_RETRY_DELAY',
    1,
    'int'
);


/*
|--------------------------------------------------------------------------
| Optional Read Replica Configuration
|--------------------------------------------------------------------------
|
| These values are reserved for future read/write splitting.
| They are NOT used by the current connection.
|
*/

$dbReadHost = envValue(
    'DB_READ_HOST'
);

$dbReadPort = (int) envValue(
    'DB_READ_PORT',
    $dbPort,
    'int'
);


/*
|--------------------------------------------------------------------------
| SSL/TLS Configuration
|--------------------------------------------------------------------------
*/

$dbSslCa = envValue(
    'DB_SSL_CA'
);

$dbSslCert = envValue(
    'DB_SSL_CERT'
);

$dbSslKey = envValue(
    'DB_SSL_KEY'
);

$dbSslVerify = (bool) envValue(
    'DB_SSL_VERIFY',
    true,
    'bool'
);


/*
|--------------------------------------------------------------------------
| Configuration Validation
|--------------------------------------------------------------------------
*/

if ($dbPort < 1 || $dbPort > 65535) {

    throw new RuntimeException(
        'Invalid database port configuration.'
    );
}


if ($dbTimeout < 1) {

    $dbTimeout = 10;
}


if ($dbMaxRetries < 1) {

    $dbMaxRetries = 1;
}


if ($dbMaxRetries > 5) {

    $dbMaxRetries = 5;
}


if ($dbRetryDelay < 1) {

    $dbRetryDelay = 1;
}


/*
|--------------------------------------------------------------------------
| Production Configuration Validation
|--------------------------------------------------------------------------
|
| Production should never silently fall back to the default
| local database credentials.
|
*/

if (
    strtolower($appEnv) === 'production' &&
    (
        $dbHost === '127.0.0.1' ||
        $dbHost === 'localhost' ||
        $dbUser === 'root' ||
        $dbPass === ''
    )
) {

    error_log(
        '[ThinkPlus Cloud] Invalid production database configuration.'
    );

    http_response_code(503);

    exit(
        'Database service is not properly configured.'
    );
}


/*
|--------------------------------------------------------------------------
| SSL/TLS Validation
|--------------------------------------------------------------------------
*/

if (
    strtolower($appEnv) === 'production' &&
    !empty($dbSslCa) &&
    !is_file((string) $dbSslCa)
) {

    error_log(
        '[ThinkPlus Cloud] Configured DB_SSL_CA file was not found.'
    );

    http_response_code(503);

    exit(
        'Database security configuration is invalid.'
    );
}
/*
|--------------------------------------------------------------------------
| PDO Data Source Name
|--------------------------------------------------------------------------
|
| The charset is explicitly defined in the DSN to ensure that all
| database communication uses UTF-8 compatible encoding.
|
*/

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $dbHost,
    $dbPort,
    $dbName,
    $dbCharset
);


/*
|--------------------------------------------------------------------------
| Secure PDO Options
|--------------------------------------------------------------------------
*/

$options = [

    /*
     * Throw exceptions for database errors.
     */
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

    /*
     * Return database rows as associative arrays.
     */
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    /*
     * Use native MySQL prepared statements.
     */
    PDO::ATTR_EMULATE_PREPARES => false,

    /*
     * Persistent connections are deliberately disabled.
     *
     * ThinkPlus Cloud is a web SaaS application and connection
     * lifecycle should remain controlled by the PHP runtime.
     */
    PDO::ATTR_PERSISTENT => false,

    /*
     * Connection timeout.
     */
    PDO::ATTR_TIMEOUT => $dbTimeout,

    /*
     * Buffered queries improve compatibility with normal
     * request/response database operations.
     */
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
];


/*
|--------------------------------------------------------------------------
| MySQL Session Configuration
|--------------------------------------------------------------------------
|
| Apply secure SQL behaviour for each new connection.
|
| Charset is already specified in the DSN, so there is no
| redundant SET NAMES command here.
|
*/

$options[PDO::MYSQL_ATTR_INIT_COMMAND] =
    'SET SESSION sql_mode = "STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"';


/*
|--------------------------------------------------------------------------
| SSL/TLS Database Connection
|--------------------------------------------------------------------------
|
| TLS is enabled only when a CA certificate is configured.
|
| Production environments should use certificate verification.
|
*/

if (!empty($dbSslCa)) {

    $options[PDO::MYSQL_ATTR_SSL_CA] =
        (string) $dbSslCa;


    /*
     * Verify the database server certificate by default.
     */
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] =
        $dbSslVerify;


    /*
     * Optional client certificate.
     */
    if (!empty($dbSslCert)) {

        $options[PDO::MYSQL_ATTR_SSL_CERT] =
            (string) $dbSslCert;
    }


    /*
     * Optional client private key.
     */
    if (!empty($dbSslKey)) {

        $options[PDO::MYSQL_ATTR_SSL_KEY] =
            (string) $dbSslKey;
    }
}


/*
|--------------------------------------------------------------------------
| Connection Configuration Summary
|--------------------------------------------------------------------------
|
| Do NOT print this information to users.
| It is intentionally kept in memory for the current request.
|
*/

$databaseConfiguration = [
    'environment' => $appEnv,
    'host' => $dbHost,
    'port' => $dbPort,
    'database' => $dbName,
    'charset' => $dbCharset,
    'timeout' => $dbTimeout,
    'max_retries' => $dbMaxRetries,
    'ssl_enabled' => !empty($dbSslCa),
    'read_replica_configured' => !empty($dbReadHost),
];
/*
|--------------------------------------------------------------------------
| Database Connection Manager
|--------------------------------------------------------------------------
|
| Responsible for creating the PDO connection safely.
|
| Features:
| - Controlled retry attempts
| - Exponential backoff
| - No recursive connection attempts
| - Safe error logging
| - Single connection per PHP request
|
*/

final class DatabaseConnector
{
    /**
     * Create a PDO database connection.
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     * @param int $maxRetries
     * @param int $retryDelay
     *
     * @return PDO
     *
     * @throws PDOException
     */
    public static function connect(
        string $dsn,
        string $username,
        string $password,
        array $options,
        int $maxRetries = 3,
        int $retryDelay = 1
    ): PDO {

        $lastException = null;


        /*
         * Ensure safe retry limits.
         */

        $maxRetries = max(
            1,
            min($maxRetries, 5)
        );

        $retryDelay = max(
            1,
            min($retryDelay, 10)
        );


        /*
         * ------------------------------------------------------
         * Connection attempts
         * ------------------------------------------------------
         */

        for (
            $attempt = 1;
            $attempt <= $maxRetries;
            $attempt++
        ) {

            try {

                $connection = new PDO(
                    $dsn,
                    $username,
                    $password,
                    $options
                );


                /*
                 * Confirm the connection is usable.
                 */

                $connection->query(
                    'SELECT 1'
                );


                /*
                 * Connection successful.
                 */

                return $connection;

            } catch (PDOException $exception) {

                $lastException = $exception;


                /*
                 * Do not retry the final attempt.
                 */

                if ($attempt >= $maxRetries) {
                    break;
                }


                /*
                 * Exponential backoff:
                 *
                 * Attempt 1 → delay
                 * Attempt 2 → delay × 2
                 * Attempt 3 → delay × 4
                 */

                $delay = min(
                    $retryDelay * (2 ** ($attempt - 1)),
                    10
                );


                /*
                 * Log only safe diagnostic information.
                 *
                 * Credentials and SQL are never logged here.
                 */

                error_log(
                    sprintf(
                        '[ThinkPlus Cloud] Database connection attempt %d/%d failed. Retrying in %d second(s).',
                        $attempt,
                        $maxRetries,
                        $delay
                    )
                );


                /*
                 * Wait before retrying.
                 */

                sleep($delay);
            }
        }


        /*
         * All attempts failed.
         */

        if ($lastException instanceof PDOException) {

            throw $lastException;
        }


        /*
         * Defensive fallback.
         */

        throw new PDOException(
            'Unable to establish database connection.'
        );
    }
}
/*
|--------------------------------------------------------------------------
| Initialize Database Connection
|--------------------------------------------------------------------------
|
| The global $pdo variable is intentionally preserved.
|
| Existing ThinkPlus Cloud files may currently depend on:
|
|     require_once 'config/database.php';
|
| followed by:
|
|     $pdo->prepare(...);
|
| Therefore, this file must continue exposing $pdo globally.
|
*/

try {

    $pdo = DatabaseConnector::connect(
        $dsn,
        $dbUser,
        $dbPass,
        $options,
        $dbMaxRetries,
        $dbRetryDelay
    );

} catch (PDOException $exception) {

    /*
     * Log the failure without exposing credentials,
     * SQL statements, or the DSN to the user.
     */

    error_log(
        sprintf(
            '[ThinkPlus Cloud] Database initialization failed. PDO code: %s',
            (string) $exception->getCode()
        )
    );


    /*
     * Return a generic service-unavailable response.
     */

    if (!headers_sent()) {
        http_response_code(503);
    }


    /*
     * JSON response for API/AJAX requests.
     */

    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';

    if (
        str_contains(
            strtolower($acceptHeader),
            'application/json'
        )
    ) {

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        exit(
            json_encode(
                [
                    'error' => 'Service Temporarily Unavailable',
                    'message' => 'Database service is temporarily unavailable.',
                ],
                JSON_UNESCAPED_SLASHES
            )
        );
    }


    /*
     * Normal web response.
     */

    exit(
        'Database service is temporarily unavailable. Please try again later.'
    );
}


/*
|--------------------------------------------------------------------------
| Legacy Database Constants
|--------------------------------------------------------------------------
|
| These constants are preserved for older ThinkPlus Cloud files.
|
| New code should prefer configuration/environment services,
| but removing these immediately could break legacy files.
|
*/

if (!defined('DB_HOST')) {

    define(
        'DB_HOST',
        $dbHost
    );
}


if (!defined('DB_PORT')) {

    define(
        'DB_PORT',
        $dbPort
    );
}


if (!defined('DB_NAME')) {

    define(
        'DB_NAME',
        $dbName
    );
}


if (!defined('DB_USER')) {

    define(
        'DB_USER',
        $dbUser
    );
}


if (!defined('DB_CHARSET')) {

    define(
        'DB_CHARSET',
        $dbCharset
    );
}


/*
|--------------------------------------------------------------------------
| Optional Legacy Password Constant
|--------------------------------------------------------------------------
|
| Only define DB_PASS when an older application component
| explicitly requires it.
|
| New application code must NOT depend on this constant.
|
*/

if (
    !defined('DB_PASS') &&
    $dbPass !== ''
) {

    define(
        'DB_PASS',
        $dbPass
    );
}
/*
|--------------------------------------------------------------------------
| Database Helper Functions
|--------------------------------------------------------------------------
|
| These helpers provide a small, consistent interface for common
| prepared-statement operations.
|
| IMPORTANT:
| - Always use prepared statements.
| - Never concatenate untrusted input into SQL.
| - These helpers do NOT perform tenant authorization.
| - Tenant isolation belongs to the security/application layer.
|
*/


/*
|--------------------------------------------------------------------------
| Execute Prepared Query
|--------------------------------------------------------------------------
*/

function dbExecute(
    PDO $connection,
    string $sql,
    array $params = []
): PDOStatement {

    $statement = $connection->prepare(
        $sql
    );

    $statement->execute(
        $params
    );

    return $statement;
}


/*
|--------------------------------------------------------------------------
| Fetch One Row
|--------------------------------------------------------------------------
*/

function dbFetchOne(
    PDO $connection,
    string $sql,
    array $params = []
): ?array {

    $statement = dbExecute(
        $connection,
        $sql,
        $params
    );

    $result = $statement->fetch();

    return $result === false
        ? null
        : $result;
}


/*
|--------------------------------------------------------------------------
| Fetch Multiple Rows
|--------------------------------------------------------------------------
*/

function dbFetchAll(
    PDO $connection,
    string $sql,
    array $params = []
): array {

    $statement = dbExecute(
        $connection,
        $sql,
        $params
    );

    return $statement->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Fetch Single Value
|--------------------------------------------------------------------------
*/

function dbFetchValue(
    PDO $connection,
    string $sql,
    array $params = [],
    int $column = 0
): mixed {

    $statement = dbExecute(
        $connection,
        $sql,
        $params
    );

    return $statement->fetchColumn(
        $column
    );
}


/*
|--------------------------------------------------------------------------
| Database Transaction Helpers
|--------------------------------------------------------------------------
*/

function dbBeginTransaction(
    PDO $connection
): void {

    if (!$connection->inTransaction()) {

        $connection->beginTransaction();
    }
}


function dbCommit(
    PDO $connection
): void {

    if ($connection->inTransaction()) {

        $connection->commit();
    }
}


function dbRollback(
    PDO $connection
): void {

    if ($connection->inTransaction()) {

        $connection->rollBack();
    }
}

|--------------------------------------------------------------------------
| Execute Prepared Query
|--------------------------------------------------------------------------
*/

if (!function_exists('executeQuery')) {

    function executeQuery(
        PDO $connection,
        string $query,
        array $params = []
    ): PDOStatement {

        $statement = $connection->prepare($query);

        $statement->execute($params);

        return $statement;
    }
}


/*
|--------------------------------------------------------------------------
| Fetch One Row
|--------------------------------------------------------------------------
*/

if (!function_exists('fetchOne')) {

    function fetchOne(
        PDOStatement $statement
    ): ?array {

        $result = $statement->fetch();

        if ($result === false) {
            return null;
        }

        return $result;
    }
}


/*
|--------------------------------------------------------------------------
| Fetch All Rows
|--------------------------------------------------------------------------
*/

if (!function_exists('fetchAll')) {

    function fetchAll(
        PDOStatement $statement
    ): array {

        $result = $statement->fetchAll();

        return is_array($result)
            ? $result
            : [];
    }
}


/*
|--------------------------------------------------------------------------
| Fetch Single Value
|--------------------------------------------------------------------------
*/

if (!function_exists('fetchValue')) {

    function fetchValue(
        PDOStatement $statement,
        int $column = 0
    ): mixed {

        return $statement->fetchColumn($column);
    }
}


/*
|--------------------------------------------------------------------------
| Execute INSERT / UPDATE / DELETE
|--------------------------------------------------------------------------
*/

if (!function_exists('executeStatement')) {

    function executeStatement(
        PDO $connection,
        string $query,
        array $params = []
    ): bool {

        $statement = $connection->prepare($query);

        return $statement->execute($params);
    }
}


/*
|--------------------------------------------------------------------------
| Last Insert ID
|--------------------------------------------------------------------------
*/

if (!function_exists('lastInsertId')) {

    function lastInsertId(
        PDO $connection
    ): int {

        return (int) $connection->lastInsertId();
    }
}


/*
|--------------------------------------------------------------------------
| Transaction Helper
|--------------------------------------------------------------------------
|
| Executes multiple database operations atomically.
|
| If any operation throws an exception, the transaction
| is rolled back automatically.
|
*/

if (!function_exists('transaction')) {

    function transaction(
        PDO $connection,
        callable $callback
    ): mixed {

        $connection->beginTransaction();

        try {

            $result = $callback($connection);

            $connection->commit();

            return $result;

        } catch (Throwable $exception) {

            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Database Health Check
|--------------------------------------------------------------------------
*/

if (!function_exists('databaseHealthy')) {

    function databaseHealthy(
        PDO $connection
    ): bool {

        try {

            $statement = $connection->query(
                'SELECT 1'
            );

            return $statement !== false;

        } catch (Throwable $exception) {

            error_log(
                '[ThinkPlus Cloud] Database health check failed.'
            );

            return false;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Database Configuration Accessor
|--------------------------------------------------------------------------
|
| Provides non-sensitive configuration information to application
| components without exposing the database password.
|
*/

if (!function_exists('databaseConfig')) {

    function databaseConfig(): array
    {
        global $databaseConfiguration;

        return $databaseConfiguration;
    }
}


/*
|--------------------------------------------------------------------------
| Database Connection Accessor
|--------------------------------------------------------------------------
|
| Existing code can continue using $pdo directly.
|
| New code may use:
|
|     $db = database();
|
*/

if (!function_exists('database')) {

    function database(): PDO
    {
        global $pdo;

        if (!$pdo instanceof PDO) {

            throw new RuntimeException(
                'Database connection is not available.'
            );
        }

        return $pdo;
    }
}


/*
|--------------------------------------------------------------------------
| Database Transaction Status
|--------------------------------------------------------------------------
*/

if (!function_exists('databaseInTransaction')) {

    function databaseInTransaction(
        ?PDO $connection = null
    ): bool {

        $connection ??= database();

        return $connection->inTransaction();
    }
}


/*
|--------------------------------------------------------------------------
| Graceful Database Shutdown
|--------------------------------------------------------------------------
|
| PHP automatically closes PDO connections at the end of the request.
| Explicitly clearing the global reference is unnecessary, but this
| helper is available for long-running processes and workers.
|
*/

if (!function_exists('closeDatabase')) {

    function closeDatabase(): void
    {
        global $pdo;

        $pdo = null;
    }
}



/*
|--------------------------------------------------------------------------
| Final Compatibility Verification
|--------------------------------------------------------------------------
|
| The following guarantees are intentionally preserved:
|
| 1. $pdo remains globally available.
| 2. DB_HOST remains available.
| 3. DB_PORT remains available.
| 4. DB_NAME remains available.
| 5. DB_USER remains available.
| 6. DB_CHARSET remains available.
| 7. DB_PASS remains available when configured.
|
| This allows existing root-level PHP files and the current
| application architecture to migrate gradually.
|
*/


/*
|--------------------------------------------------------------------------
| Compatibility Assertions
|--------------------------------------------------------------------------
|
| These checks are intentionally non-destructive.
| They do not modify the existing database connection.
|
*/

if (!isset($pdo) || !$pdo instanceof PDO) {

    throw new RuntimeException(
        'ThinkPlus Cloud database connection is not available.'
    );
}


/*
|--------------------------------------------------------------------------
| End of ThinkPlus Cloud Database Configuration
|--------------------------------------------------------------------------
|
| Public compatibility surface:
|
|     $pdo
|     DB_HOST
|     DB_PORT
|     DB_NAME
|     DB_USER
|     DB_PASS
|     DB_CHARSET
|
| Helper functions:
|
|     envValue()
|     castEnvValue()
|     dbExecute()
|     dbFetchOne()
|     dbFetchAll()
|     dbFetchValue()
|     dbBeginTransaction()
|     dbCommit()
|     dbRollback()
|     dbLastInsertId()
|     dbHealthy()
|     dbTransaction()
|
| The file intentionally has no closing PHP tag.
|
*/
