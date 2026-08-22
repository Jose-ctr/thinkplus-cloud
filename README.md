# ThinkPlus Cloud ☁️

### The School Management SaaS for Kenyan Schools

**Run your entire school in the cloud.**

Fees • Exams • Students • Parents • SMS • M-Pesa • Payroll • Reports

> Multi-tenant school management platform built for Kenyan schools.

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1)
![PDO](https://img.shields.io/badge/Database-PDO-green)
![Status](https://img.shields.io/badge/status-Phase%202%2F12%20Complete-yellow)
![Location](https://img.shields.io/badge/Location-Mariakani%2C%20Kenya-brightgreen)

---

## 📋 Table of Contents

1. [Overview](#-overview)
2. [Technology Stack](#-technology-stack)
3. [Core Features](#-core-features)
4. [User Roles](#-user-roles)
5. [Multi-Tenant Design](#-multi-tenant-design)
6. [Database](#-database-v30)
7. [Security](#-security)
8. [Project Structure](#-project-structure)
9. [Configuration](#-configuration)
10. [Development Roadmap](#-development-roadmap)
11. [Development Status](#-development-status)
12. [Contact](#-contact)
13. [License](#-license)

---

# 📖 Overview

**ThinkPlus Cloud** is a cloud-based School Management SaaS platform designed for Kenyan schools.

The platform is being developed to provide one centralized system for managing:

- Students
- Parents and guardians
- Teachers and staff
- Classes and streams
- Academic years and terms
- CBC and other academic structures
- Exams and marks
- Report cards
- Attendance
- School fees
- Invoices and payments
- M-Pesa payments
- SMS notifications
- WhatsApp communication
- Payroll
- School analytics
- SaaS subscriptions

ThinkPlus Cloud is designed as a **multi-tenant SaaS platform**, allowing multiple schools to operate on the same application while keeping school data logically isolated.

**Founder:** Joseph Mbui  
**Location:** Mariakani, Kilifi County, Kenya  
**Email:** mbuijoseph51@gmail.com

---

# 🛠️ Technology Stack

| Technology | Purpose |
|---|---|
| PHP 8.2+ | Backend application |
| MySQL 8.0+ | Relational database |
| PDO | Database access |
| HTML5 | Web interface |
| CSS3 | Styling |
| Vanilla JavaScript | Frontend interactions |
| M-Pesa Daraja API | Payment integration |
| Africa's Talking | SMS integration |
| GitHub | Source control |

---

# ⭐ Core Features

## 🏫 School Management

- School profiles
- School branches
- Students
- Parents and guardians
- Teachers
- Staff
- Classes
- Streams
- Academic years
- Terms

## 📚 Academics

- Subjects
- Exams
- Assessments
- Marks
- Grading
- Academic results
- Report cards

## 💰 Finance

- Fee structures
- Student invoices
- Payments
- Receipts
- Outstanding balances
- Financial records

## 📱 M-Pesa

Planned integration with the **M-Pesa Daraja API**, including:

- STK Push
- Payment callbacks
- Transaction records
- Payment reconciliation

## 📢 Communication

- SMS notifications
- Bulk SMS
- WhatsApp communication
- School announcements
- Parent notifications

## 👥 HR & Payroll

- Staff records
- Payroll
- Salary records
- Deductions
- Payslips

## ☁️ SaaS

- Multiple schools
- School subscriptions
- Subscription plans
- Billing
- Trial periods
- Platform administration

---

# 👥 User Roles

The platform is designed to support role-based access control.

Planned roles include:

- `super_admin` — Platform administrator
- `school_admin` — School administrator
- `teacher` — Academic and attendance management
- `accountant` — Fees and financial management
- `parent` — Access to their children's information
- `student` — Access to permitted academic information

Permissions will be controlled through the application's RBAC system.

---

# 🏢 Multi-Tenant Design

ThinkPlus Cloud is designed as a **multi-school / multi-tenant SaaS platform**.

Each school is represented as a tenant.

The database uses `school_id` and related tenant relationships to associate school-owned records with the correct school.

Example:

```sql
SELECT *
FROM students
WHERE school_id = ?
AND deleted_at IS NULL;

🏗️ Project Structure
thinkplus-website/
│
├── app/
│   ├── Auth/
│   ├── School/
│   ├── Academics/
│   ├── Finance/
│   ├── Comms/
│   ├── HR/
│   ├── SaaS/
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
