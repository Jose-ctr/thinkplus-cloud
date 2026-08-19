<?php
declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Secure Database Connection
 * ============================================================
 *
 * Author: Joseph Mbui
 * Copyright: © 2026 ThinkPlus Cloud
 *
 * File:
 * app/config/db.php
 *
 * Description:
 * Central PDO connection for the ThinkPlus Cloud
 * school management SaaS.
 *
 * IMPORTANT:
 * ------------------------------------------------------------
 * Never commit real production database passwords to GitHub.
 *
 * Configure these environment variables on your hosting:
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
| Database Configuration
|--------------------------------------------------------------------------
|
| Environment variables are preferred.
|
| Local development defaults:
| host = localhost
| database = thinkplus
| user = root
| password = empty
|
*/

$dbHost = getenv('THINKPLUS_DB_HOST');

$dbName = getenv('THINKPLUS_DB_NAME');

$dbUser = getenv('THINKPLUS_DB_USER');

$dbPass = getenv('THINKPLUS_DB_PASSWORD');


/*
|--------------------------------------------------------------------------
| Development Defaults
|--------------------------------------------------------------------------
|
| These defaults make local development easier.
| Replace them through environment variables in production.
|
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
| Database Character Set
|--------------------------------------------------------------------------
*/

$charset = 'utf8mb4';


/*
|--------------------------------------------------------------------------
| Data Source Name
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
| PDO Options
|--------------------------------------------------------------------------
*/

$options = [

    /*
     * Throw exceptions when database errors occur.
     */
    PDO::ATTR_ERRMODE =>
        PDO::ERRMODE_EXCEPTION,


    /*
     * Return database rows as associative arrays.
     */
    PDO::ATTR_DEFAULT_FETCH_MODE =>
        PDO::FETCH_ASSOC,


    /*
     * Use real prepared statements.
     *
     * This helps protect ThinkPlus against
     * SQL injection.
     */
    PDO::ATTR_EMULATE_PREPARES =>
        false,


    /*
     * Do not use persistent connections.
     *
     * This keeps connection handling predictable
     * on shared hosting.
     */
    PDO::ATTR_PERSISTENT =>
        false,


    /*
     * Convert NULL database values correctly.
     */
    PDO::ATTR_ORACLE_NULLS =>
        PDO::NULL_NATURAL,
];


/*
|--------------------------------------------------------------------------
| Create Database Connection
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
     * Never display the actual database error
     * to visitors in production.
     *
     * The detailed error is written to the
     * server error log instead.
     */

    error_log(
        'ThinkPlus database connection failed: ' .
        $e->getMessage()
    );


    /*
     * Return HTTP 500.
     */

    http_response_code(500);


    /*
     * Safe user-facing message.
     */

    exit(
        'ThinkPlus is temporarily unable to connect to the database.'
    );
}


/*
|--------------------------------------------------------------------------
| Connection Helper
|--------------------------------------------------------------------------
|
| Other ThinkPlus files can use:
|
| require_once __DIR__ . '/db.php';
|
| and then:
|
| $pdo
|
| Or:
|
| db()
|
*/

function db(): PDO
{
    global $pdo;

    return $pdo;
}


/*
|--------------------------------------------------------------------------
| Verify Connection
|--------------------------------------------------------------------------
|
| This function can be used by diagnostics or
| installation checks.
|
*/

function databaseIsConnected(): bool
{
    global $pdo;

    return isset($pdo) &&
           $pdo instanceof PDO;
}
