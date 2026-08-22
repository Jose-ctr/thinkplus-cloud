<?php
declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Database Configuration
 * ============================================================
 *
 * Author: Joseph Mbui
 * Copyright: © 2026 ThinkPlus Cloud
 *
 * File:
 * config/database.php
 *
 * Description:
 * Secure PDO connection for ThinkPlus Cloud.
 *
 * IMPORTANT:
 * - Never hard-code production database credentials.
 * - Use environment variables in production.
 * - Never commit .env to GitHub.
 *
 * ============================================================
 */


/*
|--------------------------------------------------------------------------
| Load .env values when available
|--------------------------------------------------------------------------
|
| This project intentionally avoids requiring a third-party
| dotenv package at this stage.
|
| The application first checks real environment variables.
| If they are unavailable, it attempts to read a local .env file.
|
*/

function envValue(
    string $key,
    ?string $default = null
): ?string {

    $value = getenv($key);

    if ($value !== false) {
        return $value;
    }

    /*
     * Try loading project .env manually.
     */
    static $env = null;

    if ($env === null) {

        $env = [];

        $envFile = dirname(__DIR__) . '/.env';

        if (is_file($envFile) && is_readable($envFile)) {

            $lines = file(
                $envFile,
                FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
            );

            if ($lines !== false) {

                foreach ($lines as $line) {

                    $line = trim($line);

                    /*
                     * Ignore comments.
                     */
                    if (
                        $line === '' ||
                        str_starts_with($line, '#')
                    ) {
                        continue;
                    }

                    if (!str_contains($line, '=')) {
                        continue;
                    }

                    [$name, $val] = explode(
                        '=',
                        $line,
                        2
                    );

                    $name = trim($name);
                    $val  = trim($val);

                    /*
                     * Remove optional quotes.
                     */
                    if (
                        strlen($val) >= 2 &&
                        (
                            (
                                $val[0] === '"' &&
                                $val[strlen($val) - 1] === '"'
                            ) ||
                            (
                                $val[0] === "'" &&
                                $val[strlen($val) - 1] === "'"
                            )
                        )
                    ) {
                        $val = substr(
                            $val,
                            1,
                            -1
                        );
                    }

                    $env[$name] = $val;
                }
            }
        }
    }

    return $env[$key] ?? $default;
}


/*
|--------------------------------------------------------------------------
| Database Settings
|--------------------------------------------------------------------------
*/

$dbHost = envValue(
    'DB_HOST',
    '127.0.0.1'
);

$dbPort = envValue(
    'DB_PORT',
    '3306'
);

$dbName = envValue(
    'DB_NAME',
    'thinkplus_cloud'
);

$dbUser = envValue(
    'DB_USER',
    'root'
);

$dbPass = envValue(
    'DB_PASS',
    ''
);


/*
|--------------------------------------------------------------------------
| Charset
|--------------------------------------------------------------------------
*/

$dbCharset = 'utf8mb4';


/*
|--------------------------------------------------------------------------
| PDO DSN
|--------------------------------------------------------------------------
*/

$dsn =
    'mysql:host=' .
    $dbHost .
    ';port=' .
    $dbPort .
    ';dbname=' .
    $dbName .
    ';charset=' .
    $dbCharset;


/*
|--------------------------------------------------------------------------
| PDO Options
|--------------------------------------------------------------------------
*/

$options = [

    /*
     * Throw exceptions for database errors.
     */
    PDO::ATTR_ERRMODE =>
        PDO::ERRMODE_EXCEPTION,

    /*
     * Always return associative arrays.
     */
    PDO::ATTR_DEFAULT_FETCH_MODE =>
        PDO::FETCH_ASSOC,

    /*
     * Use native prepared statements.
     */
    PDO::ATTR_EMULATE_PREPARES =>
        false,

    /*
     * Persistent connections disabled.
     */
    PDO::ATTR_PERSISTENT =>
        false,

    /*
     * MySQL buffered queries.
     */
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY =>
        true
];


/*
|--------------------------------------------------------------------------
| Create PDO Connection
|--------------------------------------------------------------------------
*/

try {

    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        $options
    );

} catch (PDOException $e) {

    /*
     * Never expose database credentials or
     * raw database errors to website visitors.
     */

    error_log(
        'ThinkPlus Cloud database connection failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    exit(
        'Database connection failed. Please try again later.'
    );
}


/*
|--------------------------------------------------------------------------
| Optional Database Configuration Constants
|--------------------------------------------------------------------------
|
| These are useful for older project files that may reference
| database constants.
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


/*
|--------------------------------------------------------------------------
| End
|--------------------------------------------------------------------------
*/
