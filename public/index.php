<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']) &&
              is_numeric($_SESSION['user_id']) &&
              (int) $_SESSION['user_id'] > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>ThinkPlus Cloud | School Management & Digital Solutions</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="ThinkPlus Cloud provides school management, fee tracking, academic progress, CBC learning and digital creator tools."
    >

    <meta name="theme-color" content="#2563eb">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            background: #f5f7fa;
            color: #1f2937;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        /* =====================================================
           HEADER
        ===================================================== */

        .header {
            background:
                linear-gradient(
                    135deg,
                    #1d4ed8,
                    #2563eb,
                    #0ea5e9
                );

            color: white;
            padding: 60px 20px;
            text-align: center;
        }

        .logo {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 3px;
            opacity: .9;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 42px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 18px;
            opacity: .95;
            max-width: 700px;
            margin: auto;
        }

        .header-buttons {
            margin-top: 25px;
        }

        .header-btn {
            display: inline-block;
            padding: 13px 22px;
            margin: 5px;
            border-radius: 8px;
            font-weight: bold;
            background: white;
            color: #2563eb;
        }

        .header-btn.dark {
            background: #0f172a;
            color: white;
        }

        /* =====================================================
           CONTAINER
        ===================================================== */

        .container {
            max-width: 1150px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .section-title h2 {
            font-size: 30px;
            color: #111827;
        }

        .section-title p {
            color: #6b7280;
            margin-top: 5px;
        }

        /* =====================================================
           MODULES
        ===================================================== */

        .modules {
            display: grid;
            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(280px, 1fr)
                );

            gap: 22px;
        }

        .card {
            background: white;
            padding: 28px;
            border-radius: 15px;

            box-shadow:
                0 5px 20px rgba(0,0,0,.07);

            border: 1px solid #e5e7eb;

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .card:hover {
            transform: translateY(-5px);

            box-shadow:
                0 12px 30px rgba(0,0,0,.12);
        }

        .card h2 {
            color: #2563eb;
            margin: 12px 0;
            font-size: 22px;
        }

        .card p {
            color: #6b7280;
            min-height: 75px;
        }

        .badge {
            display: inline-block;

            background: #10b981;
            color: white;

            padding: 5px 11px;

            border-radius: 20px;

            font-size: 11px;
            font-weight: bold;

            letter-spacing: .5px;
        }

        .badge-new {
            background: #f59e0b;
        }

        .badge-pro {
            background: #7c3aed;
        }

        .btn {
            display: inline-block;

            background: #2563eb;
            color: white;

            padding: 12px 18px;

            border-radius: 8px;

            margin-top: 15px;

            font-weight: bold;

            transition:
                background .2s ease;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn-green {
            background: #059669;
        }

        .btn-green:hover {
            background: #047857;
        }

        .btn-orange {
            background: #f59e0b;
        }

        .btn-orange:hover {
            background: #d97706;
        }

        .btn-purple {
            background: #7c3aed;
        }

        .btn-purple:hover {
            background: #6d28d9;
        }

        /* =====================================================
           SCHOOL PORTAL
        ===================================================== */

        .portal {
            margin-top: 45px;

            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #1e293b
                );

            color: white;

            padding: 40px;

            border-radius: 18px;
        }

        .portal h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .portal > p {
            color: #cbd5e1;
            margin-bottom: 25px;
        }

        .portal-grid {
            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(190px, 1fr)
                );

            gap: 15px;
        }

        .portal-item {
            background: rgba(255,255,255,.08);

            padding: 20px;

            border-radius: 12px;

            border: 1px solid
                rgba(255,255,255,.1);
        }

        .portal-item strong {
            display: block;
            margin-bottom: 5px;
        }

        .portal-item span {
            color: #cbd5e1;
            font-size: 14px;
        }

        /* =====================================================
           FEATURES
        ===================================================== */

        .features {
            margin-top: 45px;

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(220px, 1fr)
                );

            gap: 18px;
        }

        .feature {
            background: white;

            padding: 22px;

            border-radius: 12px;

            border: 1px solid #e5e7eb;
        }

        .feature h3 {
            color: #111827;
            margin-bottom: 5px;
        }

        .feature p {
            color: #6b7280;
            font-size: 14px;
        }

        /* =====================================================
           FOOTER
        ===================================================== */

        footer {
            margin-top: 60px;

            background: #111827;

            color: #9ca3af;

            text-align: center;

            padding: 30px 20px;
        }

        footer strong {
            color: white;
        }

        footer p {
            margin: 5px 0;
        }

        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 600px) {

            .header {
                padding: 45px 18px;
            }

            .header h1 {
                font-size: 30px;
            }

            .header p {
                font-size: 16px;
            }

            .container {
                margin-top: 30px;
            }

            .portal {
                padding: 25px;
            }

            .section-title h2 {
                font-size: 25px;
            }
        }

    </style>
</head>

<body>


<!-- ==========================================================
     HERO
=========================================================== -->

<header class="header">

    <div class="logo">
        JOSEPH MBUI PRESENTS
    </div>

    <h1>
        THINKPLUS CLOUD
    </h1>

    <p>
        Powerful digital solutions for schools,
        students, parents, teachers and creators.
    </p>

    <div class="header-buttons">

        <?php if ($isLoggedIn): ?>

            <a
                href="/public/dashboard.php"
                class="header-btn"
            >
                Dashboard
            </a>

            <a
                href="/public/logout.php"
                class="header-btn dark"
            >
                Logout
            </a>

        <?php else: ?>

            <a
                href="/public/index.php"
                class="header-btn"
            >
                School Login
            </a>

            <a
                href="/public/register.php"
                class="header-btn dark"
            >
                Register School
            </a>

        <?php endif; ?>

    </div>

