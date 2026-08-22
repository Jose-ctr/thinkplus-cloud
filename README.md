# ThinkPlus Cloud ☁️

### The School Management SaaS for Kenyan Schools

**Run your entire school in the cloud.**

Fees • Exams • Students • Parents • SMS • M-Pesa • Payroll • Reports

> Multi-tenant school management platform built specifically for Kenyan schools.

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1)
![PDO](https://img.shields.io/badge/Database-PDO-green)
![Status](https://img.shields.io/badge/status-Phase%202%2F12%20Complete-yellow)
![License](https://img.shields.io/badge/license-MIT-lightgrey)
![Location](https://img.shields.io/badge/Location-Mariakani%2C%20Kenya-brightgreen)

---

## 📋 Table of Contents

1. [Overview](#-overview)
2. [Project Vision](#-project-vision)
3. [Technology Stack](#-technology-stack)
4. [Core Features](#-core-features)
5. [User Roles](#-user-roles)
6. [Multi-Tenant Architecture](#-multi-tenant-architecture)
7. [Database](#-database)
8. [Security](#-security)
9. [Project Structure](#-project-structure)
10. [Configuration](#-configuration)
11. [Development Roadmap](#-development-roadmap)
12. [Next Development Phase](#-next-development-phase)
13. [Local Development](#-local-development)
14. [Production Goals](#-production-goals)
15. [Contact](#-contact)
16. [License](#-license)

---

# 📖 Overview

**ThinkPlus Cloud** is a cloud-based School Management SaaS platform designed for
Kenyan schools.

The platform is being developed to provide schools with one centralized system
for managing their daily academic, administrative, financial and communication
operations.

ThinkPlus Cloud is designed as a **multi-tenant SaaS platform**, allowing
multiple schools to use the same application while keeping each school's data
isolated.

### The platform is designed to manage:

- Students
- Parents and guardians
- Teachers
- School staff
- Classes and streams
- Subjects
- Academic years
- Terms
- Exams
- Marks
- Grading
- Report cards
- Attendance
- School fees
- Fee structures
- Invoices
- Payments
- M-Pesa transactions
- SMS
- WhatsApp communication
- Announcements
- Payroll
- School analytics
- SaaS subscriptions
- School billing
- Audit logs
- Security
- User permissions

---

# 🎯 Project Vision

The goal of ThinkPlus Cloud is to build a reliable and scalable school
management platform that can serve:

- Primary schools
- Secondary schools
- Academies
- Tuition centres
- Private schools
- Multi-branch schools
- School groups and chains

The long-term objective is to provide schools with a complete digital
management platform accessible from:

- Android phones
- iPhones
- Tablets
- Laptops
- Desktop computers

No special school hardware should be required for normal platform usage.

---

# 🛠️ Technology Stack

| Technology | Purpose |
|---|---|
| PHP 8.2+ | Backend application |
| MySQL 8.0+ | Relational database |
| PDO | Secure database access |
| HTML5 | Web interface |
| CSS3 | Responsive styling |
| Vanilla JavaScript | Frontend interactions |
| M-Pesa Daraja API | Payment integration |
| Africa's Talking | SMS integration |
| WhatsApp API | Communication |
| GitHub | Source control |

---

# ⭐ Core Features

## 🏫 School Management

- School registration
- School profiles
- Multiple school branches
- School settings
- Academic calendar
- Classes
- Streams
- Teachers
- Staff
- Students
- Parents and guardians

---

## 📚 Academics

- Academic years
- Terms
- Subjects
- Exams
- Assessments
- Marks
- Grading
- Student results
- Report cards
- Academic performance
- CBC-oriented academic structure

---

## 💰 Finance

- Fee structures
- Student fee accounts
- Invoices
- Payments
- Receipts
- Outstanding balances
- Payment history
- Financial reports
- Fee reminders

---

## 📱 M-Pesa

ThinkPlus Cloud is designed to integrate with the **Safaricom M-Pesa Daraja
API**.

Planned functionality includes:

- STK Push
- Payment callbacks
- Transaction verification
- M-Pesa transaction records
- Payment reconciliation
- Automatic fee account updates
- Payment receipts

Production credentials must never be stored directly inside source code.

---

## 📢 Communication

Planned communication features include:

- Bulk SMS
- Fee reminders
- Exam notifications
- Attendance notifications
- School announcements
- Parent notifications
- WhatsApp communication
- Notification history

---

## 👨‍💼 HR & Payroll

Planned HR functionality includes:

- Staff records
- Employee numbers
- Departments
- Staff positions
- Salary structures
- Payroll processing
- Deductions
- Payslips
- Payroll history

---

## 📊 Analytics

The platform will provide dashboards for:

- Student population
- Fee collection
- Outstanding balances
- Academic performance
- Attendance
- Staff information
- School activity
- Subscription status

---

# 👥 User Roles

ThinkPlus Cloud is designed around role-based access control.

### `super_admin`

Platform-level administrator.

Responsibilities include:

- Managing schools
- Managing subscriptions
- Managing platform settings
- Monitoring system activity
- Platform administration

---

### `school_admin`

School administrator.

Can manage:

- Students
- Parents
- Teachers
- Staff
- Classes
- Academics
- Fees
- Reports
- School settings

---

### `teacher`

Teacher account.

Planned permissions include:

- View assigned classes
- Record attendance
- Enter marks
- View academic information
- Generate academic reports where permitted

---

### `accountant`

Finance-focused account.

Can manage:

- Fee structures
- Invoices
- Payments
- Receipts
- Balances
- Financial reports

---

### `parent`

Parent or guardian account.

Can view information belonging only to their children.

Planned features:

- Child profile
- Attendance
- Results
- Report cards
- Fee balances
- Payment history
- Notifications

---

### `student`

Student account.

Planned access includes:

- Academic results
- Report cards
- Attendance
- Fee information
- School announcements

---

# 🏢 Multi-Tenant Architecture

ThinkPlus Cloud is designed as a **multi-tenant SaaS platform**.

Each school operates as an independent tenant.

The primary tenant identifier is:

```text
school_id
Application queries must always enforce tenant isolation.
Example:
SELECT *
FROM students
WHERE school_id = ?
AND deleted_at IS NULL;
The application must never trust a school_id supplied directly by an untrusted request.
The current authenticated user's school membership must determine the effective tenant.
Tenant isolation goals
School A cannot access School B data
School B cannot access School C data
Users only access authorized branches
User roles are scoped correctly
Financial records remain tenant-specific
Academic records remain tenant-specific
Communication records remain tenant-specific
🗄️ Database
ThinkPlus Cloud currently uses:
MySQL 8.0+
Database:
thinkplus_cloud
Storage engine:
InnoDB
Character set:
utf8mb4
The primary database schema is:
database/schema.sql
Current schema:
Database Schema v3.0
The schema is designed to support:
Multi-school tenancy
School branches
Users
Roles
Permissions
Sessions
Students
Guardians
Staff
Classes
Streams
Academic years
Terms
Subjects
Assessments
Results
Attendance
Fees
Invoices
Payments
M-Pesa
SMS
Notifications
Subscriptions
Billing
Audit/security
Database Design Principles
The database uses:
Primary keys
Foreign keys
Unique constraints
Check constraints
Indexes
Soft deletes
Timestamps
UUID-style public identifiers
Tenant identifiers
Referential integrity
Many records use:
created_at
updated_at
deleted_at
Public-facing identifiers use:
public_id
This helps avoid exposing internal numeric IDs through public APIs.
🔒 Security
Security is a core part of the architecture.
Planned and implemented security mechanisms include:
Password Security
Passwords use PHP's password hashing system:
password_hash()
Passwords are verified using:
password_verify()
PDO Prepared Statements
Database queries should use prepared statements:
$stmt = $pdo->prepare(
    'SELECT * FROM users WHERE id = ?'
);

$stmt->execute([$userId]);
User input must never be concatenated directly into SQL queries.
CSRF Protection
Forms use CSRF tokens.
Example:
csrfField();
Requests are verified using:
verifyCsrfToken();
Session Security
Authentication sessions are designed to support:
Secure session cookies
Session expiration
Session revocation
Session token hashing
Logout
Session regeneration
Tenant Security
Tenant access must be verified before retrieving or modifying school data.
Security components:
security/
├── Tenant.php
├── Csrf.php
├── Audit.php
└── Security.php
Audit Logging
Important security-sensitive actions should be recorded.
Examples:
Login
Logout
Password changes
Permission changes
Student changes
Financial transactions
Administrative actions
🏗️ Project Structure
Current project structure:
thinkplus-website/
│
├── app/
│   │
│   ├── Auth/
│   │   ├── Login.php
│   │   ├── Register.php
│   │   ├── Reset.php
│   │   └── Permissions.php
│   │
│   ├── School/
│   │   ├── Dashboard.php
│   │   ├── Student.php
│   │   ├── Parent.php
│   │   ├── Teacher.php
│   │   └── Class.php
│   │
│   ├── Academics/
│   │   ├── Exam.php
│   │   ├── Marks.php
│   │   └── ReportCard.php
│   │
│   ├── Finance/
│   │   ├── Fee.php
│   │   ├── Invoice.php
│   │   ├── Mpesa.php
│   │   └── Receipt.php
│   │
│   ├── Comms/
│   │   ├── Sms.php
│   │   ├── Whatsapp.php
│   │   └── Announcement.php
│   │
│   ├── HR/
│   │   ├── Staff.php
│   │   └── Payroll.php
│   │
│   ├── SaaS/
│   │   ├── School.php
│   │   ├── Subscription.php
│   │   └── Billing.php
│   │
│   └── helpers/
│       └── functions.php
│
├── config/
│   ├── database.php
│   ├── app.php
│   └── services.php
│
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── migrations/
│
├── public/
│   ├── index.php
│   ├── login.php
│   ├── dashboard.php
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── security/
│   ├── Audit.php
│   ├── Tenant.php
│   ├── Csrf.php
│   └── Security.php
│
├── routes/
│   └── web.php
│
├── storage/
│   ├── logs/
│   └── reports/
│
├── .env.example
├── .gitignore
└── README.md
⚙️ Configuration
ThinkPlus Cloud uses environment-based configuration.
Create a local environment file from:
.env.example
Example:
cp .env.example .env
Configure:
APP_ENV=development
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=thinkplus_cloud
DB_USER=root
DB_PASS=
Important
Never commit:
.env
to GitHub.
Only:
.env.example
should be committed.
Production credentials must remain private.
🧰 Local Development
1. Requirements
Install:
PHP 8.2+
MySQL 8.0+
Git
PHP PDO MySQL extension
Verify PHP:
php -v
Verify MySQL:
mysql --version
2. Create Database
Create the database:
CREATE DATABASE thinkplus_cloud
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
3. Import Schema
Run:
mysql -u root -p thinkplus_cloud < database/schema.sql
4. Configure Environment
Copy:
cp .env.example .env
Then configure the database credentials.
5. Run Development Server
From the project root:
php -S localhost:8000 -t public
Open:
http://localhost:8000
🚧 Development Roadmap
ThinkPlus Cloud is being developed in 12 major phases.
Phase 1 — Architecture & Foundation
Status:
✅ COMPLETE
Includes:
Repository structure
Application architecture
Initial configuration
Project organization
SaaS planning
Phase 2 — Database v3.0
Status:
✅ COMPLETE
Includes:
Multi-tenant database
Schools
Branches
Users
Roles
Permissions
Students
Guardians
Staff
Classes
Streams
Academic structure
Finance foundation
Communication foundation
SaaS foundation
Audit/security foundation
Primary file:
database/schema.sql
🚀 Phase 3 — Authentication & Security
Status:
🔜 NEXT
This is the next major development stage.
Phase 3 objectives
Build a complete authentication and authorization system.
Authentication
Login
Logout
Registration
Password hashing
Password verification
Password reset
Session management
Session expiration
Session revocation
Login protection
Authorization
Implement:
super_admin
school_admin
teacher
accountant
parent
student
with role and permission checks.
Tenant Security
Implement:
security/Tenant.php
to determine the authenticated user's active school.
All school-level operations must enforce tenant isolation.
CSRF
Implement:
security/Csrf.php
for protected POST requests.
Security helpers
Implement:
security/Security.php
for centralized security controls.
Audit
Implement:
security/Audit.php
for recording important system actions.
🏫 Phase 4 — School Management
Status:
⏳ PLANNED
Features:
School dashboard
School profile
Branch management
Student management
Parent management
Teacher management
Staff management
Class management
Stream management
Academic year management
Term management
💰 Phase 5 — Finance
Status:
⏳ PLANNED
Features:
Fee structures
Student fee accounts
Invoices
Payments
Receipts
Fee balances
Financial reports
Payment history
Fee reminders
📱 Phase 6 — M-Pesa Integration
Status:
⏳ PLANNED
Integration with:
M-Pesa Daraja API
Planned functionality:
STK Push
Callback endpoint
Transaction validation
Transaction storage
Payment reconciliation
Automatic fee updates
Receipt generation
Production API credentials will be stored securely through environment configuration.
📚 Phase 7 — Academics
Status:
⏳ PLANNED
Features:
Subjects
Exams
Assessments
Marks entry
Grading
Results
Report cards
Academic performance
CBC-oriented reporting
📢 Phase 8 — Attendance & Communication
Status:
⏳ PLANNED
Features:
Student attendance
Staff attendance
Attendance reports
SMS
WhatsApp
Announcements
Parent notifications
Fee reminders
Academic notifications
👨‍💼 Phase 9 — HR & Payroll
Status:
⏳ PLANNED
Features:
Staff records
Employee management
Salary structures
Payroll
Deductions
Payslips
Payroll reports
👨‍👩‍👧 Phase 10 — Parent Portal
Status:
⏳ PLANNED
Parents will be able to:
View children
View attendance
View results
View report cards
View fee balances
View payment history
Receive announcements
Receive notifications
Make supported payments
☁️ Phase 11 — SaaS Administration
Status:
⏳ PLANNED
Platform administration features:
School onboarding
School management
Subscription plans
School subscriptions
Billing
Trial periods
Subscription status
Platform analytics
Platform audit logs
🚀 Phase 12 — Production Launch
Status:
⏳ PLANNED
Production launch will include:
Production hosting
HTTPS
Secure environment configuration
Database backups
Monitoring
Error logging
Performance optimization
Security testing
API hardening
Production M-Pesa integration
Production SMS integration
User acceptance testing
School pilot testing
Documentation
Deployment procedures
🔥 Immediate Next Development
After the database v3.0 phase, development should proceed in this order:
1. config/database.php
2. config/app.php
3. config/services.php
4. app/helpers/functions.php
5. security/Security.php
6. security/Csrf.php
7. security/Tenant.php
8. security/Audit.php
9. app/Auth/Login.php
10. app/Auth/Register.php
11. app/Auth/Reset.php
12. app/Auth/Permissions.php
13. public/login.php
14. public/dashboard.php
15. routes/web.php
The first priority is to establish a working database connection and secure authentication foundation before building the school modules.
🧪 Development Principles
ThinkPlus Cloud development follows these principles:
Security First
Never trust user input.
Use:
PDO prepared statements
CSRF protection
Password hashing
Session security
Tenant validation
Authorization checks
Audit logging
Tenant Isolation
Every school-level operation must verify the authenticated user's school.
Database Integrity
Use:
Foreign keys
Unique constraints
Check constraints
Transactions
Proper indexes
Maintainability
Business logic should be organized into appropriate modules instead of placing the entire application inside a single PHP file.
Production Readiness
Development code should be written with eventual production deployment in mind.
📈 Production Goals
ThinkPlus Cloud is intended to become a scalable SaaS platform capable of supporting:
Multiple schools
Multiple branches
Thousands of students
Multiple parents per student
Multiple children per parent
Teachers and school staff
Financial transactions
M-Pesa payments
SMS communication
Academic records
Attendance
Payroll
Subscription billing
Mobile and web APIs
📞 Contact
Joseph Mbui
Founder — ThinkPlus Cloud
📍 Mariakani, Kilifi County, Kenya
📧 Email:
mbuijoseph51@gmail.com
GitHub:
Jose-ctr/thinkplus-website
Website:
https://jose-ctr.github.io/thinkplus-website/
📄 License
Copyright © 2026 ThinkPlus Cloud
Released under the MIT License.
🇰🇪 Built in Kenya
ThinkPlus Cloud is being developed to help Kenyan schools move from manual school administration to a secure, centralized digital platform.
Building in public.
Current Status
Phase 1  ✅ Architecture & Foundation
Phase 2  ✅ Database v3.0
Phase 3  🔜 Authentication & Security
Phase 4  ⏳ School Management
Phase 5  ⏳ Finance
Phase 6  ⏳ M-Pesa
Phase 7  ⏳ Academics
Phase 8  ⏳ Attendance & Communication
Phase 9  ⏳ HR & Payroll
Phase 10 ⏳ Parent Portal
Phase 11 ⏳ SaaS Administration
Phase 12 ⏳ Production Launch
ThinkPlus Cloud — One platform. One school system. Anywhere. ☁️🇰🇪

**One important correction:** I deliberately made the README's structure match the repository you showed, rather than claiming folders/files that aren't currently there. Also, I would **not** put `config/database.php` into the README as “complete” until we actually create that file. Your next development step should be **Phase 3: the database connection/configuration foundation**, then authentication and tenant security.
