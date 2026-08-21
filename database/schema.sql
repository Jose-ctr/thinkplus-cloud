-- ============================================================
-- THINKPLUS CLOUD
-- COMPLETE DATABASE SCHEMA v1.0 MVP
-- ============================================================
-- Author: Joseph Mbui
-- Copyright: © 2026 ThinkPlus Cloud
--
-- Supports:
--   • Super Admin
--   • School Admin
--   • Teachers
--   • Parents
--   • Students
--   • Classes
--   • Subjects
--   • Fees
--   • Payments
--   • Fee Statements
--   • Exams
--   • Academic Progress
--   • Report Cards
--   • Attendance
--   • Staff
--   • Payroll
--   • Announcements / News
--   • Parent ↔ Student relationships
--   • SMS logs
--   • M-Pesa transactions
--   • Audit logs
--   • Subscriptions
--
-- IMPORTANT:
-- This is a FRESH DATABASE schema.
-- ============================================================

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE';
SET time_zone = '+00:00';

START TRANSACTION;


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
    logo VARCHAR(255) DEFAULT NULL,

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

    UNIQUE KEY uq_school_email (email)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 3. CLASSES
-- ============================================================

CREATE TABLE classes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    class_name VARCHAR(100) NOT NULL,

    class_code VARCHAR(50) DEFAULT NULL,

    stream VARCHAR(50) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_class_school_name (
        school_id,
        class_name,
        stream
    ),

    KEY idx_class_school (school_id),

    CONSTRAINT fk_classes_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 4. STUDENTS
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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 5. PARENT PROFILES
-- ============================================================

CREATE TABLE parents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    national_id VARCHAR(50) DEFAULT NULL,

    occupation VARCHAR(150) DEFAULT NULL,

    address VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_parent_user (user_id),

    CONSTRAINT fk_parents_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 6. PARENT ↔ STUDENT
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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 7. SUBJECTS
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

    CONSTRAINT fk_subject_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 8. FEES
-- ============================================================

CREATE TABLE fees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    academic_year YEAR NOT NULL,

    term ENUM(
        'Term 1',
        'Term 2',
        'Term 3'
    ) NOT NULL,

    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    description VARCHAR(255) DEFAULT NULL,

    due_date DATE DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_fees_school (school_id),

    KEY idx_fees_student (student_id),

    CONSTRAINT fk_fees_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_fees_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 9. PAYMENTS
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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 10. EXAMS
-- ============================================================

CREATE TABLE exams (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    exam_name VARCHAR(150) NOT NULL,

    academic_year YEAR NOT NULL,

    term ENUM(
        'Term 1',
        'Term 2',
        'Term 3'
    ) NOT NULL,

    start_date DATE DEFAULT NULL,

    end_date DATE DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_exam_school (school_id),

    CONSTRAINT fk_exams_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 11. RESULTS / ACADEMIC PROGRESS
-- ============================================================

CREATE TABLE results (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    exam_id INT UNSIGNED NOT NULL,

    student_id BIGINT UNSIGNED NOT NULL,

    subject_id INT UNSIGNED NOT NULL,

    score DECIMAL(5,2) NOT NULL,

    grade VARCHAR(10) DEFAULT NULL,

    teacher_comment VARCHAR(255) DEFAULT NULL,

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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 12. ATTENDANCE
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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 13. STAFF
-- ============================================================

CREATE TABLE staff (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED DEFAULT NULL,

    employee_no VARCHAR(50) NOT NULL,

    full_name VARCHAR(150) NOT NULL,

    phone VARCHAR(30) DEFAULT NULL,

    position VARCHAR(100) DEFAULT NULL,

    department VARCHAR(100) DEFAULT NULL,

    salary DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    employment_status ENUM(
        'active',
        'inactive',
        'terminated'
    ) NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_employee (
        school_id,
        employee_no
    ),

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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 14. PAYROLL
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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 15. ANNOUNCEMENTS / SCHOOL NEWS
-- ============================================================

CREATE TABLE announcements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    title VARCHAR(200) NOT NULL,

    message TEXT NOT NULL,

    target ENUM(
        'all',
        'parents',
        'teachers',
        'staff'
    ) NOT NULL DEFAULT 'all',

    published_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_announcements_school (school_id),

    KEY idx_announcements_published (published_at),

    CONSTRAINT fk_announcements_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 16. PARENT / SCHOOL MESSAGES
-- ============================================================

CREATE TABLE messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED NOT NULL,

    sender_id BIGINT UNSIGNED NOT NULL,

    receiver_id BIGINT UNSIGNED NOT NULL,

    subject VARCHAR(200) DEFAULT NULL,

    message TEXT NOT NULL,

    is_read TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 17. SUBSCRIPTIONS
-- ============================================================

CREATE TABLE subscriptions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

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

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_subscription_school (school_id),

    CONSTRAINT fk_subscriptions_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 18. SMS LOGS
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

    provider_reference VARCHAR(100) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_sms_school (school_id),

    CONSTRAINT fk_sms_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


============================================================
-- 19. M-PESA TRANSACTIONS
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
        'failed'
    ) NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_checkout_request (
        checkout_request_id
    ),

    UNIQUE KEY uq_mpesa_receipt (
        mpesa_receipt
    ),

    KEY idx_mpesa_school (school_id),

    KEY idx_mpesa_student (student_id),

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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 20. AUDIT LOGS
-- ============================================================

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    school_id INT UNSIGNED DEFAULT NULL,

    user_id BIGINT UNSIGNED DEFAULT NULL,

    action VARCHAR(100) NOT NULL,

    description TEXT DEFAULT NULL,

    ip_address VARCHAR(45) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_audit_school (school_id),

    KEY idx_audit_user (user_id),

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

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 
============================================================
-- END
--
    ============================================================

COMMIT;
