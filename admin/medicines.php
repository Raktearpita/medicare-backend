<?php

declare(strict_types=1);

session_start();

if (
    !isset($_SESSION['admin_id']) ||
    ($_SESSION['admin_logged_in'] ?? false) !== true
) {
    header('Location: ../login.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@medicare.com';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Medicines | MediCare Admin</title>

    <meta
        name="description"
        content="Manage medicines, inventory, pricing and stock from the MediCare admin panel.">

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com">
    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        :root {
            --primary: #126b5b;
            --primary-dark: #0b5145;
            --primary-light: #e8f6f2;

            --secondary: #f4a261;

            --dark: #12211e;
            --text: #344440;
            --muted: #72807c;

            --white: #ffffff;
            --background: #f5faf8;
            --border: #dfeae6;

            --success: #16805f;
            --success-bg: #eaf8f2;

            --warning: #b87900;
            --warning-bg: #fff6df;

            --danger: #d95353;
            --danger-bg: #fff0f0;

            --shadow: 0 15px 45px rgba(18, 107, 91, .08);

            --sidebar-width: 270px;
            --radius: 18px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "DM Sans", sans-serif;
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
        }

        button,
        input,
        select {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* =========================
           APP LAYOUT
        ========================= */

        .app {
            min-height: 100vh;
            display: flex;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #102f29;
            color: #fff;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
        }

        .sidebar-header {
            padding: 28px 22px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            background: #fff;
            color: var(--primary);
            display: grid;
            place-items: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .brand-text strong {
            display: block;
            font-family: "Manrope", sans-serif;
            font-size: 19px;
            font-weight: 800;
        }

        .brand-text span {
            color: rgba(255, 255, 255, .58);
            font-size: 11px;
            letter-spacing: .7px;
        }

        .sidebar-nav {
            padding: 24px 14px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-label {
            color: rgba(255, 255, 255, .38);
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.4px;
            padding: 0 12px 10px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 12px 13px;
            border-radius: 12px;
            color: rgba(255, 255, 255, .66);
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 600;
            transition: .2s ease;
        }

        .nav-item i {
            width: 19px;
            text-align: center;
            font-size: 15px;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, .07);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, .12);
            color: #fff;
            box-shadow: inset 3px 0 0 #74d3bd;
        }

        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        .admin-mini {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px;
            border-radius: 13px;
            background: rgba(255, 255, 255, .06);
        }

        .admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary);
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .admin-mini-info {
            min-width: 0;
        }

        .admin-mini-info strong {
            display: block;
            color: #fff;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-mini-info span {
            display: block;
            color: rgba(255, 255, 255, .45);
            font-size: 10px;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-width: 0;
        }

        .topbar {
            height: 76px;
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 clamp(18px, 3vw, 42px);
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .mobile-menu {
            display: none;
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 11px;
            color: var(--dark);
        }

        .page-heading h1 {
            font-family: "Manrope", sans-serif;
            font-size: clamp(19px, 2vw, 25px);
            color: var(--dark);
            font-weight: 800;
        }

        .page-heading p {
            margin-top: 2px;
            color: var(--muted);
            font-size: 12px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .view-store {
            height: 40px;
            padding: 0 15px;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: #fff;
            color: var(--text);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            transition: .2s ease;
        }

        .view-store:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .top-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 800;
        }

        /* =========================
           CONTENT
        ========================= */

        .content {
            padding: clamp(20px, 3vw, 42px);
            max-width: 1900px;
            margin: 0 auto;
        }

        .content-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .content-head h2 {
            font-family: "Manrope", sans-serif;
            font-size: clamp(21px, 2.3vw, 30px);
            color: var(--dark);
            font-weight: 800;
        }

        .content-head p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 6px;
        }

        .primary-btn {
            min-height: 44px;
            padding: 0 17px;
            border: 0;
            border-radius: 12px;
            background: var(--primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 9px 20px rgba(18, 107, 91, .18);
            transition: .2s ease;
        }

        .primary-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* =========================
           SUMMARY CARDS
        ========================= */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 19px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .summary-icon {
            width: 45px;
            height: 45px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            font-size: 17px;
        }

        .summary-icon.green {
            color: var(--primary);
            background: var(--primary-light);
        }

        .summary-icon.orange {
            color: var(--warning);
            background: var(--warning-bg);
        }

        .summary-icon.red {
            color: var(--danger);
            background: var(--danger-bg);
        }

        .summary-icon.blue {
            color: #4169a1;
            background: #edf3fc;
        }

        .summary-info span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
        }

        .summary-info strong {
            display: block;
            color: var(--dark);
            font-family: "Manrope", sans-serif;
            font-size: 21px;
            font-weight: 800;
            margin-top: 3px;
        }

        /* =========================
           TOOLBAR
        ========================= */

        .panel {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .toolbar {
            padding: 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 11px;
            flex-wrap: wrap;
        }

        .search-box {
            height: 44px;
            min-width: min(360px, 100%);
            flex: 1 1 280px;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ba9a5;
            font-size: 13px;
        }

        .search-box input {
            width: 100%;
            height: 100%;
            border: 1px solid var(--border);
            border-radius: 11px;
            outline: none;
            padding: 0 13px 0 39px;
            color: var(--dark);
            background: #fbfdfc;
            transition: .2s ease;
            font-size: 13px;
        }

        .search-box input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(18, 107, 91, .08);
        }

        .filter-select {
            height: 44px;
            min-width: 155px;
            border: 1px solid var(--border);
            border-radius: 11px;
            outline: none;
            padding: 0 12px;
            background: #fbfdfc;
            color: var(--text);
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .filter-select:focus {
            border-color: var(--primary);
        }

        .refresh-btn {
            width: 44px;
            height: 44px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            border-radius: 11px;
            transition: .2s ease;
        }

        .refresh-btn:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        .refresh-btn.loading i {
            animation: spin .7s linear infinite;
        }



        /* =========================
           TABLE
        ========================= */

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .medicine-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .medicine-table th {
            background: #fbfdfc;
            color: #82908c;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
            text-align: left;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .medicine-table td {
            padding: 14px 18px;
            border-bottom: 1px solid #edf3f0;
            vertical-align: middle;
            font-size: 12px;
        }

        .medicine-table tbody tr {
            transition: background .15s ease;
        }

        .medicine-table tbody tr:hover {
            background: #fbfdfc;
        }

        .medicine-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .medicine-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 230px;
        }

        .medicine-image {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--primary-light);
            overflow: hidden;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            border: 1px solid #dceee9;
        }

        .medicine-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .medicine-image i {
            color: var(--primary);
            font-size: 18px;
        }

        .medicine-name {
            color: var(--dark);
            font-weight: 800;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .medicine-id {
            color: #98a49f;
            font-size: 10px;
        }

        .category-name {
            color: var(--text);
            font-weight: 600;
        }

        .price {
            color: var(--dark);
            font-weight: 800;
            white-space: nowrap;
        }

        .stock-number {
            font-weight: 800;
        }

        .stock-good {
            color: var(--success);
        }

        .stock-low {
            color: var(--warning);
        }

        .stock-out {
            color: var(--danger);
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 50px;
            padding: 6px 9px;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .status.in-stock {
            color: var(--success);
            background: var(--success-bg);
        }

        .status.low-stock {
            color: var(--warning);
            background: var(--warning-bg);
        }

        .status.out-stock {
            color: var(--danger);
            background: var(--danger-bg);
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #fff;
            display: grid;
            place-items: center;
            color: var(--muted);
            transition: .2s ease;
        }

        .action-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .action-btn.delete:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: var(--danger-bg);
        }

        /* =========================
           EMPTY / LOADING
        ========================= */

        .state-box {
            padding: 65px 20px;
            text-align: center;
            display: none;
        }

        .state-box.show {
            display: block;
        }

        .state-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            border-radius: 17px;
            background: var(--primary-light);
            color: var(--primary);
            display: grid;
            place-items: center;
            font-size: 22px;
        }

        .state-box h3 {
            color: var(--dark);
            font-family: "Manrope", sans-serif;
            font-size: 18px;
            margin-bottom: 6px;
        }

        .state-box p {
            color: var(--muted);
            font-size: 12px;
        }



        /* =========================
           PAGINATION
        ========================= */

        .table-footer {
            border-top: 1px solid var(--border);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .results-info {
            color: var(--muted);
            font-size: 11px;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .page-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #fff;
            color: var(--text);
            font-size: 11px;
            font-weight: 700;
        }

        .page-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .page-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .page-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        /* =========================
           TOAST
        ========================= */

        .toast {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 2000;
            min-width: 270px;
            max-width: 420px;
            background: #12211e;
            color: #fff;
            border-radius: 13px;
            padding: 13px 15px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .18);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            transform: translateY(25px);
            opacity: 0;
            pointer-events: none;
            transition: .25s ease;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast i {
            color: #74d3bd;
        }

        /* =========================
           SIDEBAR OVERLAY
        ========================= */

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(8, 25, 21, .45);
            z-index: 950;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .overlay.show {
                display: block;
            }

            .main {
                width: 100%;
                margin-left: 0;
            }

            .mobile-menu {
                display: block;
            }

            .view-store {
                display: none;
            }
        }

        @media (max-width: 650px) {
            .content {
                padding: 18px 13px 30px;
            }

            .topbar {
                padding: 0 13px;
            }

            .content-head {
                align-items: stretch;
                flex-direction: column;
                margin-bottom: 18px;
            }

            .primary-btn {
                width: 100%;
            }

            .summary-grid {
                grid-template-columns: 1fr 1fr;
                gap: 9px;
            }

            .summary-card {
                padding: 13px;
                gap: 10px;
            }

            .summary-icon {
                width: 39px;
                height: 39px;
                font-size: 14px;
            }

            .summary-info strong {
                font-size: 17px;
            }

            .toolbar {
                padding: 12px;
            }

            .search-box {
                flex-basis: 100%;
            }

            .filter-select {
                flex: 1;
                min-width: 0;
            }

            .refresh-btn {
                flex-shrink: 0;
            }

            .table-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .pagination {
                justify-content: center;
            }

            .admin-mini {
                padding: 8px;
            }
        }

        @media (max-width: 380px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .page-heading p {
                display: none;
            }

            .top-avatar {
                display: none;
            }

            .filter-select {
                flex-basis: calc(50% - 5px);
            }
        }

        @media (min-width: 1800px) {
            :root {
                --sidebar-width: 300px;
            }

            .sidebar-header {
                padding-left: 28px;
                padding-right: 28px;
            }

            .content {
                padding-left: 55px;
                padding-right: 55px;
            }

            .medicine-table th,
            .medicine-table td {
                padding-left: 24px;
                padding-right: 24px;
            }
        }

        @media (min-width: 2400px) {
            :root {
                --sidebar-width: 340px;
            }

            body {
                font-size: 15px;
            }

            .content {
                max-width: 2300px;
                padding-left: 75px;
                padding-right: 75px;
            }
        }
    </style>
</head>

<body>

    <div class="app">

        <!-- =========================
         SIDEBAR
    ========================== -->
        <aside class="sidebar" id="sidebar">

            <div class="sidebar-header">
                <a href="dashboard.php" class="brand">
                    <div class="brand-icon">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>

                    <div class="brand-text">
                        <strong>MediCare</strong>
                        <span>ADMIN PANEL</span>
                    </div>
                </a>
            </div>

            <nav class="sidebar-nav">

                <div class="nav-label">Overview</div>

                <a href="dashboard.php" class="nav-item">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>

                <div class="nav-label" style="margin-top:22px;">Management</div>

                <a href="medicines.php" class="nav-item active">
                    <i class="fa-solid fa-pills"></i>
                    <span>Medicines</span>
                </a>

                <a href="orders.php" class="nav-item">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Orders</span>
                </a>

                <a href="categories.php" class="nav-item">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Categories</span>
                </a>

                <div class="nav-label" style="margin-top:22px;">Quick Access</div>

                <a href="add-medicine.php" class="nav-item">
                    <i class="fa-solid fa-circle-plus"></i>
                    <span>Add Medicine</span>
                </a>

            </nav>

            <div class="sidebar-footer">
                <div class="admin-mini">

                    <div class="admin-avatar" id="sidebarAvatar">
                        A
                    </div>

                    <div class="admin-mini-info">
                        <strong id="sidebarName">
                            <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?>
                        </strong>

                        <span id="sidebarEmail">
                            <?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                </div>
            </div>

        </aside>

        <div class="overlay" id="overlay"></div>

        <!-- =========================
         MAIN
    ========================== -->
        <main class="main">

            <!-- TOPBAR -->
            <header class="topbar">

                <div class="topbar-left">

                    <button
                        class="mobile-menu"
                        id="mobileMenu"
                        aria-label="Open menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <div class="page-heading">
                        <h1>Medicines</h1>
                        <p>Manage your medicine inventory</p>
                    </div>

                </div>

                <div class="topbar-right">

                    <a
                        href="../index.php"
                        target="_blank"
                        rel="noopener"
                        class="view-store">
                        <i class="fa-solid fa-store"></i>
                        View Store
                    </a>

                    <div class="top-avatar" id="topAvatar">
                        A
                    </div>

                </div>

            </header>

            <!-- CONTENT -->
            <section class="content">

                <div class="content-head">

                    <div>
                        <h2>Medicine Inventory</h2>
                        <p>
                            View, search and manage all medicines available in your store.
                        </p>
                    </div>

                    <a href="add-medicine.php" class="primary-btn">
                        <i class="fa-solid fa-plus"></i>
                        Add Medicine
                    </a>

                </div>

                <!-- SUMMARY -->
                <div class="summary-grid">

                    <div class="summary-card">

                        <div class="summary-icon green">
                            <i class="fa-solid fa-pills"></i>
                        </div>

                        <div class="summary-info">
                            <span>Total Medicines</span>
                            <strong id="totalMedicines">—</strong>
                        </div>

                    </div>

                    <div class="summary-card">

                        <div class="summary-icon blue">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>

                        <div class="summary-info">
                            <span>Available Stock</span>
                            <strong id="totalStock">—</strong>
                        </div>

                    </div>

                    <div class="summary-card">

                        <div class="summary-icon orange">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>

                        <div class="summary-info">
                            <span>Low Stock</span>
                            <strong id="lowStock">—</strong>
                        </div>

                    </div>

                    <div class="summary-card">

                        <div class="summary-icon red">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>

                        <div class="summary-info">
                            <span>Out of Stock</span>
                            <strong id="outStock">—</strong>
                        </div>

                    </div>

                </div>

                <!-- MEDICINE PANEL -->
                <div class="panel">

                    <!-- TOOLBAR -->
                    <div class="toolbar">

                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                                type="search"
                                id="searchInput"
                                placeholder="Search medicine by name..."
                                autocomplete="off">
                        </div>

                        <select
                            class="filter-select"
                            id="stockFilter"
                            aria-label="Stock filter">
                            <option value="">All Stock</option>
                            <option value="in">In Stock</option>
                            <option value="low">Low Stock</option>
                            <option value="out">Out of Stock</option>
                        </select>

                        <select
                            class="filter-select"
                            id="sortSelect"
                            aria-label="Sort medicines">
                            <option value="">Newest First</option>
                            <option value="name_asc">Name A-Z</option>
                            <option value="name_desc">Name Z-A</option>
                            <option value="price_asc">Price Low-High</option>
                            <option value="price_desc">Price High-Low</option>
                            <option value="stock_asc">Stock Low-High</option>
                            <option value="stock_desc">Stock High-Low</option>
                        </select>

                        <button
                            class="refresh-btn"
                            id="refreshBtn"
                            title="Refresh medicines"
                            aria-label="Refresh medicines">
                            <i class="fa-solid fa-rotate"></i>
                        </button>

                    </div>



                    <!-- ERROR -->
                    <div class="state-box" id="errorState">

                        <div class="state-icon">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>

                        <h3>Unable to load medicines</h3>

                        <p id="errorMessage">
                            Something went wrong while loading the inventory.
                        </p>

                        <button
                            class="primary-btn"
                            id="retryBtn"
                            style="margin:18px auto 0;">
                            <i class="fa-solid fa-rotate"></i>
                            Try Again
                        </button>

                    </div>

                    <!-- EMPTY -->
                    <div class="state-box" id="emptyState">

                        <div class="state-icon">
                            <i class="fa-solid fa-pills"></i>
                        </div>

                        <h3>No medicines found</h3>

                        <p>
                            Try changing your search or filter.
                        </p>

                    </div>

                    <!-- TABLE -->
                    <div class="table-wrap" id="tableWrap" style="display:none;">

                        <table class="medicine-table">

                            <thead>
                                <tr>
                                    <th>Medicine</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody id="medicineTableBody"></tbody>

                        </table>

                    </div>

                    <!-- FOOTER -->
                    <div
                        class="table-footer"
                        id="tableFooter"
                        style="display:none;">

                        <div class="results-info" id="resultsInfo">
                            Showing medicines
                        </div>

                        <div class="pagination" id="pagination"></div>

                    </div>

                </div>

            </section>

        </main>

    </div>

    <!-- TOAST -->
    <div class="toast" id="toast">
        <i class="fa-solid fa-circle-check"></i>
        <span id="toastMessage">Done</span>
    </div>

    <script>
        "use strict";

        const API_URL = "../backend/medicines/get-medicines.php";

        const LOW_STOCK_LIMIT = 10;

        let currentPage = 1;
        let totalPages = 1;
        let searchTimer = null;

        const elements = {
            sidebar: document.getElementById("sidebar"),
            overlay: document.getElementById("overlay"),
            mobileMenu: document.getElementById("mobileMenu"),

            searchInput: document.getElementById("searchInput"),
            stockFilter: document.getElementById("stockFilter"),
            sortSelect: document.getElementById("sortSelect"),
            refreshBtn: document.getElementById("refreshBtn"),

            errorState: document.getElementById("errorState"),
            errorMessage: document.getElementById("errorMessage"),
            emptyState: document.getElementById("emptyState"),

            tableWrap: document.getElementById("tableWrap"),
            tableBody: document.getElementById("medicineTableBody"),
            tableFooter: document.getElementById("tableFooter"),

            resultsInfo: document.getElementById("resultsInfo"),
            pagination: document.getElementById("pagination"),

            totalMedicines: document.getElementById("totalMedicines"),
            totalStock: document.getElementById("totalStock"),
            lowStock: document.getElementById("lowStock"),
            outStock: document.getElementById("outStock"),

            retryBtn: document.getElementById("retryBtn"),

            toast: document.getElementById("toast"),
            toastMessage: document.getElementById("toastMessage")
        };

        /* =========================
           HELPERS
        ========================== */

        function escapeHtml(value) {
            return String(value ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function formatCurrency(value) {
            const number = Number(value) || 0;

            return new Intl.NumberFormat("en-IN", {
                style: "currency",
                currency: "INR",
                maximumFractionDigits: 2
            }).format(number);
        }

        function formatNumber(value) {
            return new Intl.NumberFormat("en-IN").format(
                Number(value) || 0
            );
        }

        function getInitials(name) {
            const words = String(name || "Admin")
                .trim()
                .split(/\s+/)
                .filter(Boolean);

            if (!words.length) {
                return "A";
            }

            if (words.length === 1) {
                return words[0].substring(0, 2).toUpperCase();
            }

            return (
                words[0].charAt(0) +
                words[words.length - 1].charAt(0)
            ).toUpperCase();
        }

        function showToast(message, icon = "fa-circle-check") {
            elements.toastMessage.textContent = message;

            const iconElement = elements.toast.querySelector("i");

            iconElement.className =
                "fa-solid " + icon;

            elements.toast.classList.add("show");

            clearTimeout(showToast.timer);

            showToast.timer = setTimeout(() => {
                elements.toast.classList.remove("show");
            }, 3000);
        }

        function hideAllStates() {
            elements.errorState.classList.remove("show");
            elements.emptyState.classList.remove("show");

            elements.tableWrap.style.display = "none";
            elements.tableFooter.style.display = "none";
        }


        function showError(message) {
            hideAllStates();

            elements.errorMessage.textContent =
                message || "Unable to load medicines.";

            elements.errorState.classList.add("show");
        }

        function showEmpty() {
            hideAllStates();

            elements.emptyState.classList.add("show");
        }

        /* =========================
           STOCK
        ========================== */

        function getStockStatus(stock) {
            const value = Number(stock) || 0;

            if (value <= 0) {
                return {
                    className: "out-stock",
                    text: "Out of Stock"
                };
            }

            if (value <= LOW_STOCK_LIMIT) {
                return {
                    className: "low-stock",
                    text: "Low Stock"
                };
            }

            return {
                className: "in-stock",
                text: "In Stock"
            };
        }

        function getStockClass(stock) {
            const value = Number(stock) || 0;

            if (value <= 0) {
                return "stock-out";
            }

            if (value <= LOW_STOCK_LIMIT) {
                return "stock-low";
            }

            return "stock-good";
        }

        /* =========================
           RENDER TABLE
        ========================== */

        function renderMedicines(medicines) {

            elements.tableBody.innerHTML = "";

            medicines.forEach(medicine => {

                const id = Number(medicine.id) || 0;

                const name =
                    medicine.name ||
                    medicine.medicine_name ||
                    "Unnamed Medicine";

                const category =
                    medicine.category ||
                    medicine.category_name ||
                    "Uncategorized";

                const price =
                    Number(medicine.price) || 0;

                const stock =
                    Number(medicine.stock) || 0;

                const image =
                    medicine.image ||
                    "";

                const status =
                    getStockStatus(stock);

                const stockClass =
                    getStockClass(stock);

                let imageHtml = `
                <i class="fa-solid fa-pills"></i>
            `;

                if (image) {
                    imageHtml = `
                    <img
                        src="${escapeHtml(image)}"
                        alt="${escapeHtml(name)}"
                        loading="lazy"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='grid';"
                    >
                    <i
                        class="fa-solid fa-pills"
                        style="display:none;"
                    ></i>
                `;
                }

                const row = document.createElement("tr");

                row.innerHTML = `
                <td>
                    <div class="medicine-info">

                        <div class="medicine-image">
                            ${imageHtml}
                        </div>

                        <div>
                            <div class="medicine-name">
                                ${escapeHtml(name)}
                            </div>

                            <div class="medicine-id">
                                MED-${String(id).padStart(4, "0")}
                            </div>
                        </div>

                    </div>
                </td>

                <td>
                    <span class="category-name">
                        ${escapeHtml(category)}
                    </span>
                </td>

                <td>
                    <span class="price">
                        ${formatCurrency(price)}
                    </span>
                </td>

                <td>
                    <span class="stock-number ${stockClass}">
                        ${formatNumber(stock)}
                    </span>
                </td>

                <td>
                    <span class="status ${status.className}">
                        ${status.text}
                    </span>
                </td>
            `;

                elements.tableBody.appendChild(row);
            });
        }

        /* =========================
           SUMMARY
        ========================== */

        function calculateSummary(medicines) {

            let stockTotal = 0;
            let low = 0;
            let out = 0;

            medicines.forEach(medicine => {

                const stock =
                    Number(medicine.stock) || 0;

                stockTotal += stock;

                if (stock <= 0) {
                    out++;
                } else if (stock <= LOW_STOCK_LIMIT) {
                    low++;
                }
            });

            elements.totalMedicines.textContent =
                formatNumber(medicines.length);

            elements.totalStock.textContent =
                formatNumber(stockTotal);

            elements.lowStock.textContent =
                formatNumber(low);

            elements.outStock.textContent =
                formatNumber(out);
        }

        /* =========================
           PAGINATION
        ========================== */

        function renderPagination(pagination) {

            elements.pagination.innerHTML = "";

            const current =
                Number(pagination?.current_page) ||
                currentPage;

            const pages =
                Number(pagination?.total_pages) ||
                1;

            currentPage = current;
            totalPages = pages;

            if (pages <= 1) {
                return;
            }

            const previous = document.createElement("button");

            previous.className = "page-btn";
            previous.innerHTML =
                '<i class="fa-solid fa-chevron-left"></i>';

            previous.disabled = current <= 1;

            previous.addEventListener("click", () => {
                if (current > 1) {
                    loadMedicines(current - 1);
                }
            });

            elements.pagination.appendChild(previous);

            let start = Math.max(1, current - 2);
            let end = Math.min(pages, current + 2);

            if (current <= 2) {
                end = Math.min(pages, 5);
            }

            if (current >= pages - 1) {
                start = Math.max(1, pages - 4);
            }

            for (let page = start; page <= end; page++) {

                const button =
                    document.createElement("button");

                button.className =
                    "page-btn" +
                    (page === current ? " active" : "");

                button.textContent = page;

                button.addEventListener("click", () => {
                    loadMedicines(page);
                });

                elements.pagination.appendChild(button);
            }

            const next = document.createElement("button");

            next.className = "page-btn";

            next.innerHTML =
                '<i class="fa-solid fa-chevron-right"></i>';

            next.disabled = current >= pages;

            next.addEventListener("click", () => {
                if (current < pages) {
                    loadMedicines(current + 1);
                }
            });

            elements.pagination.appendChild(next);
        }

        /* =========================
           LOAD MEDICINES
        ========================== */

        async function loadMedicines(page = 1) {

            currentPage = page;

            elements.refreshBtn.classList.add("loading");

            try {

                const search =
                    elements.searchInput.value.trim();

                const stockFilter =
                    elements.stockFilter.value;

                const sort =
                    elements.sortSelect.value;

                const params =
                    new URLSearchParams();

                params.set("page", page);
                params.set("limit", "20");

                if (search) {
                    params.set("search", search);
                }

                /*
                 * Existing backend supports in_stock.
                 *
                 * For the "All Stock" and "In Stock"
                 * filters we can use it directly.
                 */
                if (stockFilter === "in") {
                    params.set("in_stock", "1");
                }

                if (sort) {
                    switch (sort) {

                        case "name_asc":
                            params.set("sort", "name_asc");
                            break;

                        case "name_desc":
                            params.set("sort", "name_desc");
                            break;

                        case "price_asc":
                            params.set("sort", "price_asc");
                            break;

                        case "price_desc":
                            params.set("sort", "price_desc");
                            break;

                        case "stock_asc":
                            params.set("sort", "stock_asc");
                            break;

                        case "stock_desc":
                            params.set("sort", "stock_desc");
                            break;
                    }
                }

                const response =
                    await fetch(
                        `${API_URL}?${params.toString()}`, {
                            method: "GET",
                            credentials: "same-origin",
                            headers: {
                                "Accept": "application/json"
                            },
                            cache: "no-store"
                        }
                    );

                const data =
                    await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message ||
                        "Unable to load medicines."
                    );
                }

                let medicines =
                    Array.isArray(data.medicines) ?
                    data.medicines : [];

                /*
                 * Low / out of stock filters are handled
                 * here because the current medicine API
                 * does not require a dedicated stock-status
                 * parameter.
                 */
                if (stockFilter === "low") {

                    medicines =
                        medicines.filter(medicine => {
                            const stock =
                                Number(medicine.stock) || 0;

                            return (
                                stock > 0 &&
                                stock <= LOW_STOCK_LIMIT
                            );
                        });
                }

                if (stockFilter === "out") {

                    medicines =
                        medicines.filter(medicine => {
                            const stock =
                                Number(medicine.stock) || 0;

                            return stock <= 0;
                        });
                }

                if (!medicines.length) {

                    calculateSummary([]);

                    showEmpty();

                    elements.resultsInfo.textContent =
                        "No medicines found.";

                    return;
                }

                renderMedicines(medicines);

                calculateSummary(medicines);

                renderPagination(
                    data.pagination || {
                        current_page: page,
                        total_pages: 1
                    }
                );

                elements.tableWrap.style.display =
                    "block";

                elements.tableFooter.style.display =
                    "flex";

                const pagination =
                    data.pagination || {};

                const total =
                    Number(pagination.total) ||
                    medicines.length;

                const current =
                    Number(pagination.current_page) ||
                    page;

                const limit =
                    Number(pagination.limit) ||
                    medicines.length;

                const start =
                    total === 0 ?
                    0 :
                    ((current - 1) * limit) + 1;

                const end =
                    Math.min(
                        current * limit,
                        total
                    );

                elements.resultsInfo.textContent =
                    `Showing ${formatNumber(start)}–${formatNumber(end)} of ${formatNumber(total)} medicines`;

            } catch (error) {

                console.error(
                    "Medicine loading error:",
                    error
                );

                showError(
                    error.message ||
                    "Unable to connect to the medicine API."
                );

            } finally {

                elements.refreshBtn.classList.remove(
                    "loading"
                );
            }
        }

        /* =========================
           SEARCH
        ========================== */

        elements.searchInput.addEventListener(
            "input",
            () => {

                clearTimeout(searchTimer);

                searchTimer = setTimeout(() => {
                    loadMedicines(1);
                }, 350);
            }
        );

        /* =========================
           FILTERS
        ========================== */

        elements.stockFilter.addEventListener(
            "change",
            () => {
                loadMedicines(1);
            }
        );

        elements.sortSelect.addEventListener(
            "change",
            () => {
                loadMedicines(1);
            }
        );

        /* =========================
           REFRESH
        ========================== */

        elements.refreshBtn.addEventListener(
            "click",
            () => {
                loadMedicines(currentPage);
            }
        );

        elements.retryBtn.addEventListener(
            "click",
            () => {
                loadMedicines(currentPage);
            }
        );

        /* =========================
           MEDICINE ACTIONS
        ========================== */

        window.editMedicine = function(id) {

            if (!id) {
                return;
            }

            window.location.href =
                `edit-medicine.php?id=${encodeURIComponent(id)}`;
        };

        window.viewMedicine = function(id) {

            if (!id) {
                return;
            }

            window.open(
                `../medicine-details.php?id=${encodeURIComponent(id)}`,
                "_blank",
                "noopener"
            );
        };

        /* =========================
           MOBILE SIDEBAR
        ========================== */

        function openSidebar() {

            elements.sidebar.classList.add("open");
            elements.overlay.classList.add("show");
            document.body.style.overflow = "hidden";
        }

        function closeSidebar() {

            elements.sidebar.classList.remove("open");
            elements.overlay.classList.remove("show");
            document.body.style.overflow = "";
        }

        elements.mobileMenu.addEventListener(
            "click",
            openSidebar
        );

        elements.overlay.addEventListener(
            "click",
            closeSidebar
        );

        document.querySelectorAll(".nav-item").forEach(
            item => {

                item.addEventListener(
                    "click",
                    () => {

                        if (
                            window.innerWidth <= 900
                        ) {
                            closeSidebar();
                        }
                    }
                );
            }
        );

        window.addEventListener(
            "resize",
            () => {

                if (window.innerWidth > 900) {
                    closeSidebar();
                }
            }
        );

        /* =========================
           ADMIN AVATAR
        ========================== */

        const adminName =
            <?= json_encode($adminName, JSON_UNESCAPED_UNICODE); ?>;

        const initials =
            getInitials(adminName);

        document.getElementById(
            "sidebarAvatar"
        ).textContent = initials;

        document.getElementById(
            "topAvatar"
        ).textContent = initials;

        /* =========================
           INITIAL LOAD
        ========================== */

        loadMedicines(1);
    </script>

</body>

</html>