-- 
============================================================
-- THINKPLUS CLOUD
-- MULTI-SCHOOL SAAS DATABASE
-- COMPLETE DATABASE SCHEMA v2.0
-- ============================================================
-- Author: Joseph Mbui
-- Copyright: © 2026 ThinkPlus Cloud
--
-- DATABASE:
--   MySQL 8.0+
--
-- ARCHITECTURE:
--   Multi-tenant / Multi-school
--
-- CORE:
--   Super Admin
--   School Admin
--   Teacher
--   Parent
--
-- PARENT PORTAL:
--   Fees
--   Payments
--   Balance
--   Fee Statement
--   Academic Progress
--   Report Cards
--   Attendance
--   School News
--   Messages
--   PDF Documents
--
-- PAYMENTS:
--   Cash
--   M-Pesa
--   Bank
--   Card
--   Other
--
-- ============================================================


SET SQL_MODE = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,
NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION';

SET time_zone = '+00:00';

SET NAMES utf8mb4;


-- ============================================================
-- 1. SCHOOLS
-- ============================================================

CREATE TABLE schools (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_name VARCHAR(150) NOT NULL,
    school_code VARCHAR(50) NOT NULL,

    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,

    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    county VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Kenya',

    logo VARCHAR(500) DEFAULT NULL,

    status ENUM(
        'active',
        'suspended',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_school_code (school_code),

    UNIQUE KEY uq_school_email (email),

    KEY idx_school_status (status)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. USERS
-- ============================================================

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED DEFAULT NULL,

    role ENUM(
        'super_admin',
        'school_admin',
        'teacher',
        'parent'
    ) NOT NULL DEFAULT 'parent',

    name VARCHAR(150) NOT NULL,

    email VARCHAR(150) DEFAULT NULL,

    phone VARCHAR(30) DEFAULT NULL,

    password VARCHAR(255) NOT NULL,

    status ENUM(
        'active',
        'suspended',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    last_login_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_user_email (email),

    UNIQUE KEY uq_user_phone (phone),

    KEY idx_user_school (school_id),

    KEY idx_user_role (role),

    CONSTRAINT fk_users_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. ACADEMIC YEARS
-- ============================================================

CREATE TABLE academic_years (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    year_label VARCHAR(20) NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    status ENUM(
        'upcoming',
        'active',
        'closed'
    ) NOT NULL DEFAULT 'upcoming',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_academic_year (
        school_id,
        year_label
    ),

    KEY idx_academic_school (school_id),

    CONSTRAINT fk_academic_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. TERMS
-- ============================================================

CREATE TABLE terms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    term_name ENUM(
        'Term 1',
        'Term 2',
        'Term 3'
    ) NOT NULL,

    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,

    status ENUM(
        'upcoming',
        'active',
        'closed'
    ) NOT NULL DEFAULT 'upcoming',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_term (
        academic_year_id,
        term_name
    ),

    KEY idx_terms_school (school_id),

    CONSTRAINT fk_terms_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_terms_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. CLASSES
-- ============================================================

CREATE TABLE classes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    class_name VARCHAR(100) NOT NULL,

    class_code VARCHAR(50) DEFAULT NULL,

    stream VARCHAR(50) DEFAULT NULL,

    class_teacher_id BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_class (
        school_id,
        academic_year_id,
        class_name,
        stream
    ),

    KEY idx_class_school (school_id),

    KEY idx_class_year (academic_year_id),

    KEY idx_class_teacher (class_teacher_id),

    CONSTRAINT fk_classes_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_classes_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_classes_teacher
        FOREIGN KEY (class_teacher_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. STUDENTS
-- ============================================================

CREATE TABLE students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    class_id INT UNSIGNED DEFAULT NULL,

    admission_no VARCHAR(50) NOT NULL,

    first_name VARCHAR(100) NOT NULL,

    middle_name VARCHAR(100) DEFAULT NULL,

    last_name VARCHAR(100) DEFAULT NULL,

    gender ENUM(
        'male',
        'female',
        'other'
    ) DEFAULT NULL,

    date_of_birth DATE DEFAULT NULL,

    parent_phone VARCHAR(30) DEFAULT NULL,

    admission_date DATE DEFAULT NULL,

    status ENUM(
        'active',
        'inactive',
        'graduated',
        'transferred'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_student_admission (
        school_id,
        admission_no
    ),

    KEY idx_student_school (school_id),

    KEY idx_student_class (class_id),

    CONSTRAINT fk_students_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_students_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. PARENT PROFILES
-- ============================================================

CREATE TABLE parents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    national_id VARCHAR(50) DEFAULT NULL,

    occupation VARCHAR(150) DEFAULT NULL,

    address VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_parent_user (user_id),

    CONSTRAINT fk_parents_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. PARENT ↔ STUDENT
-- ============================================================

CREATE TABLE parent_students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    parent_id BIGINT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    relationship ENUM(
        'father',
        'mother',
        'guardian',
        'other'
    ) NOT NULL DEFAULT 'guardian',

    is_primary TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_parent_student (
        parent_id,
        student_id
    ),

    KEY idx_ps_parent (parent_id),

    KEY idx_ps_student (student_id),

    CONSTRAINT fk_ps_parent
        FOREIGN KEY (parent_id)
        REFERENCES parents(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ps_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. SUBJECTS
-- ============================================================

CREATE TABLE subjects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    subject_name VARCHAR(100) NOT NULL,

    subject_code VARCHAR(30) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_subject_school (
        school_id,
        subject_name
    ),

    KEY idx_subject_school (school_id),

    CONSTRAINT fk_subject_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. STUDENT CLASS HISTORY
-- ============================================================

CREATE TABLE student_class_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    class_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    start_date DATE DEFAULT NULL,

    end_date DATE DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_sch_student (student_id),

    KEY idx_sch_class (class_id),

    KEY idx_sch_year (academic_year_id),

    CONSTRAINT fk_sch_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 11. FEE STRUCTURES
-- ============================================================

CREATE TABLE fee_structures (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    class_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    fee_name VARCHAR(150) NOT NULL,

    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    due_date DATE DEFAULT NULL,

    description VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'active',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_fee_structure_school (school_id),

    KEY idx_fee_structure_class (class_id),

    KEY idx_fee_structure_year (academic_year_id),

    KEY idx_fee_structure_term (term_id),

    CONSTRAINT fk_fee_structure_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 12. STUDENT FEES / CHARGES
-- ============================================================

CREATE TABLE fees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    fee_structure_id BIGINT UNSIGNED DEFAULT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    fee_name VARCHAR(150) NOT NULL,

    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    due_date DATE DEFAULT NULL,

    description VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_fees_school (school_id),

    KEY idx_fees_student (student_id),

    KEY idx_fees_structure (fee_structure_id),

    KEY idx_fees_year (academic_year_id),

    KEY idx_fees_term (term_id),

    CONSTRAINT fk_fees_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_structure
        FOREIGN KEY (fee_structure_id)
        REFERENCES fee_structures(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. PAYMENTS
-- ============================================================

CREATE TABLE payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    payment_method ENUM(
        'cash',
        'mpesa',
        'bank',
        'card',
        'other'
    ) NOT NULL DEFAULT 'cash',

    transaction_reference VARCHAR(100) DEFAULT NULL,

    phone VARCHAR(30) DEFAULT NULL,

    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    status ENUM(
        'pending',
        'completed',
        'failed',
        'reversed'
    ) NOT NULL DEFAULT 'completed',

    notes TEXT DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payment_reference (
        transaction_reference
    ),

    KEY idx_payment_school (school_id),

    KEY idx_payment_student (student_id),

    KEY idx_payment_date (payment_date),

    CONSTRAINT fk_payments_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_payments_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 14. PAYMENT ALLOCATIONS
-- ============================================================
-- Allows one payment to pay one or multiple fee charges.

CREATE TABLE payment_allocations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    payment_id BIGINT UNSIGNED NOT NULL,

    fee_id BIGINT UNSIGNED NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payment_fee (
        payment_id,
        fee_id
    ),

    KEY idx_allocation_fee (fee_id),

    CONSTRAINT fk_allocation_payment
        FOREIGN KEY (payment_id)
        REFERENCES payments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_allocation_fee
        FOREIGN KEY (fee_id)
        REFERENCES fees(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 15. FEE STATEMENTS
-- ============================================================
-- Stores generated statement metadata.
-- The actual balance should be calculated from fees/payments.

CREATE TABLE fee_statements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    statement_number VARCHAR(100) NOT NULL,

    total_charged DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    total_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    pdf_path VARCHAR(500) DEFAULT NULL,

    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_statement_number (
        statement_number
    ),

    KEY idx_statement_school (school_id),

    KEY idx_statement_student (student_id),

    KEY idx_statement_year (academic_year_id),

    KEY idx_statemYes. This is the SQL database schema, not the PHP application code.
Since you want ThinkPlus Cloud to support multiple schools and be future-compatible, I would use a stronger version than the earlier 20-table schema. It should support:
Multiple schools/tenants
Super Admin
School Admin
Teachers
Parents
Multiple children per parent
Students/classes/subjects
Fees and fee balances
Payment history
M-Pesa
Fee statements
Academic progress/results
Report cards
Attendance
School news/announcements
Parent-school messaging
Staff/payroll
SMS
Subscriptions
Documents/PDF records
Audit logs
Future expansion
The following is one complete SQL schema you can paste into your database SQL editor in one go.
-- ============================================================
-- THINKPLUS CLOUD
-- MULTI-SCHOOL SAAS DATABASE
-- COMPLETE DATABASE SCHEMA v2.0
-- ============================================================
-- Author: Joseph Mbui
-- Copyright: © 2026 ThinkPlus Cloud
--
-- DATABASE:
--   MySQL 8.0+
--
-- ARCHITECTURE:
--   Multi-tenant / Multi-school
--
-- CORE:
--   Super Admin
--   School Admin
--   Teacher
--   Parent
--
-- PARENT PORTAL:
--   Fees
--   Payments
--   Balance
--   Fee Statement
--   Academic Progress
--   Report Cards
--   Attendance
--   School News
--   Messages
--   PDF Documents
--
-- PAYMENTS:
--   Cash
--   M-Pesa
--   Bank
--   Card
--   Other
--
-- ============================================================


SET SQL_MODE = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,
NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION';

SET time_zone = '+00:00';

SET NAMES utf8mb4;


-- ============================================================
-- 1. SCHOOLS
-- ============================================================

CREATE TABLE schools (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_name VARCHAR(150) NOT NULL,
    school_code VARCHAR(50) NOT NULL,

    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,

    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    county VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Kenya',

    logo VARCHAR(500) DEFAULT NULL,

    status ENUM(
        'active',
        'suspended',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_school_code (school_code),

    UNIQUE KEY uq_school_email (email),

    KEY idx_school_status (status)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. USERS
-- ============================================================

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED DEFAULT NULL,

    role ENUM(
        'super_admin',
        'school_admin',
        'teacher',
        'parent'
    ) NOT NULL DEFAULT 'parent',

    name VARCHAR(150) NOT NULL,

    email VARCHAR(150) DEFAULT NULL,

    phone VARCHAR(30) DEFAULT NULL,

    password VARCHAR(255) NOT NULL,

    status ENUM(
        'active',
        'suspended',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    last_login_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_user_email (email),

    UNIQUE KEY uq_user_phone (phone),

    KEY idx_user_school (school_id),

    KEY idx_user_role (role),

    CONSTRAINT fk_users_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. ACADEMIC YEARS
-- ============================================================

CREATE TABLE academic_years (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    year_label VARCHAR(20) NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    status ENUM(
        'upcoming',
        'active',
        'closed'
    ) NOT NULL DEFAULT 'upcoming',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_academic_year (
        school_id,
        year_label
    ),

    KEY idx_academic_school (school_id),

    CONSTRAINT fk_academic_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. TERMS
-- ============================================================

CREATE TABLE terms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    term_name ENUM(
        'Term 1',
        'Term 2',
        'Term 3'
    ) NOT NULL,

    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,

    status ENUM(
        'upcoming',
        'active',
        'closed'
    ) NOT NULL DEFAULT 'upcoming',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_term (
        academic_year_id,
        term_name
    ),

    KEY idx_terms_school (school_id),

    CONSTRAINT fk_terms_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_terms_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. CLASSES
-- ============================================================

CREATE TABLE classes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    class_name VARCHAR(100) NOT NULL,

    class_code VARCHAR(50) DEFAULT NULL,

    stream VARCHAR(50) DEFAULT NULL,

    class_teacher_id BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_class (
        school_id,
        academic_year_id,
        class_name,
        stream
    ),

    KEY idx_class_school (school_id),

    KEY idx_class_year (academic_year_id),

    KEY idx_class_teacher (class_teacher_id),

    CONSTRAINT fk_classes_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_classes_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_classes_teacher
        FOREIGN KEY (class_teacher_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. STUDENTS
-- ============================================================

CREATE TABLE students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    class_id INT UNSIGNED DEFAULT NULL,

    admission_no VARCHAR(50) NOT NULL,

    first_name VARCHAR(100) NOT NULL,

    middle_name VARCHAR(100) DEFAULT NULL,

    last_name VARCHAR(100) DEFAULT NULL,

    gender ENUM(
        'male',
        'female',
        'other'
    ) DEFAULT NULL,

    date_of_birth DATE DEFAULT NULL,

    parent_phone VARCHAR(30) DEFAULT NULL,

    admission_date DATE DEFAULT NULL,

    status ENUM(
        'active',
        'inactive',
        'graduated',
        'transferred'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_student_admission (
        school_id,
        admission_no
    ),

    KEY idx_student_school (school_id),

    KEY idx_student_class (class_id),

    CONSTRAINT fk_students_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_students_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. PARENT PROFILES
-- ============================================================

CREATE TABLE parents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    national_id VARCHAR(50) DEFAULT NULL,

    occupation VARCHAR(150) DEFAULT NULL,

    address VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_parent_user (user_id),

    CONSTRAINT fk_parents_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. PARENT ↔ STUDENT
-- ============================================================

CREATE TABLE parent_students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    parent_id BIGINT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    relationship ENUM(
        'father',
        'mother',
        'guardian',
        'other'
    ) NOT NULL DEFAULT 'guardian',

    is_primary TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_parent_student (
        parent_id,
        student_id
    ),

    KEY idx_ps_parent (parent_id),

    KEY idx_ps_student (student_id),

    CONSTRAINT fk_ps_parent
        FOREIGN KEY (parent_id)
        REFERENCES parents(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ps_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. SUBJECTS
-- ============================================================

CREATE TABLE subjects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    subject_name VARCHAR(100) NOT NULL,

    subject_code VARCHAR(30) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_subject_school (
        school_id,
        subject_name
    ),

    KEY idx_subject_school (school_id),

    CONSTRAINT fk_subject_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. STUDENT CLASS HISTORY
-- ============================================================

CREATE TABLE student_class_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    class_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    start_date DATE DEFAULT NULL,

    end_date DATE DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_sch_student (student_id),

    KEY idx_sch_class (class_id),

    KEY idx_sch_year (academic_year_id),

    CONSTRAINT fk_sch_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 11. FEE STRUCTURES
-- ============================================================

CREATE TABLE fee_structures (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    class_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    fee_name VARCHAR(150) NOT NULL,

    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    due_date DATE DEFAULT NULL,

    description VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'active',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_fee_structure_school (school_id),

    KEY idx_fee_structure_class (class_id),

    KEY idx_fee_structure_year (academic_year_id),

    KEY idx_fee_structure_term (term_id),

    CONSTRAINT fk_fee_structure_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 12. STUDENT FEES / CHARGES
-- ============================================================

CREATE TABLE fees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    fee_structure_id BIGINT UNSIGNED DEFAULT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    fee_name VARCHAR(150) NOT NULL,

    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    due_date DATE DEFAULT NULL,

    description VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_fees_school (school_id),

    KEY idx_fees_student (student_id),

    KEY idx_fees_structure (fee_structure_id),

    KEY idx_fees_year (academic_year_id),

    KEY idx_fees_term (term_id),

    CONSTRAINT fk_fees_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_structure
        FOREIGN KEY (fee_structure_id)
        REFERENCES fee_structures(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. PAYMENTS
-- ============================================================

CREATE TABLE payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    payment_method ENUM(
        'cash',
        'mpesa',
        'bank',
        'card',
        'other'
    ) NOT NULL DEFAULT 'cash',

    transaction_reference VARCHAR(100) DEFAULT NULL,

    phone VARCHAR(30) DEFAULT NULL,

    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    status ENUM(
        'pending',
        'completed',
        'failed',
        'reversed'
    ) NOT NULL DEFAULT 'completed',

    notes TEXT DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payment_reference (
        transaction_reference
    ),

    KEY idx_payment_school (school_id),

    KEY idx_payment_student (student_id),

    KEY idx_payment_date (payment_date),

    CONSTRAINT fk_payments_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_payments_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 14. PAYMENT ALLOCATIONS
-- ============================================================
-- Allows one payment to pay one or multiple fee charges.

CREATE TABLE payment_allocations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    payment_id BIGINT UNSIGNED NOT NULL,

    fee_id BIGINT UNSIGNED NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payment_fee (
        payment_id,
        fee_id
    ),

    KEY idx_allocation_fee (fee_id),

    CONSTRAINT fk_allocation_payment
        FOREIGN KEY (payment_id)
        REFERENCES payments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_allocation_fee
        FOREIGN KEY (fee_id)
        REFERENCES fees(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 15. FEE STATEMENTS
-- ============================================================
-- Stores generated statement metadata.
-- The actual balance should be calculated from fees/payments.

CREATE TABLE fee_statements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    statement_number VARCHAR(100) NOT NULL,

    total_charged DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    total_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    pdf_path VARCHAR(500) DEFAULT NULL,

    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_statement_number (
        statement_number
    ),

    KEY idx_statement_school (school_id),

    KEY idx_statement_student (student_id),

    KEY idx_statement_year (academic_year_id),

    KEY idx_statemYes. This is the SQL database schema, not the PHP application code.
Since you want ThinkPlus Cloud to support multiple schools and be future-compatible, I would use a stronger version than the earlier 20-table schema. It should support:
Multiple schools/tenants
Super Admin
School Admin
Teachers
Parents
Multiple children per parent
Students/classes/subjects
Fees and fee balances
Payment history
M-Pesa
Fee statements
Academic progress/results
Report cards
Attendance
School news/announcements
Parent-school messaging
Staff/payroll
SMS
Subscriptions
Documents/PDF records
Audit logs
Future expansion
The following is one complete SQL schema you can paste into your database SQL editor in one go.
-- ============================================================
-- THINKPLUS CLOUD
-- MULTI-SCHOOL SAAS DATABASE
-- COMPLETE DATABASE SCHEMA v2.0
-- ============================================================
-- Author: Joseph Mbui
-- Copyright: © 2026 ThinkPlus Cloud
--
-- DATABASE:
--   MySQL 8.0+
--
-- ARCHITECTURE:
--   Multi-tenant / Multi-school
--
-- CORE:
--   Super Admin
--   School Admin
--   Teacher
--   Parent
--
-- PARENT PORTAL:
--   Fees
--   Payments
--   Balance
--   Fee Statement
--   Academic Progress
--   Report Cards
--   Attendance
--   School News
--   Messages
--   PDF Documents
--
-- PAYMENTS:
--   Cash
--   M-Pesa
--   Bank
--   Card
--   Other
--
-- ============================================================


SET SQL_MODE = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,
NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION';

SET time_zone = '+00:00';

SET NAMES utf8mb4;


-- ============================================================
-- 1. SCHOOLS
-- ============================================================

CREATE TABLE schools (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_name VARCHAR(150) NOT NULL,
    school_code VARCHAR(50) NOT NULL,

    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,

    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    county VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Kenya',

    logo VARCHAR(500) DEFAULT NULL,

    status ENUM(
        'active',
        'suspended',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_school_code (school_code),

    UNIQUE KEY uq_school_email (email),

    KEY idx_school_status (status)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. USERS
-- ============================================================

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED DEFAULT NULL,

    role ENUM(
        'super_admin',
        'school_admin',
        'teacher',
        'parent'
    ) NOT NULL DEFAULT 'parent',

    name VARCHAR(150) NOT NULL,

    email VARCHAR(150) DEFAULT NULL,

    phone VARCHAR(30) DEFAULT NULL,

    password VARCHAR(255) NOT NULL,

    status ENUM(
        'active',
        'suspended',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    last_login_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_user_email (email),

    UNIQUE KEY uq_user_phone (phone),

    KEY idx_user_school (school_id),

    KEY idx_user_role (role),

    CONSTRAINT fk_users_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. ACADEMIC YEARS
-- ============================================================

CREATE TABLE academic_years (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    year_label VARCHAR(20) NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    status ENUM(
        'upcoming',
        'active',
        'closed'
    ) NOT NULL DEFAULT 'upcoming',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_academic_year (
        school_id,
        year_label
    ),

    KEY idx_academic_school (school_id),

    CONSTRAINT fk_academic_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. TERMS
-- ============================================================

CREATE TABLE terms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    term_name ENUM(
        'Term 1',
        'Term 2',
        'Term 3'
    ) NOT NULL,

    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,

    status ENUM(
        'upcoming',
        'active',
        'closed'
    ) NOT NULL DEFAULT 'upcoming',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_term (
        academic_year_id,
        term_name
    ),

    KEY idx_terms_school (school_id),

    CONSTRAINT fk_terms_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_terms_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. CLASSES
-- ============================================================

CREATE TABLE classes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    class_name VARCHAR(100) NOT NULL,

    class_code VARCHAR(50) DEFAULT NULL,

    stream VARCHAR(50) DEFAULT NULL,

    class_teacher_id BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_class (
        school_id,
        academic_year_id,
        class_name,
        stream
    ),

    KEY idx_class_school (school_id),

    KEY idx_class_year (academic_year_id),

    KEY idx_class_teacher (class_teacher_id),

    CONSTRAINT fk_classes_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_classes_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_classes_teacher
        FOREIGN KEY (class_teacher_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. STUDENTS
-- ============================================================

CREATE TABLE students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    class_id INT UNSIGNED DEFAULT NULL,

    admission_no VARCHAR(50) NOT NULL,

    first_name VARCHAR(100) NOT NULL,

    middle_name VARCHAR(100) DEFAULT NULL,

    last_name VARCHAR(100) DEFAULT NULL,

    gender ENUM(
        'male',
        'female',
        'other'
    ) DEFAULT NULL,

    date_of_birth DATE DEFAULT NULL,

    parent_phone VARCHAR(30) DEFAULT NULL,

    admission_date DATE DEFAULT NULL,

    status ENUM(
        'active',
        'inactive',
        'graduated',
        'transferred'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_student_admission (
        school_id,
        admission_no
    ),

    KEY idx_student_school (school_id),

    KEY idx_student_class (class_id),

    CONSTRAINT fk_students_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_students_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. PARENT PROFILES
-- ============================================================

CREATE TABLE parents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    national_id VARCHAR(50) DEFAULT NULL,

    occupation VARCHAR(150) DEFAULT NULL,

    address VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_parent_user (user_id),

    CONSTRAINT fk_parents_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. PARENT ↔ STUDENT
-- ============================================================

CREATE TABLE parent_students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    parent_id BIGINT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    relationship ENUM(
        'father',
        'mother',
        'guardian',
        'other'
    ) NOT NULL DEFAULT 'guardian',

    is_primary TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_parent_student (
        parent_id,
        student_id
    ),

    KEY idx_ps_parent (parent_id),

    KEY idx_ps_student (student_id),

    CONSTRAINT fk_ps_parent
        FOREIGN KEY (parent_id)
        REFERENCES parents(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ps_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. SUBJECTS
-- ============================================================

CREATE TABLE subjects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    subject_name VARCHAR(100) NOT NULL,

    subject_code VARCHAR(30) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_subject_school (
        school_id,
        subject_name
    ),

    KEY idx_subject_school (school_id),

    CONSTRAINT fk_subject_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. STUDENT CLASS HISTORY
-- ============================================================

CREATE TABLE student_class_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    class_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    start_date DATE DEFAULT NULL,

    end_date DATE DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_sch_student (student_id),

    KEY idx_sch_class (class_id),

    KEY idx_sch_year (academic_year_id),

    CONSTRAINT fk_sch_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sch_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 11. FEE STRUCTURES
-- ============================================================

CREATE TABLE fee_structures (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    class_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    fee_name VARCHAR(150) NOT NULL,

    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    due_date DATE DEFAULT NULL,

    description VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'active',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_fee_structure_school (school_id),

    KEY idx_fee_structure_class (class_id),

    KEY idx_fee_structure_year (academic_year_id),

    KEY idx_fee_structure_term (term_id),

    CONSTRAINT fk_fee_structure_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_class
        FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fee_structure_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 12. STUDENT FEES / CHARGES
-- ============================================================

CREATE TABLE fees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    fee_structure_id BIGINT UNSIGNED DEFAULT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    fee_name VARCHAR(150) NOT NULL,

    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    due_date DATE DEFAULT NULL,

    description VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_fees_school (school_id),

    KEY idx_fees_student (student_id),

    KEY idx_fees_structure (fee_structure_id),

    KEY idx_fees_year (academic_year_id),

    KEY idx_fees_term (term_id),

    CONSTRAINT fk_fees_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_structure
        FOREIGN KEY (fee_structure_id)
        REFERENCES fee_structures(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. PAYMENTS
-- ============================================================

CREATE TABLE payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    payment_method ENUM(
        'cash',
        'mpesa',
        'bank',
        'card',
        'other'
    ) NOT NULL DEFAULT 'cash',

    transaction_reference VARCHAR(100) DEFAULT NULL,

    phone VARCHAR(30) DEFAULT NULL,

    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    status ENUM(
        'pending',
        'completed',
        'failed',
        'reversed'
    ) NOT NULL DEFAULT 'completed',

    notes TEXT DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payment_reference (
        transaction_reference
    ),

    KEY idx_payment_school (school_id),

    KEY idx_payment_student (student_id),

    KEY idx_payment_date (payment_date),

    CONSTRAINT fk_payments_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_payments_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 14. PAYMENT ALLOCATIONS
-- ============================================================
-- Allows one payment to pay one or multiple fee charges.

CREATE TABLE payment_allocations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    payment_id BIGINT UNSIGNED NOT NULL,

    fee_id BIGINT UNSIGNED NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payment_fee (
        payment_id,
        fee_id
    ),

    KEY idx_allocation_fee (fee_id),

    CONSTRAINT fk_allocation_payment
        FOREIGN KEY (payment_id)
        REFERENCES payments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_allocation_fee
        FOREIGN KEY (fee_id)
        REFERENCES fees(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 15. FEE STATEMENTS
-- ============================================================
-- Stores generated statement metadata.
-- The actual balance should be calculated from fees/payments.

CREATE TABLE fee_statements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    statement_number VARCHAR(100) NOT NULL,

    total_charged DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    total_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    pdf_path VARCHAR(500) DEFAULT NULL,

    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_statement_number (
        statement_number
    ),

    KEY idx_statement_school (school_id),

    KEY idx_statement_student (student_id),

    KEY idx_statement_year (academic_year_id),

     KEY idx_statement_term (term_id),

    CONSTRAINT fk_statement_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_statement_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_statement_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_statement_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 16. EXAMS
-- ============================================================

CREATE TABLE exams (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    academic_year_id INT UNSIGNED NOT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    exam_name VARCHAR(150) NOT NULL,

    start_date DATE DEFAULT NULL,

    end_date DATE DEFAULT NULL,

    status ENUM(
        'draft',
        'active',
        'completed',
        'published'
    ) NOT NULL DEFAULT 'draft',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_exam_school (school_id),

    KEY idx_exam_year (academic_year_id),

    KEY idx_exam_term (term_id),

    CONSTRAINT fk_exams_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_exams_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_exams_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 17. RESULTS / ACADEMIC PROGRESS
-- ============================================================

CREATE TABLE results (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    exam_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    subject_id INT UNSIGNED NOT NULL,

    score DECIMAL(5,2) NOT NULL,

    grade VARCHAR(20) DEFAULT NULL,

    teacher_comment VARCHAR(500) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_result (
        exam_id,
        student_id,
        subject_id
    ),

    KEY idx_results_school (school_id),

    KEY idx_results_student (student_id),

    KEY idx_results_exam (exam_id),

    CONSTRAINT fk_results_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_results_exam
        FOREIGN KEY (exam_id)
        REFERENCES exams(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_results_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_results_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 18. REPORT CARDS
-- ============================================================

CREATE TABLE report_cards (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    exam_id INT UNSIGNED DEFAULT NULL,

    academic_year_id INT UNSIGNED DEFAULT NULL,

    term_id INT UNSIGNED DEFAULT NULL,

    overall_score DECIMAL(6,2) DEFAULT NULL,

    overall_grade VARCHAR(20) DEFAULT NULL,

    teacher_comment TEXT DEFAULT NULL,

    principal_comment TEXT DEFAULT NULL,

    pdf_path VARCHAR(500) DEFAULT NULL,

    status ENUM(
        'draft',
        'published'
    ) NOT NULL DEFAULT 'draft',

    generated_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_report_card (
        student_id,
        exam_id
    ),

    KEY idx_report_school (school_id),

    KEY idx_report_student (student_id),

    CONSTRAINT fk_report_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_report_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_report_exam
        FOREIGN KEY (exam_id)
        REFERENCES exams(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_report_year
        FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_report_term
        FOREIGN KEY (term_id)
        REFERENCES terms(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 19. ATTENDANCE
-- ============================================================

CREATE TABLE attendance (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    attendance_date DATE NOT NULL,

    status ENUM(
        'present',
        'absent',
        'late',
        'excused'
    ) NOT NULL,

    remarks VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_attendance (
        student_id,
        attendance_date
    ),

    KEY idx_attendance_school (school_id),

    KEY idx_attendance_student (student_id),

    KEY idx_attendance_date (attendance_date),

    CONSTRAINT fk_attendance_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_attendance_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 20. STAFF
-- ============================================================

CREATE TABLE staff (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED DEFAULT NULL,

    employee_no VARCHAR(50) NOT NULL,

    full_name VARCHAR(150) NOT NULL,

    phone VARCHAR(30) DEFAULT NULL,

    email VARCHAR(150) DEFAULT NULL,

    position VARCHAR(100) DEFAULT NULL,

    department VARCHAR(100) DEFAULT NULL,

    salary DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    employment_status ENUM(
        'active',
        'inactive',
        'terminated'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_employee (
        school_id,
        employee_no
    ),

    KEY idx_staff_school (school_id),

    KEY idx_staff_user (user_id),

    CONSTRAINT fk_staff_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_staff_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
============================================================
-- 21. PAYROLL
-- ============================================================

CREATE TABLE payroll (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    staff_id BIGINT UNSIGNED NOT NULL,

    payroll_month DATE NOT NULL,

    basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    allowances DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    deductions DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    net_salary DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    status ENUM(
        'draft',
        'processed',
        'paid'
    ) NOT NULL DEFAULT 'draft',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_payroll (
        staff_id,
        payroll_month
    ),

    KEY idx_payroll_school (school_id),

    CONSTRAINT fk_payroll_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_payroll_staff
        FOREIGN KEY (staff_id)
        REFERENCES staff(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 
============================================================
-- 22. ANNOUNCEMENTS / SCHOOL NEWS
-- ============================================================

CREATE TABLE announcements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    title VARCHAR(200) NOT NULL,

    message TEXT NOT NULL,

    image_path VARCHAR(500) DEFAULT NULL,

    target ENUM(
        'all',
        'parents',
        'teachers',
        'staff'
    ) NOT NULL DEFAULT 'all',

    published_at DATETIME DEFAULT NULL,

    expires_at DATETIME DEFAULT NULL,

    status ENUM(
        'draft',
        'published',
        'archived'
    ) NOT NULL DEFAULT 'draft',

    created_by BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_announcements_school (school_id),

    KEY idx_announcements_published (published_at),

    KEY idx_announcements_target (target),

    CONSTRAINT fk_announcements_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_announcements_creator
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 
============================================================
-- 23. MESSAGES
-- ============================================================

CREATE TABLE messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    sender_id BIGINT UNSIGNED NOT NULL,

    receiver_id BIGINT UNSIGNED NOT NULL,

    subject VARCHAR(200) DEFAULT NULL,

    message TEXT NOT NULL,

    is_read TINYINT(1) NOT NULL DEFAULT 0,

    read_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_messages_school (school_id),

    KEY idx_messages_sender (sender_id),

    KEY idx_messages_receiver (receiver_id),

    CONSTRAINT fk_messages_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_messages_sender
        FOREIGN KEY (sender_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_messages_receiver
        FOREIGN KEY (receiver_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
============================================================
-- 24. SMS LOGS
-- ============================================================

CREATE TABLE sms_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    recipient VARCHAR(30) NOT NULL,

    message TEXT NOT NULL,

    status ENUM(
        'pending',
        'sent',
        'failed'
    ) NOT NULL DEFAULT 'pending',

    provider VARCHAR(100) DEFAULT NULL,

    provider_reference VARCHAR(150) DEFAULT NULL,

    sent_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_sms_school (school_id),

    KEY idx_sms_status (status),

    CONSTRAINT fk_sms_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 
============================================================
-- 25. M-PESA TRANSACTIONS
-- ============================================================

CREATE TABLE mpesa_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED DEFAULT NULL,

    merchant_request_id VARCHAR(100) DEFAULT NULL,

    checkout_request_id VARCHAR(100) DEFAULT NULL,

    mpesa_receipt VARCHAR(100) DEFAULT NULL,

    phone VARCHAR(30) NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    transaction_date DATETIME DEFAULT NULL,

    result_code VARCHAR(20) DEFAULT NULL,

    result_description VARCHAR(255) DEFAULT NULL,

    status ENUM(
        'pending',
        'completed',
        'failed',
        'reversed'
    ) NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_checkout_request (
        checkout_request_id
    ),

    UNIQUE KEY uq_mpesa_receipt (
        mpesa_receipt
    ),

    KEY idx_mpesa_school (school_id),

    KEY idx_mpesa_student (student_id),

    KEY idx_mpesa_phone (phone),

    CONSTRAINT fk_mpesa_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_mpesa_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
============================================================
-- 26. DOCUMENTS / PDF FILES
-- ============================================================
-- Used for:
--   Fee statements
--   Report cards
--   Receipts
--   School documents
--   Future downloadable PDFs

CREATE TABLE documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED DEFAULT NULL,

    user_id BIGINT UNSIGNED DEFAULT NULL,

    document_type ENUM(
        'fee_statement',
        'receipt',
        'report_card',
        'progress_report',
        'attendance_report',
        'school_document',
        'other'
    ) NOT NULL,

    title VARCHAR(255) NOT NULL,

    file_name VARCHAR(255) NOT NULL,

    file_path VARCHAR(500) NOT NULL,

    mime_type VARCHAR(100) NOT NULL DEFAULT 'application/pdf',

    file_size BIGINT UNSIGNED DEFAULT NULL,

    is_public TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_documents_school (school_id),

    KEY idx_documents_student (student_id),

    KEY idx_documents_user (user_id),

    KEY idx_documents_type (document_type),

    CONSTRAINT fk_documents_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_documents_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_documents_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
============================================================
-- 27. SUBSCRIPTIONS
-- ============================================================

CREATE TABLE subscriptions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    plan ENUM(
        'basic',
        'pro',
        'enterprise'
    ) NOT NULL DEFAULT 'basic',

    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    start_date DATE NOT NULL,

    end_date DATE NOT NULL,

    status ENUM(
        'trial',
        'active',
        'expired',
        'cancelled'
    ) NOT NULL DEFAULT 'trial',

    auto_renew TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_subscription_school (school_id),

    KEY idx_subscription_status (status),

    CONSTRAINT fk_subscriptions_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 
============================================================
-- 28. AUDIT LOGS
-- ============================================================

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED DEFAULT NULL,

    user_id BIGINT UNSIGNED DEFAULT NULL,

    action VARCHAR(100) NOT NULL,

    entity_type VARCHAR(100) DEFAULT NULL,

    entity_id BIGINT UNSIGNED DEFAULT NULL,

    description TEXT DEFAULT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,

    user_agent VARCHAR(500) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_audit_school (school_id),

    KEY idx_audit_user (user_id),

    KEY idx_audit_entity (
        entity_type,
        entity_id
    ),

    KEY idx_audit_created (created_at),

    CONSTRAINT fk_audit_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 
============================================================
-- 29. SYSTEM SETTINGS
-- ============================================================

CREATE TABLE school_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    setting_key VARCHAR(100) NOT NULL,

    setting_value TEXT DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_school_setting (
        school_id,
        setting_key
    ),

    CONSTRAINT fk_settings_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
============================================================
-- 30. NOTIFICATION LOGS
-- ============================================================

CREATE TABLE notification_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED DEFAULT NULL,

    user_id BIGINT UNSIGNED DEFAULT NULL,

    title VARCHAR(255) NOT NULL,

    message TEXT NOT NULL,

    channel ENUM(
        'in_app',
        'email',
        'sms',
        'push'
    ) NOT NULL DEFAULT 'in_app',

    status Estatus ENUM(
        'pending',
        'sent',
        'failed',
        'read'
    ) NOT NULL DEFAULT 'pending',

    sent_at DATETIME DEFAULT NULL,

    read_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_notification_school (school_id),

    KEY idx_notification_user (user_id),

    KEY idx_notification_status (status),

    CONSTRAINT fk_notification_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notification_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 
============================================================
-- 31. LOGIN SESSIONS
-- ============================================================
-- Useful for future security and multiple devices.

CREATE TABLE user_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    session_token_hash VARCHAR(255) NOT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,

    user_agent VARCHAR(500) DEFAULT NULL,

    expires_at DATETIME NOT NULL,

    last_activity_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_session_token (
        session_token_hash
    ),

    KEY idx_session_user (user_id),

    KEY idx_session_expiry (expires_at),

    CONSTRAINT fk_sessions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
============================================================
-- 32. PASSWORD RESET TOKENS
-- ============================================================

CREATE TABLE password_reset_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    token_hash VARCHAR(255) NOT NULL,

    expires_at DATETIME NOT NULL,

    used_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_reset_token (
        token_hash
    ),

    KEY idx_reset_user (user_id),

    CONSTRAINT fk_reset_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
============================================================
-- 33. SYSTEM FILE STORAGE
-- ============================================================
-- Allows future storage providers:
-- local / S3 / Cloudinary / other object storage.

CREATE TABLE file_storage (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED DEFAULT NULL,

    uploaded_by BIGINT UNSIGNED DEFAULT NULL,

    storage_provider VARCHAR(50) NOT NULL DEFAULT 'local',

    storage_key VARCHAR(500) NOT NULL,

    original_name VARCHAR(255) NOT NULL,

    mime_type VARCHAR(100) DEFAULT NULL,

    file_size BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_storage_school (school_id),

    KEY idx_storage_user (uploaded_by),

    UNIQUE KEY uq_storage_key (
        storage_provider,
        storage_key
    ),

    CONSTRAINT fk_storage_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_storage_user
        FOREIGN KEY (uploaded_by)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
============================================================
-- END OF THINKPLUS CLOUD DATABASE SCHEMA
-- ============================================================
