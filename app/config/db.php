<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Secure Database Connection
 * ============================================================
 *
 * File:
 * app/config/db.php
 *
 * Copyright: © 2026 ThinkPlus Cloud
 *
 * IMPORTANT:
 * Never commit real production database credentials.
 *
 * Production environment variables:
 *
 * THINKPLUS_DB_HOST
 * THINKPLUS_DB_NAME
 * THINKPLUS_DB_USER
 * THINKPLUS_DB_PASSWORD
 *
 * ============================================================
 */


/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
*/

$dbHost = getenv('THINKPLUS_DB_HOST');
$dbName = getenv('THINKPLUS_DB_NAME');
$dbUser = getenv('THINKPLUS_DB_USER');
$dbPass = getenv('THINKPLUS_DB_PASSWORD');


/*
|--------------------------------------------------------------------------
| LOCAL DEVELOPMENT DEFAULTS
|--------------------------------------------------------------------------
*/

if ($dbHost === false || $dbHost === '') {
    $dbHost = 'localhost';
}

if ($dbName === false || $dbName === '') {
    $dbName = 'thinkplus';
}

if ($dbUser === false || $dbUser === '') {
    $dbUser = 'root';
}

if ($dbPass === false) {
    $dbPass = '';
}


/*
|--------------------------------------------------------------------------
| CHARACTER SET
|--------------------------------------------------------------------------
*/

$charset = 'utf8mb4';


/*
|--------------------------------------------------------------------------
| DATA SOURCE NAME
|--------------------------------------------------------------------------
*/

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $dbHost,
    $dbName,
    $charset
);


/*
|--------------------------------------------------------------------------
| PDO OPTIONS
|--------------------------------------------------------------------------
*/

$options = [

    /*
     * Throw exceptions for database errors.
     */
    PDO::ATTR_ERRMODE =>
        PDO::ERRMODE_EXCEPTION,

    /*
     * Return rows as associative arrays.
     */
    PDO::ATTR_DEFAULT_FETCH_MODE =>
        PDO::FETCH_ASSOC,

    /*
     * Use native prepared statements.
     */
    PDO::ATTR_EMULATE_PREPARES =>
        false,

    /*
     * Do not use persistent connections.
     */
    PDO::ATTR_PERSISTENT =>
        false,

    /*
     * Preserve database NULL values.
     */
    PDO::ATTR_ORACLE_NULLS =>
        PDO::NULL_NATURAL,
];


/*
|--------------------------------------------------------------------------
| CREATE PDO CONNECTION
|--------------------------------------------------------------------------
*/

try {

    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        $options
    );

} catch (\PDOException $e) {

    /*
     * Log the real error server-side.
     * Never expose credentials or SQL details.
     */
    error_log(
        'ThinkPlus database connection failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    exit(
        'ThinkPlus is temporarily unable to connect to the database.'
    );
}


/*
|--------------------------------------------------------------------------
| DATABASE HELPER
|--------------------------------------------------------------------------
*/

function db(): PDO
{
    global $pdo;

    return $pdo;
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION CHECK
|--------------------------------------------------------------------------
*/

function databaseIsConnected(): bool
{
    global $pdo;

    return isset($pdo)
        && $pdo instanceof PDO;
}
