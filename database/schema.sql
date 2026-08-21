-- ============================================================
-- THINKPLUS CLOUD
-- MULTI-SCHOOL SaaS DATABASE
-- DATABASE SCHEMA v3.0 — COMPLETE
-- ============================================================
-- Author: Joseph Mbui
-- Copyright: © 2026 ThinkPlus Cloud
--
-- DATABASE: MySQL 8.0+
-- ENGINE:   InnoDB
-- CHARSET:  utf8mb4
--
-- ARCHITECTURE:
--   Multi-tenant / Multi-school SaaS
--
-- DESIGNED FOR:
--   Schools
--   Branches
--   Students
--   Parents / Guardians
--   Staff / Teachers
--   Classes / Streams
--   Subjects
--   Assessments / Results
--   Attendance
--   Report Cards
--   Fees / Invoices / Payments
--   M-Pesa
--   SMS
--   Notifications
--   SaaS Subscriptions
--   Audit / Security
--   Mobile / Web APIs
--
-- ============================================================


-- ============================================================
-- 0. DATABASE
-- ============================================================

CREATE DATABASE IF NOT EXISTS thinkplus_cloud
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE thinkplus_cloud;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- ============================================================
-- 1. SYSTEM SETTINGS
-- ============================================================

DROP TABLE IF EXISTS system_settings;

CREATE TABLE system_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT DEFAULT NULL,
    setting_type ENUM(
        'string',
        'integer',
        'decimal',
        'boolean',
        'json'
    ) NOT NULL DEFAULT 'string',

    description VARCHAR(500) DEFAULT NULL,

    is_public BOOLEAN NOT NULL DEFAULT FALSE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_system_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 2. SCHOOLS
-- ============================================================

DROP TABLE IF EXISTS schools;

