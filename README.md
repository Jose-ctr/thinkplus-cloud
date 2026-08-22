# ThinkPlus Cloud ☁️

### The School Management SaaS for Kenyan Schools

**Run your entire school in the cloud.**

Fees • Exams • Students • Parents • SMS • M-Pesa • Payroll • Reports

> Multi-tenant school management platform built for Kenyan schools.

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1)
![PDO](https://img.shields.io/badge/Database-PDO-green)
![Status](https://img.shields.io/badge/status-in%20development-orange)
![License](https://img.shields.io/badge/license-MIT-lightgrey)

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Technology Stack](#technology-stack)
3. [Architecture](#architecture)
4. [Core Features](#core-features)
5. [User Roles](#user-roles)
6. [Multi-Tenant Design](#multi-tenant-design)
7. [Database](#database)
8. [Security](#security)
9. [Pricing](#pricing)
10. [Development Roadmap](#development-roadmap)
11. [Project Structure](#project-structure)
12. [Configuration](#configuration)
13. [Development](#development)
14. [Contact](#contact)
15. [License](#license)

---

# Overview

**ThinkPlus Cloud** is a cloud-based School Management SaaS platform designed for Kenyan schools.

The goal is to provide schools with one centralized platform for managing:

- Students
- Parents and guardians
- Teachers and staff
- Classes and streams
- Academics
- Exams and marks
- Report cards
- School fees
- M-Pesa payments
- SMS notifications
- WhatsApp communication
- Payroll
- School analytics
- SaaS subscriptions

ThinkPlus Cloud is designed as a **multi-tenant system**, allowing multiple schools to operate securely on the same platform while keeping their data isolated.

---

# Technology Stack

| Technology | Purpose |
|---|---|
| PHP 8.2+ | Backend application |
| MySQL 8.0+ | Database |
| PDO | Secure database access |
| HTML5 | Web interface |
| CSS3 | Styling |
| Vanilla JavaScript | Frontend interactions |
| M-Pesa Daraja API | Payment integration |
| Africa's Talking | SMS integration |
| GitHub | Source control |

---
## 🏗️ Project Structure

ThinkPlus Cloud uses a modular, multi-tenant SaaS architecture designed for
Kenyan schools.

```text
thinkplus-website/
│
├── app/
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