</header>


<!-- ==========================================================
     MAIN
=========================================================== -->

<main class="container">


    <div class="section-title">

        <h2>
            ThinkPlus Cloud Modules
        </h2>

        <p>
            One platform. Multiple powerful digital solutions.
        </p>

    </div>


    <div class="modules">


        <!-- ==================================================
             THINKPLUS SCHOOL CLOUD
        =================================================== -->

        <div class="card">

            <span class="badge">
                CORE PLATFORM
            </span>

            <h2>
                01. THINKPLUS SCHOOL CLOUD
            </h2>

            <p>
                Complete school management system for
                administrators, teachers, parents and students.
            </p>

            <a
                href="/public/index.php"
                class="btn"
            >
                Open School Portal
            </a>

        </div>


        <!-- ==================================================
             CBC MASTER
        =================================================== -->

        <div class="card">

            <span class="badge">
                POPULAR
            </span>

            <h2>
                02. CBC MASTER
            </h2>

            <p>
                CBC notes, quizzes and examinations
                for learners. Designed for digital learning.
            </p>

            <a
                href="/cbc-master/"
                class="btn-green btn"
            >
                Open CBC MASTER
            </a>

        </div>


        <!-- ==================================================
             FEETRACK
        =================================================== -->

        <div class="card">

            <span class="badge">
                SCHOOL FINANCE
            </span>

            <h2>
                03. FEETRACK
            </h2>

            <p>
                Track school fees, balances, payments,
                M-Pesa transactions and receipts.
            </p>

            <a
                href="/feetrack/"
                class="btn"
            >
                Open FeeTrack
            </a>

        </div>


        <!-- ==================================================
             PWNBUILDER
        =================================================== -->

        <div class="card">

            <span class="badge badge-new">
                NEW
            </span>

            <h2>
                04. PWNBUILDER
            </h2>

            <p>
                Create and schedule social media content.
                Free and Pro plans available.
            </p>

            <a
                href="/pwnbuilder/dashboard.php"
                class="btn-orange btn"
            >
                Launch PWNBUILDER
            </a>

        </div>


    </div>


    <!-- ======================================================
         PARENT PORTAL
    ======================================================= -->

    <section class="portal">

        <h2>
            👨‍👩‍👧 Parent Portal
        </h2>

        <p>
            Parents can securely monitor their children's
            school progress and financial records.
        </p>


        <div class="portal-grid">

            <div class="portal-item">
                <strong>💰 Fees</strong>
                <span>
                    View school fees and charges.
                </span>
            </div>

            <div class="portal-item">
                <strong>📊 Balance</strong>
                <span>
                    View outstanding and paid balances.
                </span>
            </div>

            <div class="portal-item">
                <strong>📄 Fee Statement</strong>
                <span>
                    Generate complete fee statements.
                </span>
            </div>

            <div class="portal-item">
                <strong>📚 Academic Progress</strong>
                <span>
                    View examinations, grades and comments.
                </span>
            </div>

            <div class="portal-item">
                <strong>📰 School News</strong>
                <span>
                    Receive announcements from school.
                </span>
            </div>

            <div class="portal-item">
                <strong>📥 PDF Downloads</strong>
                <span>
                    Download statements and report cards.
                </span>
            </div>

        </div>


        <br>

        <a
            href="/public/index.php"
            class="header-btn"
        >
            Parent Login
        </a>

    </section>


    <!-- ======================================================
         FEATURES
    ======================================================= -->

    <div class="features">


        <div class="feature">

            <h3>
                🔐 Secure Accounts
            </h3>

            <p>
                Role-based access for administrators,
                teachers and parents.
            </p>

        </div>


        <div class="feature">

            <h3>
                💳 M-Pesa
            </h3>

            <p>
                Record and integrate school fee payments
                through M-Pesa.
            </p>

        </div>


        <div class="feature">

            <h3>
                📈 Academic Reports
            </h3>

            <p>
                Examination results, grades,
                comments and progress tracking.
            </p>

        </div>


        <div class="feature">

            <h3>
                📱 Mobile Friendly
            </h3>

            <p>
                Designed to work on phones,
                tablets and desktop computers.
            </p>

        </div>


        <div class="feature">

            <h3>
                📄 PDF Documents
            </h3>

            <p>
                Generate downloadable fee statements,
                receipts and academic reports.
            </p>

        </div>


        <div class="feature">

            <h3>
                📰 School Communication
            </h3>

            <p>
                Announcements, parent communication
                and SMS-ready infrastructure.
            </p>

        </div>


    </div>

</main>


<!-- ==========================================================
     FOOTER
=========================================================== -->

<footer>

    <p>
        <strong>THINKPLUS CLOUD</strong>
    </p>

    <p>
        School Management • Digital Learning •
        Financial Management • Creator Tools
    </p>

    <p>
        © 2026 ThinkPlus Cloud.
        All Rights Reserved.
    </p>

    <p>
        Created by Joseph Mbui.
    </p>

</footer>


</body>
</html>