CREATE TABLE schools (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    public_id CHAR(36) NOT NULL,

    school_code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,

    registration_number VARCHAR(100) DEFAULT NULL,

    school_type ENUM(
        'primary',
        'secondary',
        'mixed',
        'college',
        'tvets',
        'university',
        'special',
        'other'
    ) NOT NULL DEFAULT 'primary',

    ownership ENUM(
        'public',
        'private',
        'faith_based',
        'community',
        'other'
    ) NOT NULL DEFAULT 'private',

    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    alternate_phone VARCHAR(30) DEFAULT NULL,

    website VARCHAR(255) DEFAULT NULL,

    country VARCHAR(100) NOT NULL DEFAULT 'Kenya',
    county VARCHAR(100) DEFAULT NULL,
    sub_county VARCHAR(100) DEFAULT NULL,
    town VARCHAR(100) DEFAULT NULL,
    physical_address VARCHAR(500) DEFAULT NULL,

    logo_url VARCHAR(500) DEFAULT NULL,

    timezone VARCHAR(100) NOT NULL DEFAULT 'Africa/Nairobi',
    currency CHAR(3) NOT NULL DEFAULT 'KES',

    status ENUM(
        'trial',
        'active',
        'suspended',
        'expired',
        'closed'
    ) NOT NULL DEFAULT 'trial',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_school_public_id (public_id),
    UNIQUE KEY uq_school_code (school_code),
    UNIQUE KEY uq_school_registration (registration_number),
    KEY idx_school_status (status),
    KEY idx_school_county (county)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 3. SCHOOL BRANCHES
-- ============================================================

DROP TABLE IF EXISTS school_branches;

CREATE TABLE school_branches (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    branch_code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,

    phone VARCHAR(30) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,

    county VARCHAR(100) DEFAULT NULL,
    town VARCHAR(100) DEFAULT NULL,
    address VARCHAR(500) DEFAULT NULL,

    is_main BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_branch_code (
        school_id,
        branch_code
    ),

    KEY idx_branch_school (school_id),

    CONSTRAINT fk_branch_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 4. SCHOOL SETTINGS
-- ============================================================

DROP TABLE IF EXISTS school_settings;

CREATE TABLE school_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT DEFAULT NULL,
    setting_type ENUM(
        'string',
        'integer',
        'decimal',
        'boolean',
        'json'
    ) NOT NULL DEFAULT 'string',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_school_setting (
        school_id,
        setting_key
    ),

    CONSTRAINT fk_school_setting_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 5. ACADEMIC YEARS
-- ============================================================

DROP TABLE IF EXISTS academic_years;

CREATE TABLE academic_years (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    year_name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    status ENUM(
        'planned',
        'active',
        'completed',
        'closed'
    ) NOT NULL DEFAULT 'planned',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_school_year (
        school_id,
        year_name
    ),

    KEY idx_academic_year_status (school_id, status),

    CONSTRAINT fk_academic_year_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_academic_year_dates
        CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 6. TERMS
-- ============================================================

DROP TABLE IF EXISTS terms;

CREATE TABLE terms (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,

    term_number TINYINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    status ENUM(
        'planned',
        'active',
        'completed',
        'closed'
    ) NOT NULL DEFAULT 'planned',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_year_term (
        academic_year_id,
        term_number
    ),

    KEY idx_term_school (school_id),
    KEY idx_term_status (school_id, status),

    CONSTRAINT fk_term_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_term_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_term_number
        CHECK (term_number BETWEEN 1 AND 3),

    CONSTRAINT chk_term_dates
        CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 7. ROLES
-- ============================================================

DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,

    description VARCHAR(500) DEFAULT NULL,

    is_system BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_role_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 8. PERMISSIONS
-- ============================================================

DROP TABLE IF EXISTS permissions;

CREATE TABLE permissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,

    module VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,

    description VARCHAR(500) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_permission_slug (slug),
    KEY idx_permission_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 9. ROLE PERMISSIONS
-- ============================================================

DROP TABLE IF EXISTS role_permissions;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (role_id, permission_id),

    CONSTRAINT fk_role_permission_role
        FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_role_permission_permission
        FOREIGN KEY (permission_id)
        REFERENCES permissions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 10. USERS
-- ============================================================

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    public_id CHAR(36) NOT NULL,

    username VARCHAR(100) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,

    password_hash VARCHAR(255) NOT NULL,

    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,

    avatar_url VARCHAR(500) DEFAULT NULL,

    status ENUM(
        'pending',
        'active',
        'suspended',
        'locked',
        'disabled'
    ) NOT NULL DEFAULT 'pending',

    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    phone_verified_at TIMESTAMP NULL DEFAULT NULL,

    last_login_at TIMESTAMP NULL DEFAULT NULL,
    last_login_ip VARCHAR(45) DEFAULT NULL,

    failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,

    password_changed_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (id),

    UNIQUE KEY uq_user_public_id (public_id),
    UNIQUE KEY uq_user_username (username),
    UNIQUE KEY uq_user_email (email),

    KEY idx_user_phone (phone),
    KEY idx_user_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 11. SCHOOL USER MEMBERSHIPS
-- ============================================================

DROP TABLE IF EXISTS school_users;

CREATE TABLE school_users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    employee_number VARCHAR(100) DEFAULT NULL,

    status ENUM(
        'active',
        'inactive',
        'suspended'
    ) NOT NULL DEFAULT 'active',

    joined_at DATE DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_school_user (
        school_id,
        user_id
    ),

    UNIQUE KEY uq_employee_number (
        school_id,
        employee_number
    ),

    KEY idx_school_users_branch (branch_id),

    CONSTRAINT fk_school_user_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_school_user_branch
        FOREIGN KEY (branch_id)
        REFERENCES school_branches(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_school_user_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 12. USER ROLES
-- ============================================================

DROP TABLE IF EXISTS user_roles;

CREATE TABLE user_roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_user_role (
        school_id,
        user_id,
        role_id
    ),

    KEY idx_user_roles_user (user_id),

    CONSTRAINT fk_user_role_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_user_role_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_user_role_role
        FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 13. USER SESSIONS
-- ============================================================

DROP TABLE IF EXISTS user_sessions;

CREATE TABLE user_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    session_token_hash CHAR(64) NOT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,

    device_id VARCHAR(255) DEFAULT NULL,

    expires_at TIMESTAMP NOT NULL,
    last_activity_at TIMESTAMP NULL DEFAULT NULL,

    revoked_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_session_token (
        session_token_hash
    ),

    KEY idx_session_user (
        user_id,
        expires_at
    ),

    CONSTRAINT fk_session_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 14. PARENTS / GUARDIANS
-- ============================================================

DROP TABLE IF EXISTS guardians;

CREATE TABLE guardians (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    public_id CHAR(36) NOT NULL,

    user_id BIGINT UNSIGNED DEFAULT NULL,

    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,

    relationship_default VARCHAR(100) DEFAULT NULL,

    phone VARCHAR(30) NOT NULL,
    alternate_phone VARCHAR(30) DEFAULT NULL,

    email VARCHAR(255) DEFAULT NULL,

    national_id VARCHAR(100) DEFAULT NULL,

    address VARCHAR(500) DEFAULT NULL,

    occupation VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'active',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_guardian_public_id (public_id),
    UNIQUE KEY uq_guardian_user (user_id),

    KEY idx_guardian_phone (phone),
    KEY idx_guardian_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 15. CLASSES
-- ============================================================

DROP TABLE IF EXISTS classes;

CREATE TABLE classes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,

    education_level ENUM(
        'pre_primary',
        'primary',
        'junior_secondary',
        'senior_secondary',
        'college',
        'other'
    ) NOT NULL DEFAULT 'primary',

    grade_level TINYINT UNSIGNED DEFAULT NULL,

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_class_code (
        school_id,
        code
    ),

    KEY idx_class_school (school_id),

    CONSTRAINT fk_class_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 16. STREAMS
-- ============================================================

DROP TABLE IF EXISTS streams;

CREATE TABLE streams (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,

    capacity INT UNSIGNED DEFAULT NULL,

    class_teacher_id BIGINT UNSIGNED DEFAULT NULL,

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_stream_code (
        school_id,
        code
    ),

    KEY idx_stream_class (class_id),

    CONSTRAINT fk_stream_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_stream_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 17. STAFF
-- ============================================================

DROP TABLE IF EXISTS staff;

CREATE TABLE staff (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    public_id CHAR(36) NOT NULL,

    school_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,

    staff_number VARCHAR(100) NOT NULL,

    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,

    gender ENUM(
        'male',
        'female',
        'other',
        'undisclosed'
    ) DEFAULT 'undisclosed',

    phone VARCHAR(30) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,

    staff_type ENUM(
        'teacher',
        'administrator',
        'finance',
        'support',
        'security',
        'driver',
        'other'
    ) NOT NULL DEFAULT 'teacher',

    employment_type ENUM(
        'permanent',
        'contract',
        'part_time',
        'temporary',
        'volunteer'
    ) NOT NULL DEFAULT 'permanent',

    date_joined DATE DEFAULT NULL,

    status ENUM(
        'active',
        'inactive',
        'suspended',
        'terminated'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (id),

    UNIQUE KEY uq_staff_public_id (public_id),

    UNIQUE KEY uq_staff_number (
        school_id,
        staff_number
    ),

    KEY idx_staff_user (user_id),
    KEY idx_staff_school_status (school_id, status),

    CONSTRAINT fk_staff_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_staff_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 18. STUDENTS
-- ============================================================

DROP TABLE IF EXISTS students;

CREATE TABLE students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    public_id CHAR(36) NOT NULL,

    school_id BIGINT UNSIGNED NOT NULL,

    admission_number VARCHAR(100) NOT NULL,

    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,

    gender ENUM(
        'male',
        'female',
        'other',
        'undisclosed'
    ) NOT NULL DEFAULT 'undisclosed',

    date_of_birth DATE DEFAULT NULL,

    national_id VARCHAR(100) DEFAULT NULL,

    birth_certificate_number VARCHAR(100) DEFAULT NULL,

    phone VARCHAR(30) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,

    photo_url VARCHAR(500) DEFAULT NULL,

    admission_date DATE DEFAULT NULL,

    status ENUM(
        'applicant',
        'active',
        'graduated',
        'transferred',
        'withdrawn',
        'suspended',
        'deceased'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (id),

    UNIQUE KEY uq_student_public_id (public_id),

    UNIQUE KEY uq_student_admission (
        school_id,
        admission_number
    ),

    KEY idx_student_name (
        school_id,
        last_name,
        first_name
    ),

    KEY idx_student_status (
        school_id,
        status
    ),

    CONSTRAINT fk_student_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 19. STUDENT GUARDIANS
-- ============================================================

DROP TABLE IF EXISTS student_guardians;

CREATE TABLE student_guardians (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    student_id BIGINT UNSIGNED NOT NULL,
    guardian_id BIGINT UNSIGNED NOT NULL,

    relationship VARCHAR(100) NOT NULL,

    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    is_emergency_contact BOOLEAN NOT NULL DEFAULT FALSE,

    can_pickup BOOLEAN NOT NULL DEFAULT TRUE,
    can_receive_sms BOOLEAN NOT NULL DEFAULT TRUE,
    can_receive_notifications BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_student_guardian (
        student_id,
        guardian_id
    ),

    KEY idx_guardian_students (guardian_id),

    CONSTRAINT fk_student_guardian_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_student_guardian_guardian
        FOREIGN KEY (guardian_id)
        REFERENCES guardians(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 20. ENROLLMENTS
-- ============================================================

DROP TABLE IF EXISTS enrollments;

CREATE TABLE enrollments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    academic_year_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    stream_id BIGINT UNSIGNED DEFAULT NULL,

    enrollment_date DATE NOT NULL,

    exit_date DATE DEFAULT NULL,

    status ENUM(
        'active',
        'completed',
        'transferred',
        'withdrawn'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_student_year (
        student_id,
        academic_year_id
    ),

    KEY idx_enrollment_class (
        school_id,
        academic_year_id,
        class_id,
        stream_id
    ),

    CONSTRAINT fk_enrollment_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_enrollment_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_enrollment_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_enrollment_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_enrollment_stream
        FOREIGN KEY (stream_id)
        REFERENCES streams(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 21. SUBJECTS
-- ============================================================

DROP TABLE IF EXISTS subjects;

CREATE TABLE subjects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,

    learning_area VARCHAR(150) DEFAULT NULL,

    subject_type ENUM(
        'core',
        'elective',
        'optional',
        'co_curricular',
        'other'
    ) NOT NULL DEFAULT 'core',

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_subject_code (
        school_id,
        code
    ),

    KEY idx_subject_school (school_id),

    CONSTRAINT fk_subject_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 22. CLASS SUBJECTS
-- ============================================================

DROP TABLE IF EXISTS class_subjects;

CREATE TABLE class_subjects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,

    teacher_id BIGINT UNSIGNED DEFAULT NULL,

    academic_year_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_class_subject_year (
        class_id,
        subject_id,
        academic_year_id
    ),

    KEY idx_class_subject_teacher (teacher_id),

    CONSTRAINT fk_class_subject_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_class_subject_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_class_subject_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_class_subject_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES staff(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_class_subject_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 23. ASSESSMENTS
-- ============================================================

DROP TABLE IF EXISTS assessments;

CREATE TABLE assessments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    academic_year_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NOT NULL,

    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(255) NOT NULL,

    assessment_type ENUM(
        'assignment',
        'cat',
        'exam',
        'project',
        'practical',
        'quiz',
        'other'
    ) NOT NULL DEFAULT 'exam',

    max_score DECIMAL(8,2) NOT NULL,

    assessment_date DATE DEFAULT NULL,

    status ENUM(
        'draft',
        'published',
        'closed'
    ) NOT NULL DEFAULT 'draft',

    created_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_assessment_class (
        school_id,
        class_id,
        term_id
    ),

    KEY idx_assessment_subject (
        subject_id,
        term_id
    ),

    CONSTRAINT fk_assessment_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_assessment_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessment_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessment_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessment_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessment_creator
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT chk_assessment_max_score
        CHECK (max_score > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 24. RESULTS
-- ============================================================

DROP TABLE IF EXISTS results;

CREATE TABLE results (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    assessment_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    score DECIMAL(8,2) DEFAULT NULL,

    grade VARCHAR(20) DEFAULT NULL,
    points DECIMAL(8,2) DEFAULT NULL,

    teacher_comment TEXT DEFAULT NULL,

    status ENUM(
        'present',
        'absent',
        'excused',
        'pending'
    ) NOT NULL DEFAULT 'present',

    entered_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_assessment_student (
        assessment_id,
        student_id
    ),

    KEY idx_result_student (
        school_id,
        student_id
    ),

    CONSTRAINT fk_result_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_result_assessment
        FOREIGN KEY (assessment_id)
        REFERENCES assessments(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_result_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_result_entered_by
        FOREIGN KEY (entered_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 25. GRADING SYSTEMS
-- ============================================================

DROP TABLE IF EXISTS grading_systems;

CREATE TABLE grading_systems (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,

    description VARCHAR(500) DEFAULT NULL,

    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_grading_school (school_id),

    CONSTRAINT fk_grading_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 26. GRADES
-- ============================================================

DROP TABLE IF EXISTS grades;

CREATE TABLE grades (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    grading_system_id BIGINT UNSIGNED NOT NULL,

    grade_code VARCHAR(20) NOT NULL,
    grade_name VARCHAR(100) DEFAULT NULL,

    min_percentage DECIMAL(6,2) NOT NULL,
    max_percentage DECIMAL(6,2) NOT NULL,

    points DECIMAL(8,2) DEFAULT NULL,

    comment VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_grading_grade (
        grading_system_id,
        grade_code
    ),

    CONSTRAINT fk_grade_system
        FOREIGN KEY (grading_system_id)
        REFERENCES grading_systems(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_grade_range
        CHECK (
            min_percentage >= 0
            AND max_percentage <= 100
            AND max_percentage >= min_percentage
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 27. REPORT CARDS
-- ============================================================

DROP TABLE IF EXISTS report_cards;

CREATE TABLE report_cards (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    public_id CHAR(36) NOT NULL,

    school_id BIGINT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NOT NULL,

    class_id BIGINT UNSIGNED NOT NULL,
    stream_id BIGINT UNSIGNED DEFAULT NULL,

    total_marks DECIMAL(12,2) DEFAULT NULL,
    average_marks DECIMAL(8,2) DEFAULT NULL,

    position INT UNSIGNED DEFAULT NULL,
    class_size INT UNSIGNED DEFAULT NULL,

    teacher_comment TEXT DEFAULT NULL,
    principal_comment TEXT DEFAULT NULL,

    status ENUM(
        'draft',
        'published',
        'archived'
    ) NOT NULL DEFAULT 'draft',

    published_at TIMESTAMP NULL DEFAULT NULL,
    published_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_report_card_public_id (public_id),

    UNIQUE KEY uq_student_term_report (
        student_id,
        academic_year_id,
        term_id
    ),

    KEY idx_report_card_school (
        school_id,
        academic_year_id,
        term_id
    ),

    CONSTRAINT fk_report_card_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_report_card_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_report_card_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_report_card_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_report_card_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_report_card_stream
        FOREIGN KEY (stream_id)
        REFERENCES streams(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_report_card_publisher
        FOREIGN KEY (published_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 28. REPORT CARD ITEMS
-- ============================================================

DROP TABLE IF EXISTS report_card_items;

CREATE TABLE report_card_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    report_card_id BIGINT UNSIGNED NOT NULL,

    subject_id BIGINT UNSIGNED NOT NULL,

    score DECIMAL(8,2) DEFAULT NULL,
    grade VARCHAR(20) DEFAULT NULL,
    points DECIMAL(8,2) DEFAULT NULL,

    teacher_comment TEXT DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_report_subject (
        report_card_id,
        subject_id
    ),

    CONSTRAINT fk_report_item_report
        FOREIGN KEY (report_card_id)
        REFERENCES report_cards(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_report_item_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 29. ATTENDANCE REASONS
-- ============================================================

DROP TABLE IF EXISTS attendance_reasons;

CREATE TABLE attendance_reasons (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,

    is_excused BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_attendance_reason (
        school_id,
        name
    ),

    CONSTRAINT fk_attendance_reason_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 30. ATTENDANCE SESSIONS
-- ============================================================

DROP TABLE IF EXISTS attendance_sessions;

CREATE TABLE attendance_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    academic_year_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NOT NULL,

    class_id BIGINT UNSIGNED NOT NULL,
    stream_id BIGINT UNSIGNED DEFAULT NULL,

    attendance_date DATE NOT NULL,

    session_type ENUM(
        'daily',
        'morning',
        'afternoon',
        'lesson',
        'other'
    ) NOT NULL DEFAULT 'daily',

    created_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_attendance_session (
        class_id,
        stream_id,
        attendance_date,
        session_type
    ),

    KEY idx_attendance_session_school (
        school_id,
        attendance_date
    ),

    CONSTRAINT fk_attendance_session_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_attendance_session_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_attendance_session_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_attendance_session_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_attendance_session_stream
        FOREIGN KEY (stream_id)
        REFERENCES streams(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_attendance_session_creator
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 31. STUDENT ATTENDANCE
-- ============================================================

DROP TABLE IF EXISTS student_attendance;

CREATE TABLE student_attendance (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    attendance_session_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    status ENUM(
        'present',
        'absent',
        'late',
        'excused'
    ) NOT NULL DEFAULT 'present',

    reason_id BIGINT UNSIGNED DEFAULT NULL,

    notes VARCHAR(500) DEFAULT NULL,

    recorded_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_session_student (
        attendance_session_id,
        student_id
    ),

    KEY idx_student_attendance (
        school_id,
        student_id
    ),

    CONSTRAINT fk_student_attendance_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_student_attendance_session
        FOREIGN KEY (attendance_session_id)
        REFERENCES attendance_sessions(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_student_attendance_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_student_attendance_reason
        FOREIGN KEY (reason_id)
        REFERENCES attendance_reasons(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_student_attendance_recorder
        FOREIGN KEY (recorded_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 32. FEE STRUCTURES
-- ============================================================

DROP TABLE IF EXISTS fee_structures;

CREATE TABLE fee_structures (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    academic_year_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED DEFAULT NULL,

    class_id BIGINT UNSIGNED DEFAULT NULL,

    name VARCHAR(255) NOT NULL,

    description VARCHAR(500) DEFAULT NULL,

    status ENUM(
        'draft',
        'active',
        'closed'
    ) NOT NULL DEFAULT 'draft',

    created_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_fee_structure_school (
        school_id,
        academic_year_id,
        term_id
    ),

    CONSTRAINT fk_fee_structure_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_fee_structure_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_fee_structure_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_fee_structure_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_fee_structure_creator
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 33. FEE ITEMS
-- ============================================================

DROP TABLE IF EXISTS fee_items;

CREATE TABLE fee_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    fee_structure_id BIGINT UNSIGNED NOT NULL,

    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,

    description VARCHAR(500) DEFAULT NULL,

    amount DECIMAL(12,2) NOT NULL,

    mandatory BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_fee_item_code (
           fee_structure_id,
        code
    ),

    CONSTRAINT fk_fee_item_structure
        FOREIGN KEY (fee_structure_id)
        REFERENCES fee_structures(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_fee_item_amount
        CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 34. STUDENT INVOICES
-- ============================================================

DROP TABLE IF EXISTS student_invoices;

CREATE TABLE student_invoices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    public_id CHAR(36) NOT NULL,

    school_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    academic_year_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED DEFAULT NULL,

    invoice_number VARCHAR(100) NOT NULL,

    issue_date DATE NOT NULL,
    due_date DATE DEFAULT NULL,

    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    balance_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    status ENUM(
        'draft',
        'issued',
        'partially_paid',
        'paid',
        'overdue',
        'cancelled'
    ) NOT NULL DEFAULT 'draft',

    notes TEXT DEFAULT NULL,

    created_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_invoice_public_id (public_id),

    UNIQUE KEY uq_invoice_number (
        school_id,
        invoice_number
    ),

    KEY idx_invoice_student (
        school_id,
        student_id,
        status
    ),

    KEY idx_invoice_due_date (
        school_id,
        due_date,
        status
    ),

    CONSTRAINT fk_invoice_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_invoice_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_invoice_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_invoice_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_invoice_creator
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 35. INVOICE ITEMS
-- ============================================================

DROP TABLE IF EXISTS invoice_items;

CREATE TABLE invoice_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    invoice_id BIGINT UNSIGNED NOT NULL,

    fee_item_id BIGINT UNSIGNED DEFAULT NULL,

    description VARCHAR(255) NOT NULL,

    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_invoice_items_invoice (invoice_id),

    CONSTRAINT fk_invoice_item_invoice
        FOREIGN KEY (invoice_id)
        REFERENCES student_invoices(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_invoice_item_fee
        FOREIGN KEY (fee_item_id)
        REFERENCES fee_items(id)
        ON DELETE SET NULL,

    CONSTRAINT chk_invoice_item_quantity
        CHECK (quantity > 0),

    CONSTRAINT chk_invoice_item_amount
        CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
=========
-- 36. PAYMENTS
-- ============================================================

DROP TABLE IF EXISTS payments;

CREATE TABLE payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    public_id CHAR(36) NOT NULL,

    school_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED DEFAULT NULL,

    payment_reference VARCHAR(100) NOT NULL,

    amount DECIMAL(14,2) NOT NULL,

    payment_method ENUM(
        'cash',
        'mpesa',
        'bank',
        'card',
        'cheque',
        'other'
    ) NOT NULL,

    payment_date DATETIME NOT NULL,

    payer_name VARCHAR(255) DEFAULT NULL,
    payer_phone VARCHAR(30) DEFAULT NULL,

    external_reference VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'pending',
        'completed',
        'failed',
        'reversed',
        'refunded'
    ) NOT NULL DEFAULT 'completed',

    notes VARCHAR(500) DEFAULT NULL,

    received_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payment_public_id (public_id),

    UNIQUE KEY uq_payment_reference (
        school_id,
        payment_reference
    ),

    KEY idx_payment_student (
        school_id,
        student_id
    ),

    KEY idx_payment_external (
        external_reference
    ),

    KEY idx_payment_date (
        school_id,
        payment_date
    ),

    CONSTRAINT fk_payment_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_payment_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_payment_receiver
        FOREIGN KEY (received_by)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT chk_payment_amount
        CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 37. PAYMENT ALLOCATIONS
-- ============================================================

DROP TABLE IF EXISTS payment_allocations;

CREATE TABLE payment_allocations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    payment_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,

    amount DECIMAL(14,2) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payment_invoice (
        payment_id,
        invoice_id
    ),

    KEY idx_allocation_invoice (invoice_id),

    CONSTRAINT fk_allocation_payment
        FOREIGN KEY (payment_id)
        REFERENCES payments(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_allocation_invoice
        FOREIGN KEY (invoice_id)
        REFERENCES student_invoices(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_allocation_amount
        CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 38. DISCOUNTS
-- ============================================================

DROP TABLE IF EXISTS discounts;

CREATE TABLE discounts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    invoice_id BIGINT UNSIGNED DEFAULT NULL,

    name VARCHAR(150) NOT NULL,

    discount_type ENUM(
        'fixed',
        'percentage'
    ) NOT NULL,

    value DECIMAL(14,2) NOT NULL,

    reason VARCHAR(500) DEFAULT NULL,

    approved_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_discount_student (
        school_id,
        student_id
    ),

    CONSTRAINT fk_discount_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_discount_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_discount_invoice
        FOREIGN KEY (invoice_id)
        REFERENCES student_invoices(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_discount_approver
        FOREIGN KEY (approved_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
============================================================
-- 39. M-PESA STK REQUESTS
-- ============================================================

DROP TABLE IF EXISTS mpesa_stk_requests;

CREATE TABLE mpesa_stk_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED DEFAULT NULL,

    payment_id BIGINT UNSIGNED DEFAULT NULL,

    merchant_request_id VARCHAR(255) DEFAULT NULL,
    checkout_request_id VARCHAR(255) DEFAULT NULL,

    phone_number VARCHAR(30) NOT NULL,

    amount DECIMAL(14,2) NOT NULL,

    account_reference VARCHAR(255) DEFAULT NULL,
    transaction_description VARCHAR(255) DEFAULT NULL,

    response_code VARCHAR(50) DEFAULT NULL,
    response_description VARCHAR(500) DEFAULT NULL,

    result_code VARCHAR(50) DEFAULT NULL,
    result_description VARCHAR(500) DEFAULT NULL,

    status ENUM(
        'pending',
        'submitted',
        'completed',
        'failed',
        'cancelled',
        'timeout'
    ) NOT NULL DEFAULT 'pending',

    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,

    raw_response JSON DEFAULT NULL,

    PRIMARY KEY (id),

    UNIQUE KEY uq_checkout_request (
        checkout_request_id
    ),

    UNIQUE KEY uq_merchant_request (
        merchant_request_id
    ),

    KEY idx_mpesa_stk_school (
        school_id,
        status
    ),

    KEY idx_mpesa_stk_student (
        student_id
    ),

    CONSTRAINT fk_stk_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_stk_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_stk_payment
        FOREIGN KEY (payment_id)
        REFERENCES payments(id)
        ON DELETE SET NULL,

    CONSTRAINT chk_stk_amount
        CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 40. M-PESA TRANSACTIONS
-- ============================================================

DROP TABLE IF EXISTS mpesa_transactions;

CREATE TABLE mpesa_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    payment_id BIGINT UNSIGNED DEFAULT NULL,
    student_id BIGINT UNSIGNED DEFAULT NULL,

    transaction_id VARCHAR(100) NOT NULL,

    transaction_type VARCHAR(100) DEFAULT NULL,

    transaction_time DATETIME DEFAULT NULL,

    amount DECIMAL(14,2) NOT NULL,

    phone_number VARCHAR(30) DEFAULT NULL,

    sender_name VARCHAR(255) DEFAULT NULL,

    account_reference VARCHAR(255) DEFAULT NULL,

    merchant_request_id VARCHAR(255) DEFAULT NULL,
    checkout_request_id VARCHAR(255) DEFAULT NULL,

    result_code VARCHAR(50) DEFAULT NULL,
    result_description VARCHAR(500) DEFAULT NULL,

    status ENUM(
        'received',
        'processed',
        'failed',
        'reversed',
        'duplicate'
    ) NOT NULL DEFAULT 'received',

    raw_payload JSON DEFAULT NULL,

    processed_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_mpesa_transaction (
        transaction_id
    ),

    KEY idx_mpesa_school (
        school_id,
        transaction_time
    ),

    KEY idx_mpesa_student (
        student_id
    ),

    KEY idx_mpesa_checkout (
        checkout_request_id
    ),

    CONSTRAINT fk_mpesa_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_mpesa_payment
        FOREIGN KEY (payment_id)
        REFERENCES payments(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_mpesa_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE SET NULL,

    CONSTRAINT chk_mpesa_amount
        CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 41. M-PESA CALLBACKS
-- ============================================================

DROP TABLE IF EXISTS mpesa_callbacks;

CREATE TABLE mpesa_callbacks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED DEFAULT NULL,

    checkout_request_id VARCHAR(255) DEFAULT NULL,
    merchant_request_id VARCHAR(255) DEFAULT NULL,

    callback_type VARCHAR(100) DEFAULT NULL,

    payload JSON NOT NULL,

    processing_status ENUM(
        'received',
        'processed',
        'failed',
        'duplicate'
    ) NOT NULL DEFAULT 'received',

    error_message VARCHAR(1000) DEFAULT NULL,

    received_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (id),

    KEY idx_callback_checkout (
        checkout_request_id
    ),

    KEY idx_callback_status (
        processing_status
    ),

    CONSTRAINT fk_callback_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 42. SMS TEMPLATES
-- ============================================================

DROP TABLE IF EXISTS sms_templates;

CREATE TABLE sms_templates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,

    message_template TEXT NOT NULL,

    category ENUM(
        'fees',
        'attendance',
        'results',
        'announcement',
        'security',
        'general'
    ) NOT NULL DEFAULT 'general',

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_sms_template (
        school_id,
        name
    ),

    CONSTRAINT fk_sms_template_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
============================================================
-- 43. SMS LOGS
-- ============================================================

DROP TABLE IF EXISTS sms_logs;

CREATE TABLE sms_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED DEFAULT NULL,
    guardian_id BIGINT UNSIGNED DEFAULT NULL,

    recipient VARCHAR(30) NOT NULL,

    message TEXT NOT NULL,

    category VARCHAR(100) DEFAULT NULL,

    provider VARCHAR(100) DEFAULT NULL,
    provider_reference VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'pending',
        'queued',
        'sent',
        'delivered',
        'failed'
    ) NOT NULL DEFAULT 'pending',

    error_message VARCHAR(1000) DEFAULT NULL,

    sent_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_sms_school (
        school_id,
        status,
        created_at
    ),

    KEY idx_sms_recipient (recipient),

    CONSTRAINT fk_sms_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_sms_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_sms_guardian
        FOREIGN KEY (guardian_id)
        REFERENCES guardians(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 44. NOTIFICATIONS
-- ============================================================

DROP TABLE IF EXISTS notifications;

CREATE TABLE notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED DEFAULT NULL,
    guardian_id BIGINT UNSIGNED DEFAULT NULL,
    student_id BIGINT UNSIGNED DEFAULT NULL,

    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,

    notification_type ENUM(
        'system',
        'fees',
        'attendance',
        'results',
        'announcement',
        'security',
        'general'
    ) NOT NULL DEFAULT 'general',

    channel ENUM(
        'in_app',
        'push',
        'email',
        'sms',
        'all'
    ) NOT NULL DEFAULT 'in_app',

    data JSON DEFAULT NULL,

    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    read_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_notification_user (
        user_id,
        is_read,
        created_at
    ),

    KEY idx_notification_guardian (
        guardian_id,
        is_read
    ),

    CONSTRAINT fk_notification_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notification_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notification_guardian
        FOREIGN KEY (guardian_id)
        REFERENCES guardians(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notification_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 45. NOTIFICATION PREFERENCES
-- ============================================================

DROP TABLE IF EXISTS notification_preferences;

CREATE TABLE notification_preferences (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED DEFAULT NULL,
    guardian_id BIGINT UNSIGNED DEFAULT NULL,

    sms_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    email_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    push_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    in_app_enabled BOOLEAN NOT NULL DEFAULT TRUE,

    fee_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    attendance_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    result_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    announcement_notifications BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_pref_user (user_id),
    UNIQUE KEY uq_pref_guardian (guardian_id),

    CONSTRAINT fk_pref_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_pref_guardian
        FOREIGN KEY (guardian_id)
        REFERENCES guardians(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 46. ANNOUNCEMENTS
-- ============================================================

DROP TABLE IF EXISTS announcements;

CREATE TABLE announcements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,

    audience ENUM(
        'all',
        'parents',
        'students',
        'teachers',
        'staff'
    ) NOT NULL DEFAULT 'all',

    status ENUM(
        'draft',
        'published',
        'archived'
    ) NOT NULL DEFAULT 'draft',

    publish_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,

    created_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_announcement_school (
        school_id,
        status,
        publish_at
    ),

    CONSTRAINT fk_announcement_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_announcement_creator
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 47. SUBSCRIPTION PLANS
-- ============================================================

DROP TABLE IF EXISTS subscription_plans;

CREATE TABLE subscription_plans (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,

    description VARCHAR(1000) DEFAULT NULL,

    billing_interval ENUM(
        'monthly',
        'quarterly',
        'yearly'
    ) NOT NULL DEFAULT 'monthly',

    price DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    max_students INT UNSIGNED DEFAULT NULL,
    max_staff INT UNSIGNED DEFAULT NULL,
    max_branches INT UNSIGNED DEFAULT NULL,

    features JSON DEFAULT NULL,

    is_trial BOOLEAN NOT NULL DEFAULT FALSE,
    trial_days INT UNSIGNED NOT NULL DEFAULT 0,

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_plan_slug (slug),

    CONSTRAINT chk_plan_price
        CHECK (price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 48. PLAN FEATURES
-- ============================================================

DROP TABLE IF EXISTS plan_features;

CREATE TABLE plan_features (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    plan_id BIGINT UNSIGNED NOT NULL,

    feature_key VARCHAR(150) NOT NULL,
    feature_name VARCHAR(255) NOT NULL,

    enabled BOOLEAN NOT NULL DEFAULT TRUE,

    feature_limit INT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_plan_feature (
        plan_id,
        feature_key
    ),

    CONSTRAINT fk_plan_feature_plan
        FOREIGN KEY (plan_id)
        REFERENCES subscription_plans(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 49. SCHOOL SUBSCRIPTIONS
-- ============================================================

DROP TABLE IF EXISTS school_subscriptions;

CREATE TABLE school_subscriptions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,

    trial_end_date DATE DEFAULT NULL,

    status ENUM(
        'trial',
        'active',
        'past_due',
        'suspended',
        'cancelled',
        'expired'
    ) NOT NULL DEFAULT 'trial',

    auto_renew BOOLEAN NOT NULL DEFAULT TRUE,

    cancelled_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_subscription_school (
        school_id,
        status
    ),

    KEY idx_subscription_expiry (
        end_date,
        status
    ),

    CONSTRAINT fk_subscription_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_subscription_plan
        FOREIGN KEY (plan_id)
        REFERENCES subscription_plans(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 50. SUBSCRIPTION INVOICES
-- ============================================================

DROP TABLE IF EXISTS subscription_invoices;

CREATE TABLE subscription_invoices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    subscription_id BIGINT UNSIGNED NOT NULL,

    invoice_number VARCHAR(100) NOT NULL,

    amount DECIMAL(14,2) NOT NULL,

    issue_date DATE NOT NULL,
    due_date DATE DEFAULT NULL,

    status ENUM(
        'draft',
        'issued',
        'paid',
        'overdue',
        'cancelled'
    ) NOT NULL DEFAULT 'issued',

    paid_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_subscription_invoice (
        school_id,
        invoice_number
    ),

    CONSTRAINT fk_subscription_invoice_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_subscription_invoice_subscription
        FOREIGN KEY (subscription_id)
        REFERENCES school_subscriptions(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 51. SUBSCRIPTION PAYMENTS
-- ============================================================

DROP TABLE IF EXISTS subscription_payments;

CREATE TABLE subscription_payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    subscription_invoice_id BIGINT UNSIGNED NOT NULL,

    payment_reference VARCHAR(255) NOT NULL,

    amount DECIMAL(14,2) NOT NULL,

    payment_method ENUM(
        'mpesa',
        'bank',
        'card',
        'other'
    ) NOT NULL,

    external_reference VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'pending',
        'completed',
        'failed',
        'refunded'
    ) NOT NULL DEFAULT 'pending',

    paid_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_subscription_payment_reference (
        payment_reference
    ),

    CONSTRAINT fk_subscription_payment_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_subscription_payment_invoice
        FOREIGN KEY (subscription_invoice_id)
        REFERENCES subscription_invoices(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 52. USER DEVICES
-- ============================================================

DROP TABLE IF EXISTS user_devices;

CREATE TABLE user_devices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    device_id VARCHAR(255) NOT NULL,

    device_name VARCHAR(255) DEFAULT NULL,

    platform ENUM(
        'android',
        'ios',
        'web',
        'desktop',
        'other'
    ) NOT NULL DEFAULT 'android',

    app_version VARCHAR(50) DEFAULT NULL,

    push_token VARCHAR(1000) DEFAULT NULL,

    last_seen_at TIMESTAMP NULL DEFAULT NULL,

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_user_device (
        user_id,
        device_id
    ),

    KEY idx_device_push_token (push_token),

    CONSTRAINT fk_device_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 53. API CLIENTS
-- ============================================================

DROP TABLE IF EXISTS api_clients;

CREATE TABLE api_clients (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED DEFAULT NULL,

    name VARCHAR(255) NOT NULL,

    client_id VARCHAR(255) NOT NULL,
    client_secret_hash VARCHAR(255) DEFAULT NULL,

    type ENUM(
        'web',
        'mobile',
        'server',
        'integration'
    ) NOT NULL DEFAULT 'web',

    status ENUM(
        'active',
        'revoked',
        'disabled'
    ) NOT NULL DEFAULT 'active',

    last_used_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_api_client_id (client_id),

    CONSTRAINT fk_api_client_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 54. API TOKENS
-- ============================================================

DROP TABLE IF EXISTS api_tokens;

CREATE TABLE api_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED DEFAULT NULL,
    api_client_id BIGINT UNSIGNED DEFAULT NULL,

    token_hash CHAR(64) NOT NULL,

    name VARCHAR(255) DEFAULT NULL,

    scopes JSON DEFAULT NULL,

    expires_at TIMESTAMP NULL DEFAULT NULL,
    last_used_at TIMESTAMP NULL DEFAULT NULL,

    revoked_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_api_token_hash (
        token_hash
    ),

    KEY idx_api_token_user (
        user_id
    ),

    CONSTRAINT fk_api_token_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_api_token_client
        FOREIGN KEY (api_client_id)
        REFERENCES api_clients(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 55. API LOGS
-- ============================================================

DROP TABLE IF EXISTS api_logs;

CREATE TABLE api_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,

    method VARCHAR(20) NOT NULL,
    endpoint VARCHAR(500) NOT NULL,

    request_id CHAR(36) DEFAULT NULL,

    status_code SMALLINT UNSIGNED DEFAULT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(1000) DEFAULT NULL,

    response_time_ms INT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_api_log_school (
        school_id,
        created_at
    ),

    KEY idx_api_log_request (
        request_id
    ),

    CONSTRAINT fk_api_log_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_api_log_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
============================================================
-- 56. LOGIN ATTEMPTS
-- ============================================================

DROP TABLE IF EXISTS login_attempts;

CREATE TABLE login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED DEFAULT NULL,

    identifier VARCHAR(255) NOT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,

    user_agent VARCHAR(1000) DEFAULT NULL,

    status ENUM(
        'success',
        'failed',
        'blocked'
    ) NOT NULL,

    failure_reason VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_login_identifier (
        identifier,
        created_at
    ),

    KEY idx_login_user (
        user_id,
        created_at
    ),

    CONSTRAINT fk_login_attempt_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 57. PASSWORD RESETS
-- ============================================================

DROP TABLE IF EXISTS password_resets;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    token_hash CHAR(64) NOT NULL,

    expires_at TIMESTAMP NOT NULL,

    used_at TIMESTAMP NULL DEFAULT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_password_reset_token (
        token_hash
    ),

    KEY idx_password_reset_user (
        user_id,
        expires_at
    ),

    CONSTRAINT fk_password_reset_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 58. SECURITY EVENTS
-- ============================================================

DROP TABLE IF EXISTS security_events;

CREATE TABLE security_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,

    event_type VARCHAR(150) NOT NULL,

    severity ENUM(
        'info',
        'low',
        'medium',
        'high',
        'critical'
    ) NOT NULL DEFAULT 'info',

    description VARCHAR(1000) DEFAULT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(1000) DEFAULT NULL,

    metadata JSON DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_security_event (
        school_id,
        severity,
        created_at
    ),

    CONSTRAINT fk_security_event_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_security_event_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 59. AUDIT LOGS
-- ============================================================

DROP TABLE IF EXISTS audit_logs;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,

    action VARCHAR(100) NOT NULL,

    entity_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(100) DEFAULT NULL,

    old_values JSON DEFAULT NULL,
    new_values JSON DEFAULT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(1000) DEFAULT NULL,

    request_id CHAR(36) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_audit_school (
        school_id,
        created_at
    ),

    KEY idx_audit_entity (
        entity_type,
        entity_id
    ),

    KEY idx_audit_user (
        user_id,
        created_at
    ),

    CONSTRAINT fk_audit_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 60. STAFF ATTENDANCE
-- ============================================================

DROP TABLE IF EXISTS staff_attendance;

CREATE TABLE staff_attendance (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    staff_id BIGINT UNSIGNED NOT NULL,

    attendance_date DATE NOT NULL,

    clock_in DATETIME DEFAULT NULL,
    clock_out DATETIME DEFAULT NULL,

    status ENUM(
        'present',
        'absent',
        'late',
        'leave',
        'holiday'
    ) NOT NULL DEFAULT 'present',

    notes VARCHAR(500) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_staff_attendance (
        staff_id,
        attendance_date
    ),

    KEY idx_staff_attendance_school (
        school_id,
        attendance_date
    ),

    CONSTRAINT fk_staff_attendance_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_staff_attendance_staff
        FOREIGN KEY (staff_id)
        REFERENCES staff(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 61. DEPARTMENTS
-- ============================================================

DROP TABLE IF EXISTS departments;

CREATE TABLE departments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,

    description VARCHAR(500) DEFAULT NULL,

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_department_code (
        school_id,
        code
    ),

    CONSTRAINT fk_department_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 62. STAFF DEPARTMENTS
-- ============================================================

DROP TABLE IF EXISTS staff_departments;

CREATE TABLE staff_departments (
    staff_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NOT NULL,

    is_primary BOOLEAN NOT NULL DEFAULT FALSE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (staff_id, department_id),

    CONSTRAINT fk_staff_department_staff
        FOREIGN KEY (staff_id)
        REFERENCES staff(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_staff_department_department
        FOREIGN KEY (department_id)
        REFERENCES departments(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 63. STAFF ASSIGNMENTS
-- ============================================================

DROP TABLE IF EXISTS staff_assignments;

CREATE TABLE staff_assignments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    staff_id BIGINT UNSIGNED NOT NULL,

    class_id BIGINT UNSIGNED DEFAULT NULL,
    stream_id BIGINT UNSIGNED DEFAULT NULL,
    subject_id BIGINT UNSIGNED DEFAULT NULL,

    academic_year_id BIGINT UNSIGNED NOT NULL,

    assignment_type ENUM(
        'class_teacher',
        'subject_teacher',
        'administrator',
        'other'
    ) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_staff_assignment (
        school_id,
        academic_year_id
    ),

    CONSTRAINT fk_staff_assignment_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_staff_assignment_staff
        FOREIGN KEY (staff_id)
        REFERENCES staff(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_staff_assignment_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_staff_assignment_stream
        FOREIGN KEY (stream_id)
        REFERENCES streams(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_staff_assignment_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_staff_assignment_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 64. STUDENT DOCUMENTS
-- ============================================================

DROP TABLE IF EXISTS student_documents;

CREATE TABLE student_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    document_type VARCHAR(100) NOT NULL,
    document_name VARCHAR(255) NOT NULL,

    file_url VARCHAR(1000) NOT NULL,

    mime_type VARCHAR(150) DEFAULT NULL,
    file_size BIGINT UNSIGNED DEFAULT NULL,

    uploaded_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_student_document (
        student_id
    ),

    CONSTRAINT fk_student_document_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_student_document_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_student_document_user
        FOREIGN KEY (uploaded_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 65. ADMISSIONS
-- ============================================================

DROP TABLE IF EXISTS admissions;

CREATE TABLE admissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED DEFAULT NULL,

    application_number VARCHAR(100) NOT NULL,

    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,

    date_of_birth DATE DEFAULT NULL,

    gender ENUM(
        'male',
        'female',
        'other',
        'undisclosed'
    ) DEFAULT 'undisclosed',

    requested_class_id BIGINT UNSIGNED DEFAULT NULL,

    guardian_name VARCHAR(255) DEFAULT NULL,
    guardian_phone VARCHAR(30) DEFAULT NULL,
    guardian_email VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'submitted',
        'reviewing',
        'accepted',
        'rejected',
        'waitlisted',
        'enrolled'
    ) NOT NULL DEFAULT 'submitted',

    application_date DATE NOT NULL,

    reviewed_by BIGINT UNSIGNED DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,

    notes TEXT DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_application_number (
        school_id,
        application_number
    ),

    CONSTRAINT fk_admission_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_admission_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_admission_class
        FOREIGN KEY (requested_class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_admission_reviewer
        FOREIGN KEY (reviewed_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 
============================================================
=========
-- 66. REFUNDS
-- ============================================================

DROP TABLE IF EXISTS refunds;

CREATE TABLE refunds (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id BIGINT UNSIGNED NOT NULL,

    payment_id BIGINT UNSIGNED NOT NULL,

    refund_reference VARCHAR(100) NOT NULL,

    amount DECIMAL(14,2) NOT NULL,

    reason VARCHAR(500) NOT NULL,

    status ENUM(
        'requested',
        'approved',
        'processed',
        'rejected'
    ) NOT NULL DEFAULT 'requested',

    requested_by BIGINT UNSIGNED DEFAULT NULL,
    approved_by BIGINT UNSIGNED DEFAULT NULL,

    processed_at TIMESTAMP NULL DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_refund_reference (
        school_id,
        refund_reference
    ),

    CONSTRAINT fk_refund_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_refund_payment
        FOREIGN KEY (payment_id)
        REFERENCES payments(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_refund_requester
        FOREIGN KEY (requested_by)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_refund_approver
        FOREIGN KEY (approved_by)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT chk_refund_amount
        CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 67. SYSTEM SEED DATA
-- ============================================================

INSERT INTO roles
    (name, slug, description)
VALUES
    ('Super Administrator', 'super_admin',
        'Full platform administration'),
    ('School Administrator', 'school_admin',
        'Full administration of a school'),
    ('Teacher', 'teacher',
        'Teacher access'),
    ('Finance Officer', 'finance_officer',
        'Fees and financial management'),
    ('Parent / Guardian', 'parent',
        'Parent and guardian portal access'),
    ('Student', 'student',
        'Student portal access'),
    ('Staff', 'staff',
        'General staff access')
ON DUPLICATE KEY UPDATE
    name = VALUES(name);


INSERT INTO permissions
    (name, slug, module, action)
VALUES
    ('View Dashboard', 'dashboard.view', 'dashboard', 'view'),

    ('View Students', 'students.view', 'students', 'view'),
    ('Create Students', 'students.create', 'students', 'create'),
    ('Update Students', 'students.update', 'students', 'update'),
    ('Delete Students', 'students.delete', 'students', 'delete'),

    ('View Guardians', 'guardians.view', 'guardians', 'view'),
    ('Create Guardians', 'guardians.create', 'guardians', 'create'),
    ('Update Guardians', 'guardians.update', 'guardians', 'update'),

    ('View Staff', 'staff.view', 'staff', 'view'),
    ('Create Staff', 'staff.create', 'staff', 'create'),
    ('Update Staff', 'staff.update', 'staff', 'update'),

    ('View Classes', 'classes.view', 'classes', 'view'),
    ('Manage Classes', 'classes.manage', 'classes', 'manage'),

    ('View Subjects', 'subjects.view', 'subjects', 'view'),
    ('Manage Subjects', 'subjects.manage', 'subjects', 'manage'),

    ('View Assessments', 'assessments.view', 'academics', 'view'),
    ('Create Assessments', 'assessments.create', 'academics', 'create'),
    ('Enter Results', 'results.create', 'academics', 'create'),
    ('Update Results', 'results.update', 'academics', 'update'),
    ('Publish Results', 'results.publish', 'academics', 'publish'),

    ('View Attendance', 'attendance.view', 'attendance', 'view'),
    ('Manage Attendance', 'attendance.manage', 'attendance', 'manage'),

    ('View Fees', 'fees.view', 'finance', 'view'),
    ('Manage Fees', 'fees.manage', 'finance', 'manage'),
    ('View Payments', 'payments.view', 'finance', 'view'),
    ('Record Payments', 'payments.create', 'finance', 'create'),

    ('View M-Pesa', 'mpesa.view', 'mpesa', 'view'),
    ('Process M-Pesa', 'mpesa.process', 'mpesa', 'process'),

    ('Send SMS', 'sms.send', 'communication', 'send'),
    ('View SMS Logs', 'sms.logs', 'communication', 'view'),

    ('Send Notifications', 'notifications.send', 'communication', 'send'),

    ('Manage Subscriptions', 'subscriptions.manage', 'subscriptions', 'manage'),

    ('View Audit Logs', 'audit.view', 'security', 'view'),

    ('Manage Users', 'users.manage', 'security', 'manage')
ON DUPLICATE KEY UPDATE
    name = VALUES(name);


-- ============================================================
-- 68. SUBSCRIPTION PLAN SEED DATA
-- ============================================================

INSERT INTO subscription_plans
(
    name,
    slug,
    description,
    billing_interval,
    price,
    max_students,
    max_staff,
    max_branches,
    is_trial,
    trial_days
)
VALUES
(
    'Starter',
    'starter',
    'Starter plan for small schools',
    'monthly',
    2500.00,
    300,
    30,
    1,
    FALSE,
    0
),
(
    'Professional',
    'professional',
    'Professional plan for growing schools',
    'monthly',
    5000.00,
    1000,
    100,
    3,
    FALSE,
    0
),
(
    'Enterprise',
    'enterprise',
    'Enterprise plan for large school groups',
    'monthly',
    10000.00,
    NULL,
    NULL,
    NULL,
    FALSE,
    0
),
(
    'Free Trial',
    'free_trial',
    'Free trial',
    'monthly',
    0.00,
    100,
    20,
    1,
    TRUE,
    30
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    price = VALUES(price);


-- ============================================================
-- 69. DEFAULT SYSTEM SETTINGS
-- ============================================================

INSERT INTO system_settings
(
    setting_key,
    setting_value,
    setting_type,
    description,
    is_public
)
VALUES
(
    'app_name',
    'THINKPLUS CLOUD',
    'string',
    'Application name',
    TRUE
),
(
    'app_version',
    '3.0.0',
    'string',
    'Current database/application version',
    TRUE
),
(
    'default_currency',
    'KES',
    'string',
    'Default currency',
    TRUE
),
(
    'default_timezone',
    'Africa/Nairobi',
    'string',
    'Default application timezone',
    TRUE
),
(
    'mpesa_enabled',
    'true',
    'boolean',
    'Enable M-Pesa integration',
    FALSE
),
(
    'sms_enabled',
    'true',
    'boolean',
    'Enable SMS integration',
    FALSE
),
(
    'maintenance_mode',
    'false',
    'boolean',
    'Application maintenance mode',
    FALSE
)
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value);


-- ============================================================
-- 70. RE-ENABLE FOREIGN KEYS
-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- 71. FINAL VERIFICATION
-- ============================================================

SELECT
    'THINKPLUS CLOUD DATABASE SCHEMA v3.0 INSTALLED' AS status,
    DATABASE() AS database_name,
    NOW() AS installed_at;


-- ============================================================
-- END OF THINKPLUS CLOUD DATABASE SCHEMA v3.0
-- ============================================================
=========
