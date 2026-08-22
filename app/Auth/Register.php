<?php

declare(strict_types=1);

/**
 * ============================================================
 * THINKPLUS CLOUD
 * Register Handler - School + Admin Registration
 * ============================================================
 *
 * Phase 3: Authentication & Security
 *
 * Founder: Joseph Mbui
 * ThinkPlus HQ: Mariakani, Kilifi, Kenya
 *
 * File:
 * app/Auth/Register.php
 *
 * Responsibilities:
 * - Create a new school tenant
 * - Store dynamic school location
 * - Create school administrator
 * - Validate registration data
 * - Validate password strength
 * - Validate Kenyan phone number
 * - Protect registration with CSRF
 * - Prevent duplicate email
 * - Prevent duplicate school
 * - Generate secure public IDs
 * - Hash passwords securely
 * - Use database transactions
 * - Record audit events
 * - Authenticate administrator after registration
 *
 * IMPORTANT:
 * ThinkPlus HQ location is NOT used as the school's location.
 * Each school supplies its own county, town and sub-county.
 *
 * ============================================================
 */

namespace App\Auth;

use PDO;
use Throwable;
use Security\Security;
use Security\Csrf;
use Security\Audit;

class Register
{
    /*
    |--------------------------------------------------------------------------
    | DATABASE
    |--------------------------------------------------------------------------
    */

    private PDO $pdo;


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    /*
    |--------------------------------------------------------------------------
    | REGISTER SCHOOL + ADMIN
    |--------------------------------------------------------------------------
    */

