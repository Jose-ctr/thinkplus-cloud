# ThinkPlus Cloud ☁️

### The School Management SaaS for Kenyan Schools

**Run your entire school in the cloud.**

**Fees • Exams • Students • Parents • SMS • M-Pesa • Payroll • Reports**

> Multi-tenant school management platform built specifically for Kenyan schools.

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1)
![PDO](https://img.shields.io/badge/Database-PDO-green)
![Status](https://img.shields.io/badge/status-Phase%202%2F12%20Complete-yellow)
![License](https://img.shields.io/badge/license-MIT-lightgrey)
![Location](https://img.shields.io/badge/Location-Kilifi%2C%20Kenya-brightgreen)

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
14. [Development Principles](#-development-principles)
15. [Production Goals](#-production-goals)
16. [Contact](#-contact)
17. [License](#-license)

---

# 📖 Overview

**ThinkPlus Cloud** is a cloud-based School Management SaaS platform designed
for Kenyan schools.

The platform is being developed to provide schools with one centralized system
for managing their daily academic, administrative, financial, and communication
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
- Assessments
- Marks
- Grading
- Report cards
- Attendance
- School fees
- Fee structures
- Invoices
- Payments
- M-Pesa transactions
- SMS communication
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

> Production credentials must never be stored directly inside source code.

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

- ---

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
Tenant Isolation Goals
School A cannot access School B data
School B cannot access School C data
Users can only access authorized branches
User roles are scoped correctly
Financial records remain tenant-specific
Academic records remain tenant-specific
Communication records remain tenant-specific
🗄️ Database
ThinkPlus Cloud currently uses:
MySQL 8.0+
Database: thinkplus_cloud
Storage engine: InnoDB
Character set: utf8mb4
The primary database schema is:
database/schema.sql
Current schema version:
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
Audit and security
Database Design Principles
The database uses:
Primary keys
Foreign keys
Unique constraints
Check constraints
Indexes
Soft deletes
Timestamps
Public identifiers
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
Security is a core part of the ThinkPlus Cloud architecture.
The system is being designed around the following security principles.
Password Security
Passwords must use PHP's password hashing system:
password_hash()
Passwords must be verified using:
password_verify()
PDO Prepared Statements
Database queries must use prepared statements.
Example:
$stmt = $pdo->prepare(
    'SELECT * FROM users WHERE id = ?'
);

$stmt->execute([$userId]);
User input must never be concatenated directly into SQL queries.
CSRF Protection
Protected forms must use CSRF tokens.
Example:
csrfField();
Requests must be verified using:
verifyCsrfToken();
CSRF protection will be centralized in:
security/Csrf.php
Session Security
Authentication sessions are designed to support:
Secure session cookies
Session expiration
Session revocation
Session token hashing
Logout
Session regeneration
Login protection
Tenant Security
Tenant access must be verified before retrieving or modifying school data.
Security components include:
security/
├── Tenant.php
├── Csrf.php
├── Audit.php
└── Security.php
Authorization
Every protected operation must verify:
The user is authenticated.
The user has the required role or permission.
The user belongs to the correct school or authorized branch.
The requested resource belongs to the user's effective tenant.
Audit Logging
Important security-sensitive actions should be recorded.
Examples include:
Login
Logout
Password changes
Permission changes
Student changes
Financial transactions
Administrative actions
School configuration changes
Audit functionality will be centralized in:
security/Audit.php
🏗️ Project Structure
The target application architecture is:
thinkplus-cloud/
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
Legacy Files
Older root-level PHP files may still exist in the repository from earlier development experiments.
Examples include:
/login.php
/register.php
/logout.php
/process_register.php
These files are considered legacy and are not the target architecture.
The new application should use the structured:
app/
config/
public/
security/
routes/
architecture.
Legacy files should only be removed after the replacement authentication system has been implemented and tested.
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
Production credentials must remain private and must never be hard-coded into application source code.

---

# 🧰 Local Development

## 1. Requirements

Install:

- PHP 8.2+
- MySQL 8.0+
- Git
- PHP PDO MySQL extension

Verify PHP:

```bash
php -v
Verify MySQL:
mysql --version
2. Create Database
Create the ThinkPlus Cloud database:
CREATE DATABASE thinkplus_cloud
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
3. Import Database Schema
Import the primary database schema:
mysql -u root -p thinkplus_cloud < database/schema.sql
If a seed file is available, it can be imported after the main schema:
mysql -u root -p thinkplus_cloud < database/seed.sql
4. Configure Environment
Copy the example environment file:
cp .env.example .env
Then configure the database and application settings inside .env.
Never commit the .env file.
5. Run the Development Server
From the project root:
php -S localhost:8000 -t public
Then open:
http://localhost:8000
🚧 Development Roadmap
ThinkPlus Cloud is being developed in 12 major phases.
Phase 1 — Architecture & Foundation
Status: ✅ COMPLETE
Includes:
Repository structure
Application architecture
Initial configuration
Project organization
SaaS planning
Phase 2 — Database v3.0
Status: ✅ COMPLETE
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
Audit and security foundation
Primary file:
database/schema.sql
🚀 Phase 3 — Authentication & Security
Status: 🔜 NEXT
This is the next major development stage.
The objective is to establish a secure and reusable authentication and authorization foundation before building the main school management modules.
Authentication
Implement:
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
Session regeneration
Authorization
Implement the following roles:
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
CSRF Protection
Implement:
security/Csrf.php
for protected POST and state-changing requests.
Security Helpers
Implement:
security/Security.php
for centralized security controls.
Audit Logging
Implement:
security/Audit.php
for recording important system actions.
🏫 Phase 4 — School Management
Status: ⏳ PLANNED
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
Status: ⏳ PLANNED
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
Status: ⏳ PLANNED
Integration with:
Safaricom M-Pesa Daraja API
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
Status: ⏳ PLANNED
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
Status: ⏳ PLANNED
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
Status: ⏳ PLANNED
Features:
Staff records
Employee management
Salary structures
Payroll
Deductions
Payslips
Payroll reports
👨‍👩‍👧 Phase 10 — Parent Portal
Status: ⏳ PLANNED
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
Status: ⏳ PLANNED
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
Status: ⏳ PLANNED
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
After completing the database v3.0 foundation, development should proceed in this order:
config/database.php
config/app.php
config/services.php
app/helpers/functions.php
security/Security.php
security/Csrf.php
security/Tenant.php
security/Audit.php
app/Auth/Login.php
app/Auth/Register.php
app/Auth/Reset.php
app/Auth/Permissions.php
public/login.php
public/dashboard.php
routes/web.php
The first priority is to establish a working database connection and secure authentication foundation before building the school management modules.
---

# 🧪 Development Principles

ThinkPlus Cloud development follows these principles:

## Security First

Never trust user input.

Use:

- PDO prepared statements
- CSRF protection
- Password hashing
- Session security
- Tenant validation
- Authorization checks
- Audit logging

## Tenant Isolation

Every school-level operation must verify the authenticated user's school.

## Database Integrity

Use:

- Foreign keys
- Unique constraints
- Check constraints
- Transactions
- Proper indexes

## Maintainability

Business logic should be organized into appropriate modules instead of placing
the entire application inside a single PHP file.

## Production Readiness

Development code should be written with eventual production deployment in mind.

---

# 📈 Production Goals

ThinkPlus Cloud is intended to become a scalable SaaS platform capable of
supporting:

- Multiple schools
- Multiple branches
- Thousands of students
- Multiple parents per student
- Multiple children per parent
- Teachers and school staff
- Financial transactions
- M-Pesa payments
- SMS communication
- Academic records
- Attendance
- Payroll
- Subscription billing
- Mobile and web APIs

---

# 📞 Contact

**Joseph Mbui**

Founder — ThinkPlus Cloud

📍 Mariakani, Kilifi County, Kenya

📧 Email:

**mbuijoseph51@gmail.com**

GitHub:

**Jose-ctr/thinkplus-cloud**

Website:

**https://jose-ctr.github.io/thinkplus-cloud/**

---

# 📄 License

Copyright © 2026 ThinkPlus Cloud

Released under the MIT License.

---

# 🇰🇪 Built in Kenya

ThinkPlus Cloud is being developed to help Kenyan schools move from manual
school administration to a secure, centralized digital platform.

**Building in public.**

---

# 📊 Current Status

| Phase | Status |
|---|---|
| Phase 1 — Architecture & Foundation | ✅ Complete |
| Phase 2 — Database v3.0 | ✅ Complete |
| Phase 3 — Authentication & Security | 🔜 Next |
| Phase 4 — School Management | ⏳ Planned |
| Phase 5 — Finance | ⏳ Planned |
| Phase 6 — M-Pesa | ⏳ Planned |
| Phase 7 — Academics | ⏳ Planned |
| Phase 8 — Attendance & Communication | ⏳ Planned |
| Phase 9 — HR & Payroll | ⏳ Planned |
| Phase 10 — Parent Portal | ⏳ Planned |
| Phase 11 — SaaS Administration | ⏳ Planned |
| Phase 12 — Production Launch | ⏳ Planned |

---

## ☁️ ThinkPlus Cloud

**One platform. One school system. Anywhere.**

🇰🇪 **Built for Kenyan schools.**