    public function register(
        array $data,
        ?string $csrfToken = null
    ): array {

        Security::startSecureSession();


        /*
        |--------------------------------------------------------------------------
        | 1. CSRF PROTECTION
        |--------------------------------------------------------------------------
        */

        if (
            $csrfToken === null ||
            !Csrf::verify($csrfToken)
        ) {
            return [
                'success' => false,
                'message' =>
                    'Security verification failed. ' .
                    'Please refresh the page and try again.'
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | 2. READ AND SANITIZE INPUT
        |--------------------------------------------------------------------------
        */

        $schoolName = Security::sanitize(
            (string) ($data['school_name'] ?? '')
        );

        $name = Security::sanitize(
            (string) (
                $data['name'] ??
                $data['full_name'] ??
                ''
            )
        );

        $email = strtolower(
            trim(
                (string) ($data['email'] ?? '')
            )
        );

        $phone = Security::sanitize(
            (string) ($data['phone'] ?? '')
        );

        $county = Security::sanitize(
            (string) ($data['county'] ?? '')
        );

        $town = Security::sanitize(
            (string) ($data['town'] ?? '')
        );

        $subCounty = Security::sanitize(
            (string) (
                $data['sub_county'] ??
                ''
            )
        );

        $password = (string) (
            $data['password'] ?? ''
        );

        $passwordConfirmation = (string) (
            $data['password_confirmation'] ??
            $data['confirm_password'] ??
            $data['password_confirm'] ??
            ''
        );


        /*
        |--------------------------------------------------------------------------
        | 3. BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($schoolName === '') {
            return [
                'success' => false,
                'message' => 'School name is required.'
            ];
        }

        if (
            mb_strlen($schoolName) < 2 ||
            mb_strlen($schoolName) > 255
        ) {
            return [
                'success' => false,
                'message' =>
                    'School name must be between 2 and 255 characters.'
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | ADMINISTRATOR NAME
        |--------------------------------------------------------------------------
        */

        if ($name === '') {
            return [
                'success' => false,
                'message' =>
                    'Administrator name is required.'
            ];
        }

        if (
            mb_strlen($name) < 2 ||
            mb_strlen($name) > 255
        ) {
            return [
                'success' => false,
                'message' =>
                    'Administrator name must be between 2 and 255 characters.'
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | EMAIL
        |--------------------------------------------------------------------------
        */

        if ($email === '') {
            return [
                'success' => false,
                'message' =>
                    'Email address is required.'
            ];
        }

        if (!Security::isValidEmail($email)) {
            return [
                'success' => false,
                'message' =>
                    'Please provide a valid email address.'
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PHONE
        |--------------------------------------------------------------------------
        */

        $phone = $this->normalizeKenyanPhone(
            $phone
        );

        if ($phone === '') {
            return [
                'success' => false,
                'message' =>
                    'Please provide a valid Kenyan phone number.'
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | SCHOOL LOCATION
        |--------------------------------------------------------------------------
        |
        | This is the school's location.
        |
        | It is NOT ThinkPlus HQ.
        |
        */

        if ($county === '') {
            return [
                'success' => false,
                'message' =>
                    'County is required.'
            ];
        }

        if ($town === '') {
            return [
                'success' => false,
                'message' =>
                    'Town is required.'
            ];
        }

        /*
         * Sub-county may be supplied by the registration form.
         *
         * If not supplied, use the town as a fallback so that
         * the tenant still has a usable location value.
         */
        if ($subCounty === '') {
            $subCounty = $town;
        }


        /*
        |--------------------------------------------------------------------------
        | PASSWORD
        |--------------------------------------------------------------------------
        */

        if ($password === '') {
            return [
                'success' => false,
                'message' =>
                    'Password is required.'
            ];
        }

        $passwordErrors =
            Security::validatePassword(
                $password
            );

        if (!empty($passwordErrors)) {
            return [
                'success' => false,
                'message' =>
                    implode(' ', $passwordErrors),
                'errors' =>
                    $passwordErrors
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PASSWORD CONFIRMATION
        |--------------------------------------------------------------------------
        */

        if ($passwordConfirmation === '') {
            return [
                'success' => false,
                'message' =>
                    'Please confirm your password.'
            ];
        }

        if (
            !hash_equals(
                $password,
                $passwordConfirmation
            )
        ) {
            return [
                'success' => false,
                'message' =>
                    'Passwords do not match.'
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE SCHOOL SLUG
        |--------------------------------------------------------------------------
        */

        $slug = $this->generateSlug(
            $schoolName
        );


        /*
        |--------------------------------------------------------------------------
        | DATABASE TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {

            $this->pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | 4. CHECK DUPLICATE EMAIL
            |--------------------------------------------------------------------------
            */

            $emailCheck = $this->pdo->prepare(
                'SELECT id
                 FROM users
                 WHERE email = ?
                 LIMIT 1'
            );

            $emailCheck->execute([
                $email
            ]);

            if ($emailCheck->fetchColumn()) {

                $this->pdo->rollBack();

                return [
                    'success' => false,
                    'message' =>
                        'An account with this email already exists.'
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | 5. CHECK DUPLICATE SCHOOL NAME
            |--------------------------------------------------------------------------
            */

            $schoolCheck = $this->pdo->prepare(
                'SELECT id
                 FROM schools
                 WHERE name = ?
                 LIMIT 1'
            );

            $schoolCheck->execute([
                $schoolName
            ]);

            if ($schoolCheck->fetchColumn()) {

                $this->pdo->rollBack();

                return [
                    'success' => false,
                    'message' =>
                        'A school with this name already exists.'
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | 6. CHECK DUPLICATE SLUG
            |--------------------------------------------------------------------------
            */

            $slugCheck = $this->pdo->prepare(
                'SELECT id
                 FROM schools
                 WHERE slug = ?
                 LIMIT 1'
            );

            $slugCheck->execute([
                $slug
            ]);

            if ($slugCheck->fetchColumn()) {

                $slug = $this->generateSlug(
                    $schoolName
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 7. CREATE SCHOOL TENANT
            |--------------------------------------------------------------------------
            */

            $schoolPublicId =
                Security::publicId();

            $schoolStmt = $this->pdo->prepare(
                'INSERT INTO schools
                (
                    public_id,
                    name,
                    slug,
                    email,
                    phone,
                    county,
                    town,
                    sub_county,
                    status,
                    created_at
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW()
                )'
            );

            $schoolStmt->execute([
                $schoolPublicId,
                $schoolName,
                $slug,
                $email,
                $phone,
                $county,
                $town,
                $subCounty,
                'active'
            ]);


            /*
            |--------------------------------------------------------------------------
            | 8. GET SCHOOL ID
            |--------------------------------------------------------------------------
            */

            $schoolId =
                (int) $this->pdo->lastInsertId();

            if ($schoolId <= 0) {
                throw new \RuntimeException(
                    'School creation failed.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 9. HASH ADMIN PASSWORD
            |--------------------------------------------------------------------------
            */

            $passwordHash =
                Security::hashPassword(
                    $password
                );


            /*
            |--------------------------------------------------------------------------
            | 10. CREATE SCHOOL ADMIN
            |--------------------------------------------------------------------------
            */

            $userPublicId =
                Security::publicId();

            $userStmt = $this->pdo->prepare(
                'INSERT INTO users
                (
                    public_id,
                    school_id,
                    name,
                    email,
                    phone,
                    password_hash,
                    role,
                    status,
                    failed_attempts,
                    created_at
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    0,
                    NOW()
                )'
            );

            $userStmt->execute([
                $userPublicId,
                $schoolId,
                $name,
                $email,
                $phone,
                $passwordHash,
                'school_admin',
                'active'
            ]);


            /*
            |--------------------------------------------------------------------------
            | 11. GET USER ID
            |--------------------------------------------------------------------------
            */

            $userId =
                (int) $this->pdo->lastInsertId();

            if ($userId <= 0) {
                throw new \RuntimeException(
                    'Administrator account creation failed.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 12. AUDIT SCHOOL CREATION
            |--------------------------------------------------------------------------
            |
            | Audit failures should not destroy a successful
            | registration, therefore they are handled separately.
            |
            */

            try {

                Audit::create(
                    $this->pdo,
                    'school',
                    $schoolId,
                    [
                        'public_id' =>
                            $schoolPublicId,
                        'name' =>
                            $schoolName,
                        'slug' =>
                            $slug,
                        'email' =>
                            $email,
                        'phone' =>
                            $phone,
                        'county' =>
                            $county,
                        'town' =>
                            $town,
                        'sub_county' =>
                            $subCounty,
                        'status' =>
                            'active'
                    ]
                );

            } catch (Throwable $e) {

                error_log(
                    'ThinkPlus school audit error: ' .
                    $e->getMessage()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 13. AUDIT USER CREATION
            |--------------------------------------------------------------------------
            */

            try {

                Audit::create(
                    $this->pdo,
                    'user',
                    $userId,
                    [
                        'public_id' =>
                            $userPublicId,
                        'school_id' =>
                            $schoolId,
                        'name' =>
                            $name,
                        'email' =>
                            $email,
                        'phone' =>
                            $phone,
                        'role' =>
                            'school_admin',
                        'status' =>
                            'active'
                    ]
                );

            } catch (Throwable $e) {

                error_log(
                    'ThinkPlus user audit error: ' .
                    $e->getMessage()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 14. COMMIT TRANSACTION
            |--------------------------------------------------------------------------
            */

            $this->pdo->commit();


            /*
            |--------------------------------------------------------------------------
            | 15. REGENERATE SESSION
            |--------------------------------------------------------------------------
            |
            | Prevent session fixation after successful
            | authentication.
            |
            */

            session_regenerate_id(true);


            /*
            |--------------------------------------------------------------------------
            | 16. AUTHENTICATE NEW ADMIN
            |--------------------------------------------------------------------------
            */

            $_SESSION['user_id'] =
                $userId;

            $_SESSION['public_id'] =
                $userPublicId;

            $_SESSION['email'] =
                $email;

            $_SESSION['role'] =
                'school_admin';

            $_SESSION['school_id'] =
                $schoolId;

            $_SESSION['_tenant_school_id'] =
                $schoolId;

            $_SESSION['authenticated_at'] =
                time();


            /*
            |--------------------------------------------------------------------------
            | 17. ROTATE CSRF TOKEN
            |--------------------------------------------------------------------------
            */

            Csrf::rotate();


            /*
            |-----------------------------------
               
          ------------------------
            | 18. SUCCESS
            |--------------------------------------------------------------------------
            */

            return [
                'success' => true,

                'message' =>
                    'School registration successful.',

                'redirect' =>
                    '/dashboard.php',

                'school' => [
                    'id' =>
                        $schoolId,

                    'public_id' =>
                        $schoolPublicId,

                    'name' =>
                        $schoolName,

                    'slug' =>
                        $slug,

                    'county' =>
                        $county,

                    'town' =>
                        $town,

                    'sub_county' =>
                        $subCounty
                ],

                'user' => [
                    'id' =>
                        $userId,

                    'public_id' =>
                        $userPublicId,

                    'school_id' =>
                        $schoolId,

                    'name' =>
                        $name,

                    'email' =>
                        $email,

                    'phone' =>
                        $phone,

                    'role' =>
                        'school_admin'
                ]
            ];


        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | ROLLBACK
            |--------------------------------------------------------------------------
            */

            if (
                $this->pdo->inTransaction()
            ) {
                $this->pdo->rollBack();
            }


            /*
            |--------------------------------------------------------------------------
            | SERVER-SIDE ERROR LOG
            |--------------------------------------------------------------------------
            |
            | Never expose SQL/database details to the user.
            |
            */

            error_log(
                'ThinkPlus Register error: ' .
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' =>
                    'Registration failed. ' .
                    'Please try again.'
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KENYAN PHONE NORMALIZATION
    |--------------------------------------------------------------------------
    */

    private function normalizeKenyanPhone(
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
        |--------------------------------------------------------------------------
        | +254712345678
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | 254712345678
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | 0712345678 / 0112345678
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/^0(7\d{8}|1\d{8})$/',
                $phone
            )
        ) {
            return '+254' . substr(
                $phone,
                1
            );
        }

        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE SCHOOL SLUG
    |--------------------------------------------------------------------------
    */

    private function generateSlug(
        string $name
    ): string {

        $slug = strtolower(
            trim($name)
        );

        $slug = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug
        );

        $slug = trim(
            (string) $slug,
            '-'
        );

        if ($slug === '') {
            $slug = 'school';
        }

        return $slug . '-' .
            substr(
                bin2hex(
                    random_bytes(4)
                ),
                0,
                8
            );
    }
}
