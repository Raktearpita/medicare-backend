<?php

/**
 * MediCare Pharmacy
 * Admin - Order Details
 *
 * File:
 * admin/order-details.php
 */

declare(strict_types=1);

session_start();


// ---------------------------------------------------------
// Admin authentication
// ---------------------------------------------------------

if (
    empty($_SESSION['admin_id']) ||
    ($_SESSION['admin_logged_in'] ?? false) !== true ||
    ($_SESSION['login_type'] ?? '') !== 'admin'
) {
    header('Location: ../login.php');
    exit;
}


// ---------------------------------------------------------
// Get order ID
// ---------------------------------------------------------

$orderId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$orderId || $orderId <= 0) {
    header('Location: orders.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Order Details | MediCare Admin</title>

    <meta
        name="description"
        content="View MediCare Pharmacy order details">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Google Fonts -->
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap"
        rel="stylesheet">

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
            --danger: #d95353;
            --warning: #d78b20;
            --info: #3478c8;

            --shadow: 0 20px 60px rgba(18, 107, 91, .10);

            --radius: 18px;

            --sidebar-width: 260px;

            --container: 1500px;
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
            font-family: inherit;
        }


        button {
            cursor: pointer;
        }


        a {
            text-decoration: none;
            color: inherit;
        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;

            background: var(--dark);

            color: #fff;

            display: flex;
            flex-direction: column;

            z-index: 1000;

            transition: transform .3s ease;
        }


        .brand {
            height: 82px;

            display: flex;
            align-items: center;

            padding: 0 25px;

            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }


        .brand-icon {
            width: 42px;
            height: 42px;

            border-radius: 13px;

            display: grid;
            place-items: center;

            background: var(--primary);

            margin-right: 12px;

            font-size: 19px;
        }


        .brand-text strong {
            display: block;

            font-family: "Manrope", sans-serif;

            font-size: 18px;
            font-weight: 800;
        }


        .brand-text span {
            display: block;

            font-size: 11px;

            color: rgba(255, 255, 255, .55);

            margin-top: 2px;
        }


        .nav {
            padding: 22px 14px;

            flex: 1;

            overflow-y: auto;
        }


        .nav-label {
            padding: 0 12px 10px;

            color: rgba(255, 255, 255, .38);

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 1.2px;
        }


        .nav a {
            display: flex;

            align-items: center;

            gap: 13px;

            padding: 12px 13px;

            margin-bottom: 4px;

            border-radius: 11px;

            color: rgba(255, 255, 255, .67);

            font-size: 14px;

            font-weight: 600;

            transition: .2s;
        }


        .nav a i {
            width: 20px;

            text-align: center;

            font-size: 15px;
        }


        .nav a:hover,
        .nav a.active {
            color: #fff;

            background: rgba(255, 255, 255, .09);
        }


        .nav a.active {
            background: var(--primary);
        }


        .sidebar-bottom {
            padding: 16px;

            border-top: 1px solid rgba(255, 255, 255, .08);
        }


        .admin-mini {
            display: flex;
            align-items: center;

            gap: 11px;

            margin-bottom: 12px;

            padding: 10px;
        }


        .admin-avatar {
            width: 38px;
            height: 38px;

            border-radius: 50%;

            background: var(--primary);

            display: grid;
            place-items: center;

            font-size: 14px;
        }


        .admin-mini strong {
            display: block;

            font-size: 13px;

            color: #fff;
        }


        .admin-mini span {
            display: block;

            font-size: 11px;

            color: rgba(255, 255, 255, .45);

            margin-top: 2px;
        }


        .logout-btn {
            width: 100%;

            border: 0;

            background: rgba(255, 255, 255, .06);

            color: rgba(255, 255, 255, .72);

            padding: 11px;

            border-radius: 10px;

            font-weight: 600;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 8px;
        }


        .logout-btn:hover {
            background: rgba(217, 83, 83, .18);

            color: #ffb0b0;
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {
            margin-left: var(--sidebar-width);

            min-height: 100vh;
        }


        .topbar {
            height: 82px;

            background: rgba(255, 255, 255, .94);

            backdrop-filter: blur(12px);

            border-bottom: 1px solid var(--border);

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 32px;

            position: sticky;

            top: 0;

            z-index: 500;
        }


        .top-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }


        .mobile-menu {
            display: none;

            width: 40px;
            height: 40px;

            border: 1px solid var(--border);

            background: #fff;

            border-radius: 10px;

            color: var(--dark);
        }


        .page-title h1 {
            font-family: "Manrope", sans-serif;

            font-size: 22px;

            color: var(--dark);
        }


        .page-title p {
            color: var(--muted);

            font-size: 12px;

            margin-top: 3px;
        }


        .top-actions {
            display: flex;

            align-items: center;

            gap: 10px;
        }


        .top-btn {
            height: 40px;

            padding: 0 14px;

            border: 1px solid var(--border);

            background: #fff;

            color: var(--text);

            border-radius: 10px;

            font-weight: 600;

            display: inline-flex;

            align-items: center;

            gap: 8px;
        }


        .top-btn:hover {
            border-color: var(--primary);

            color: var(--primary);
        }


        .content {
            width: min(var(--container), calc(100% - 48px));

            margin: 0 auto;

            padding: 30px 0 60px;
        }


        /* =====================================================
           LOADING / ERROR
        ===================================================== */

        .state {
            min-height: 420px;

            display: flex;

            align-items: center;

            justify-content: center;

            text-align: center;
        }


        .state-card {
            max-width: 480px;

            background: #fff;

            border: 1px solid var(--border);

            border-radius: 22px;

            padding: 45px 30px;

            box-shadow: var(--shadow);
        }


        .state-icon {
            width: 64px;
            height: 64px;

            border-radius: 50%;

            display: grid;
            place-items: center;

            margin: 0 auto 18px;

            background: var(--primary-light);

            color: var(--primary);

            font-size: 25px;
        }


        .state-card.error .state-icon {
            background: #fff0f0;

            color: var(--danger);
        }


        .state-card h2 {
            font-family: "Manrope", sans-serif;

            color: var(--dark);

            font-size: 21px;

            margin-bottom: 8px;
        }


        .state-card p {
            color: var(--muted);

            font-size: 14px;

            line-height: 1.7;
        }


        .retry-btn {
            margin-top: 20px;

            border: 0;

            background: var(--primary);

            color: #fff;

            padding: 11px 18px;

            border-radius: 10px;

            font-weight: 700;
        }


        .retry-btn:hover {
            background: var(--primary-dark);
        }


        /* =====================================================
           ORDER HEADER
        ===================================================== */

        .order-header {
            background: #fff;

            border: 1px solid var(--border);

            border-radius: var(--radius);

            padding: 23px 25px;

            margin-bottom: 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            box-shadow: var(--shadow);
        }


        .order-heading {
            display: flex;

            align-items: center;

            gap: 15px;
        }


        .order-icon {
            width: 52px;
            height: 52px;

            border-radius: 14px;

            background: var(--primary-light);

            color: var(--primary);

            display: grid;

            place-items: center;

            font-size: 20px;
        }


        .order-heading h2 {
            font-family: "Manrope", sans-serif;

            color: var(--dark);

            font-size: 21px;
        }


        .order-heading p {
            color: var(--muted);

            font-size: 12px;

            margin-top: 4px;
        }


        .header-actions {
            display: flex;

            gap: 9px;

            flex-wrap: wrap;
        }


        .btn {
            border: 0;

            border-radius: 10px;

            padding: 11px 15px;

            font-weight: 700;

            font-size: 13px;

            display: inline-flex;

            align-items: center;

            gap: 8px;
        }


        .btn-primary {
            background: var(--primary);

            color: #fff;
        }


        .btn-primary:hover {
            background: var(--primary-dark);
        }


        .btn-light {
            background: var(--primary-light);

            color: var(--primary);
        }


        .btn-light:hover {
            background: #d9eee8;
        }


        /* =====================================================
           GRID
        ===================================================== */

        .grid {
            display: grid;

            grid-template-columns: minmax(0, 1fr) 370px;

            gap: 20px;

            align-items: start;
        }


        .card {
            background: #fff;

            border: 1px solid var(--border);

            border-radius: var(--radius);

            box-shadow: var(--shadow);

            overflow: hidden;

            margin-bottom: 20px;
        }


        .card-header {
            padding: 18px 21px;

            border-bottom: 1px solid var(--border);

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 10px;
        }


        .card-header h3 {
            font-family: "Manrope", sans-serif;

            color: var(--dark);

            font-size: 15px;
        }


        .card-body {
            padding: 20px;
        }


        /* =====================================================
           STATUS
        ===================================================== */

        .status-badge {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            border-radius: 999px;

            padding: 7px 11px;

            font-size: 11px;

            font-weight: 800;

            text-transform: capitalize;

            white-space: nowrap;
        }


        .status-pending {
            color: #9a650d;
            background: #fff6df;
        }


        .status-confirmed {
            color: #2f6cae;
            background: #eaf4ff;
        }


        .status-packed {
            color: #7657a9;
            background: #f2ecfb;
        }


        .status-shipped {
            color: #3478c8;
            background: #eaf3ff;
        }


        .status-out_for_delivery {
            color: #c26a18;
            background: #fff1e4;
        }


        .status-delivered {
            color: #137451;
            background: #e5f7ef;
        }


        .status-cancelled {
            color: #b33e3e;
            background: #ffebeb;
        }


        /* =====================================================
           ITEMS
        ===================================================== */

        .medicine-row {
            display: grid;

            grid-template-columns: 70px minmax(0, 1fr) auto auto;

            gap: 15px;

            align-items: center;

            padding: 15px 0;

            border-bottom: 1px solid var(--border);
        }


        .medicine-row:first-child {
            padding-top: 0;
        }


        .medicine-row:last-child {
            border-bottom: 0;

            padding-bottom: 0;
        }


        .medicine-image {
            width: 70px;
            height: 70px;

            border-radius: 12px;

            background: var(--background);

            border: 1px solid var(--border);

            overflow: hidden;

            display: grid;

            place-items: center;

            color: var(--muted);

            font-size: 21px;
        }


        .medicine-image img {
            width: 100%;
            height: 100%;

            object-fit: cover;
        }


        .medicine-info h4 {
            color: var(--dark);

            font-size: 14px;

            margin-bottom: 5px;
        }


        .medicine-info span {
            color: var(--muted);

            font-size: 12px;
        }


        .price {
            color: var(--dark);

            font-weight: 800;

            font-size: 14px;

            white-space: nowrap;
        }


        .quantity {
            color: var(--muted);

            font-size: 13px;

            white-space: nowrap;
        }


        /* =====================================================
           INFO LIST
        ===================================================== */

        .info-list {
            display: grid;

            gap: 14px;
        }


        .info-row {
            display: flex;

            justify-content: space-between;

            gap: 20px;
        }


        .info-label {
            color: var(--muted);

            font-size: 12px;
        }


        .info-value {
            color: var(--dark);

            font-size: 13px;

            font-weight: 700;

            text-align: right;

            word-break: break-word;
        }


        .address-box {
            background: var(--background);

            border: 1px solid var(--border);

            border-radius: 13px;

            padding: 15px;

            line-height: 1.7;

            color: var(--text);

            font-size: 13px;
        }


        .address-name {
            color: var(--dark);

            font-weight: 800;

            margin-bottom: 5px;
        }


        /* =====================================================
           SUMMARY
        ===================================================== */

        .summary-row {
            display: flex;

            justify-content: space-between;

            padding: 10px 0;

            font-size: 13px;
        }


        .summary-row span:first-child {
            color: var(--muted);
        }


        .summary-row span:last-child {
            color: var(--dark);

            font-weight: 700;
        }


        .summary-total {
            border-top: 1px solid var(--border);

            margin-top: 8px;

            padding-top: 16px;

            display: flex;

            justify-content: space-between;

            font-size: 17px;

            font-weight: 800;
        }


        .summary-total span:last-child {
            color: var(--primary);
        }


        /* =====================================================
           TRACKING
        ===================================================== */

        .timeline {
            position: relative;

            padding-left: 28px;
        }


        .timeline::before {
            content: "";

            position: absolute;

            left: 7px;

            top: 7px;

            bottom: 7px;

            width: 2px;

            background: var(--border);
        }


        .timeline-item {
            position: relative;

            padding-bottom: 22px;
        }


        .timeline-item:last-child {
            padding-bottom: 0;
        }


        .timeline-dot {
            position: absolute;

            left: -28px;

            top: 3px;

            width: 16px;
            height: 16px;

            border-radius: 50%;

            background: #fff;

            border: 3px solid var(--border);

            z-index: 2;
        }


        .timeline-item.active .timeline-dot {
            border-color: var(--primary);

            background: var(--primary);
        }


        .timeline-title {
            color: var(--dark);

            font-size: 13px;

            font-weight: 800;

            text-transform: capitalize;
        }


        .timeline-description {
            color: var(--muted);

            font-size: 12px;

            margin-top: 4px;

            line-height: 1.5;
        }


        .timeline-date {
            color: #98a6a2;

            font-size: 10px;

            margin-top: 4px;
        }


        /* =====================================================
           CUSTOMER
        ===================================================== */

        .customer-card {
            display: flex;

            align-items: center;

            gap: 13px;

            margin-bottom: 18px;
        }


        .customer-avatar {
            width: 48px;
            height: 48px;

            border-radius: 50%;

            background: var(--primary-light);

            color: var(--primary);

            display: grid;

            place-items: center;

            font-weight: 800;
        }


        .customer-card h4 {
            color: var(--dark);

            font-size: 14px;

            margin-bottom: 3px;
        }


        .customer-card p {
            color: var(--muted);

            font-size: 11px;

            word-break: break-word;
        }


        /* =====================================================
           TOAST
        ===================================================== */

        .toast {
            position: fixed;

            right: 24px;

            bottom: 24px;

            background: var(--dark);

            color: #fff;

            padding: 13px 17px;

            border-radius: 11px;

            box-shadow: 0 15px 40px rgba(0, 0, 0, .18);

            font-size: 13px;

            z-index: 5000;

            transform: translateY(120px);

            opacity: 0;

            transition: .3s;

            pointer-events: none;
        }


        .toast.show {
            transform: translateY(0);

            opacity: 1;
        }


        /* =====================================================
           OVERLAY
        ===================================================== */

        .sidebar-overlay {
            display: none;

            position: fixed;

            inset: 0;

            background: rgba(0, 0, 0, .45);

            z-index: 900;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 900px) {

            .sidebar {
                transform: translateX(-100%);
            }


            .sidebar.open {
                transform: translateX(0);
            }


            .sidebar-overlay.show {
                display: block;
            }


            .main {
                margin-left: 0;
            }


            .mobile-menu {
                display: grid;

                place-items: center;
            }

        }


        @media (max-width: 650px) {

            .topbar {
                padding: 0 15px;
            }


            .content {
                width: min(100% - 24px, var(--container));

                padding-top: 18px;
            }


            .top-actions .top-btn span {
                display: none;
            }


            .order-header {
                align-items: flex-start;

                flex-direction: column;
            }


            .header-actions {
                width: 100%;
            }


            .header-actions .btn {
                flex: 1;

                justify-content: center;
            }


            .medicine-row {
                grid-template-columns: 58px minmax(0, 1fr);
            }


            .medicine-image {
                width: 58px;
                height: 58px;
            }


            .medicine-row .price,
            .medicine-row .quantity {
                grid-column: 2;
            }


            .medicine-row .quantity {
                margin-top: -10px;
            }

        }


        @media (max-width: 420px) {

            .page-title h1 {
                font-size: 18px;
            }


            .page-title p {
                display: none;
            }


            .order-heading h2 {
                font-size: 17px;
            }


            .card-body {
                padding: 16px;
            }


            .order-header {
                padding: 17px;
            }


            .info-row {
                flex-direction: column;

                gap: 3px;
            }


            .info-value {
                text-align: left;
            }

        }


        @media (min-width: 1800px) {

            :root {
                --sidebar-width: 290px;
            }


            .content {
                max-width: 1700px;
            }


            .sidebar {
                width: var(--sidebar-width);
            }


            .main {
                margin-left: var(--sidebar-width);
            }

        }
    </style>

</head>

<body>


    <!-- =========================================================
     SIDEBAR
========================================================= -->

    <aside class="sidebar" id="sidebar">

        <div class="brand">

            <div class="brand-icon">
                <i class="fa-solid fa-capsules"></i>
            </div>

            <div class="brand-text">
                <strong>MediCare</strong>
                <span>Pharmacy Admin</span>
            </div>

        </div>


        <nav class="nav">

            <div class="nav-label">
                Main
            </div>


            <a href="dashboard.php">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>


            <a href="medicines.php">
                <i class="fa-solid fa-pills"></i>
                <span>Medicines</span>
            </a>


            <a href="add-medicine.php">
                <i class="fa-solid fa-circle-plus"></i>
                <span>Add Medicine</span>
            </a>


            <a href="orders.php" class="active">
                <i class="fa-solid fa-bag-shopping"></i>
                <span>Orders</span>
            </a>


            <div class="nav-label" style="margin-top:20px;">
                Management
            </div>


            <a href="users.php">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>


            <a href="categories.php">
                <i class="fa-solid fa-layer-group"></i>
                <span>Categories</span>
            </a>


            <a href="profile.php">
                <i class="fa-solid fa-user-gear"></i>
                <span>Admin Profile</span>
            </a>


            <div class="nav-label" style="margin-top:20px;">
                Store
            </div>


            <a href="../index.php">
                <i class="fa-solid fa-store"></i>
                <span>View Store</span>
            </a>

        </nav>


        <div class="sidebar-bottom">

            <div class="admin-mini">

                <div class="admin-avatar">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

                <div>
                    <strong>
                        <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?>
                    </strong>

                    <span>Administrator</span>
                </div>

            </div>


            <button
                type="button"
                class="logout-btn"
                onclick="logoutAdmin()">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </button>

        </div>

    </aside>


    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        onclick="closeSidebar()"></div>


    <!-- =========================================================
     MAIN
========================================================= -->

    <main class="main">

        <header class="topbar">

            <div class="top-left">

                <button
                    type="button"
                    class="mobile-menu"
                    onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>


                <div class="page-title">

                    <h1>Order Details</h1>

                    <p>
                        Manage and review customer order
                    </p>

                </div>

            </div>


            <div class="top-actions">

                <a
                    href="orders.php"
                    class="top-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Orders</span>
                </a>

            </div>

        </header>


        <section class="content" id="content">

            <!-- Loading state -->

            <div class="state" id="loadingState">

                <div class="state-card">

                    <div class="state-icon">

                        <i class="fa-solid fa-spinner fa-spin"></i>

                    </div>

                    <h2>Loading order</h2>

                    <p>
                        Please wait while we load the order information.
                    </p>

                </div>

            </div>


            <!-- Error state -->

            <div
                class="state"
                id="errorState"
                style="display:none;">

                <div class="state-card error">

                    <div class="state-icon">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>

                    <h2>Unable to load order</h2>

                    <p id="errorMessage">
                        Something went wrong while loading this order.
                    </p>

                    <button
                        type="button"
                        class="retry-btn"
                        onclick="loadOrder()">
                        <i class="fa-solid fa-rotate-right"></i>
                        Try Again
                    </button>

                </div>

            </div>


            <!-- Order content -->

            <div
                id="orderContent"
                style="display:none;">

                <div class="order-header">

                    <div class="order-heading">

                        <div class="order-icon">

                            <i class="fa-solid fa-receipt"></i>

                        </div>

                        <div>

                            <h2 id="orderNumber">
                                Order
                            </h2>

                            <p id="orderDate">
                                -
                            </p>

                        </div>

                    </div>


                    <div class="header-actions">

                        <span
                            id="orderStatus"
                            class="status-badge">
                            -
                        </span>


                        <button
                            type="button"
                            class="btn btn-light"
                            onclick="window.print()">
                            <i class="fa-solid fa-print"></i>
                            Print
                        </button>

                    </div>

                </div>


                <div class="grid">

                    <!-- LEFT -->

                    <div>

                        <!-- Medicines -->

                        <div class="card">

                            <div class="card-header">

                                <h3>
                                    <i class="fa-solid fa-pills"></i>
                                    Ordered Medicines
                                </h3>

                                <span
                                    id="itemCount"
                                    class="info-label">
                                    0 items
                                </span>

                            </div>


                            <div class="card-body" id="itemsContainer">

                            </div>

                        </div>


                        <!-- Delivery -->

                        <div class="card">

                            <div class="card-header">

                                <h3>
                                    <i class="fa-solid fa-location-dot"></i>
                                    Delivery Address
                                </h3>

                            </div>


                            <div class="card-body">

                                <div
                                    class="address-box"
                                    id="addressContainer">
                                </div>

                            </div>

                        </div>


                        <!-- Tracking -->

                        <div class="card">

                            <div class="card-header">

                                <h3>
                                    <i class="fa-solid fa-route"></i>
                                    Order Tracking
                                </h3>

                            </div>


                            <div class="card-body">

                                <div
                                    class="timeline"
                                    id="timeline">
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- RIGHT -->

                    <div>

                        <!-- Customer -->

                        <div class="card">

                            <div class="card-header">

                                <h3>
                                    Customer
                                </h3>

                            </div>


                            <div class="card-body">

                                <div class="customer-card">

                                    <div
                                        class="customer-avatar"
                                        id="customerAvatar">
                                        <i class="fa-solid fa-user"></i>
                                    </div>

                                    <div>

                                        <h4 id="customerName">
                                            -
                                        </h4>

                                        <p id="customerEmail">
                                            -
                                        </p>

                                    </div>

                                </div>


                                <div class="info-list">

                                    <div class="info-row">

                                        <span class="info-label">
                                            Phone
                                        </span>

                                        <span
                                            class="info-value"
                                            id="customerPhone">
                                            -
                                        </span>

                                    </div>


                                    <div class="info-row">

                                        <span class="info-label">
                                            Customer ID
                                        </span>

                                        <span
                                            class="info-value"
                                            id="customerId">
                                            -
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Payment -->

                        <div class="card">

                            <div class="card-header">

                                <h3>
                                    Payment
                                </h3>

                            </div>


                            <div class="card-body">

                                <div class="info-list">

                                    <div class="info-row">

                                        <span class="info-label">
                                            Method
                                        </span>

                                        <span
                                            class="info-value"
                                            id="paymentMethod">
                                            -
                                        </span>

                                    </div>


                                    <div class="info-row">

                                        <span class="info-label">
                                            Payment Status
                                        </span>

                                        <span
                                            class="info-value"
                                            id="paymentStatus">
                                            -
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Summary -->

                        <div class="card">

                            <div class="card-header">

                                <h3>
                                    Order Summary
                                </h3>

                            </div>


                            <div class="card-body">

                                <div class="summary-row">

                                    <span>Subtotal</span>

                                    <span id="subtotal">
                                        ₹0.00
                                    </span>

                                </div>


                                <div class="summary-row">

                                    <span>Delivery</span>

                                    <span id="deliveryCharge">
                                        ₹0.00
                                    </span>

                                </div>


                                <div class="summary-total">

                                    <span>Total</span>

                                    <span id="totalAmount">
                                        ₹0.00
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>


    <div
        class="toast"
        id="toast"></div>


    <script>
        const ORDER_ID = <?= (int) $orderId ?>;


        // =====================================================
        // Helpers
        // =====================================================

        function escapeHtml(value) {

            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }


        function formatMoney(value) {

            const number = Number(value || 0);

            return '₹' + number.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }


        function formatDate(value) {

            if (!value) {
                return '-';
            }

            const date = new Date(
                String(value).replace(' ', 'T')
            );

            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

        }


        function formatStatus(status) {

            if (!status) {
                return 'Unknown';
            }

            return String(status)
                .replace(/_/g, ' ')
                .replace(/\b\w/g, letter => letter.toUpperCase());

        }


        function statusClass(status) {

            return 'status-' +
                String(status || 'pending')
                .toLowerCase()
                .replace(/\s+/g, '_');

        }


        function showToast(message) {

            const toast = document.getElementById('toast');

            toast.textContent = message;

            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);

        }


        // =====================================================
        // Sidebar
        // =====================================================

        function toggleSidebar() {

            document
                .getElementById('sidebar')
                .classList.toggle('open');

            document
                .getElementById('sidebarOverlay')
                .classList.toggle('show');

        }


        function closeSidebar() {

            document
                .getElementById('sidebar')
                .classList.remove('open');

            document
                .getElementById('sidebarOverlay')
                .classList.remove('show');

        }


        // =====================================================
        // Load Order
        // =====================================================

        async function loadOrder() {

            const loadingState =
                document.getElementById('loadingState');

            const errorState =
                document.getElementById('errorState');

            const orderContent =
                document.getElementById('orderContent');

            const errorMessage =
                document.getElementById('errorMessage');


            loadingState.style.display = 'flex';

            errorState.style.display = 'none';

            orderContent.style.display = 'none';


            try {

                /*
                 * IMPORTANT:
                 *
                 * This is the ADMIN endpoint.
                 *
                 * NOT:
                 * ../backend/orders/get-order.php
                 *
                 * YES:
                 * ../backend/admin/get-order.php
                 */

                const url =
                    '../backend/admin/get-order.php?id=' +
                    encodeURIComponent(ORDER_ID);


                const response = await fetch(
                    url, {
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );


                const rawText = await response.text();


                let data;

                try {

                    data = JSON.parse(rawText);

                } catch (jsonError) {

                    console.error(
                        'Invalid JSON response:',
                        rawText
                    );

                    throw new Error(
                        'Server returned an invalid response. HTTP ' +
                        response.status
                    );

                }


                if (!response.ok || !data.success) {

                    throw new Error(
                        data.message ||
                        'Unable to load order.'
                    );

                }


                if (!data.order) {

                    throw new Error(
                        'Order information was not returned by the server.'
                    );

                }


                renderOrder(data.order);


                loadingState.style.display = 'none';

                errorState.style.display = 'none';

                orderContent.style.display = 'block';


            } catch (error) {

                console.error(
                    'Order loading error:',
                    error
                );


                loadingState.style.display = 'none';

                orderContent.style.display = 'none';

                errorState.style.display = 'flex';


                errorMessage.textContent =
                    error.message ||
                    'Unable to load order.';

            }

        }


        // =====================================================
        // Render Order
        // =====================================================

        function renderOrder(order) {

            document.getElementById('orderNumber').textContent =
                order.order_number ||
                ('ORD-' + order.id);


            document.getElementById('orderDate').textContent =
                'Placed on ' +
                formatDate(order.created_at);


            const statusElement =
                document.getElementById('orderStatus');


            statusElement.className =
                'status-badge ' +
                statusClass(order.status);


            statusElement.textContent =
                formatStatus(order.status);


            // -------------------------------------------------
            // Customer
            // -------------------------------------------------

            const customer =
                order.customer || {};


            document.getElementById('customerName').textContent =
                customer.name || 'Customer';


            document.getElementById('customerEmail').textContent =
                customer.email || '-';


            document.getElementById('customerPhone').textContent =
                customer.phone || '-';


            document.getElementById('customerId').textContent =
                order.user_id ?
                '#' + order.user_id :
                '-';


            const customerName =
                customer.name || 'Customer';


            document.getElementById('customerAvatar').textContent =
                customerName
                .trim()
                .charAt(0)
                .toUpperCase();


            // -------------------------------------------------
            // Payment
            // -------------------------------------------------

            document.getElementById('paymentMethod').textContent =
                formatStatus(order.payment_method || '-');


            document.getElementById('paymentStatus').textContent =
                formatStatus(order.payment_status || '-');


            // -------------------------------------------------
            // Summary
            // -------------------------------------------------

            document.getElementById('subtotal').textContent =
                formatMoney(order.subtotal);


            document.getElementById('deliveryCharge').textContent =
                Number(order.delivery_charge || 0) === 0 ?
                'FREE' :
                formatMoney(order.delivery_charge);


            document.getElementById('totalAmount').textContent =
                formatMoney(order.total_amount);


            // -------------------------------------------------
            // Items
            // -------------------------------------------------

            renderItems(order.items || []);


            // -------------------------------------------------
            // Address
            // -------------------------------------------------

            renderAddress(
                order.shipping_address || {}
            );


            // -------------------------------------------------
            // Tracking
            // -------------------------------------------------

            renderTracking(
                order.tracking || [],
                order.status
            );

        }


        // =====================================================
        // Render Items
        // =====================================================

        function renderItems(items) {

            const container =
                document.getElementById('itemsContainer');


            document.getElementById('itemCount').textContent =
                items.length +
                (items.length === 1 ? ' item' : ' items');


            if (!items.length) {

                container.innerHTML = `
                <div style="
                    text-align:center;
                    padding:25px 10px;
                    color:#72807c;
                    font-size:13px;
                ">
                    <i
                        class="fa-solid fa-box-open"
                        style="
                            font-size:28px;
                            margin-bottom:10px;
                            display:block;
                        "
                    ></i>
                    No medicines found for this order.
                </div>
            `;

                return;
            }


            container.innerHTML =
                items.map(item => {

                    const image =
                        item.image || '';


                    const imageHtml = image ?
                        `
                        <img
                            src="${escapeHtml(image)}"
                            alt="${escapeHtml(item.medicine_name)}"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='grid';"
                        >

                        <i
                            class="fa-solid fa-pills"
                            style="display:none;"
                        ></i>
                    ` :
                        `
                        <i class="fa-solid fa-pills"></i>
                    `;


                    return `
                    <div class="medicine-row">

                        <div class="medicine-image">
                            ${imageHtml}
                        </div>


                        <div class="medicine-info">

                            <h4>
                                ${escapeHtml(item.medicine_name || 'Medicine')}
                            </h4>

                            <span>
                                Medicine ID:
                                #${escapeHtml(item.medicine_id || '-')}
                            </span>

                        </div>


                        <div class="quantity">
                            Qty:
                            <strong>
                                ${escapeHtml(item.quantity || 0)}
                            </strong>
                        </div>


                        <div class="price">

                            ${formatMoney(item.total_price)}

                        </div>

                    </div>
                `;

                }).join('');

        }


        // =====================================================
        // Address
        // =====================================================

        function renderAddress(address) {

            const container =
                document.getElementById('addressContainer');


            const fullName =
                address.full_name || '';


            const phone =
                address.phone || '';


            const email =
                address.email || '';


            const line1 =
                address.address || '';


            const city =
                address.city || '';


            const state =
                address.state || '';


            const pincode =
                address.pincode || '';


            const landmark =
                address.landmark || '';


            container.innerHTML = `

            <div class="address-name">

                ${escapeHtml(fullName)}

            </div>


            <div>
                ${escapeHtml(line1)}
            </div>


            <div>
                ${escapeHtml(city)},
                ${escapeHtml(state)}
                -
                ${escapeHtml(pincode)}
            </div>


            ${
                landmark
                    ? `
                        <div>
                            Landmark:
                            ${escapeHtml(landmark)}
                        </div>
                    `
                    : ''
            }


            ${
                phone
                    ? `
                        <div>
                            <strong>Phone:</strong>
                            ${escapeHtml(phone)}
                        </div>
                    `
                    : ''
            }


            ${
                email
                    ? `
                        <div>
                            <strong>Email:</strong>
                            ${escapeHtml(email)}
                        </div>
                    `
                    : ''
            }

        `;

        }


        // =====================================================
        // Tracking
        // =====================================================

        function renderTracking(tracking, currentStatus) {

            const container =
                document.getElementById('timeline');


            if (!tracking.length) {

                container.innerHTML = `

                <div class="timeline-item active">

                    <div class="timeline-dot"></div>

                    <div class="timeline-title">
                        ${escapeHtml(
                            formatStatus(currentStatus || 'pending')
                        )}
                    </div>

                    <div class="timeline-description">
                        Current order status
                    </div>

                </div>

            `;

                return;
            }


            container.innerHTML =
                tracking.map(track => {

                    const isActive =
                        String(track.status).toLowerCase() ===
                        String(currentStatus).toLowerCase();


                    return `

                    <div
                        class="timeline-item ${isActive ? 'active' : ''}"
                    >

                        <div class="timeline-dot"></div>


                        <div class="timeline-title">

                            ${escapeHtml(
                                track.title ||
                                formatStatus(track.status)
                            )}

                        </div>


                        ${
                            track.description
                                ? `
                                    <div class="timeline-description">
                                        ${escapeHtml(track.description)}
                                    </div>
                                `
                                : ''
                        }


                        ${
                            track.created_at
                                ? `
                                    <div class="timeline-date">
                                        ${formatDate(track.created_at)}
                                    </div>
                                `
                                : ''
                        }

                    </div>

                `;

                }).join('');

        }


        // =====================================================
        // Admin Logout
        // =====================================================

        async function logoutAdmin() {

            try {

                await fetch(
                    '../backend/auth/logout.php', {
                        method: 'POST',
                        credentials: 'same-origin'
                    }
                );

            } catch (error) {

                console.error(error);

            }


            window.location.href = '../login.php';

        }


        // =====================================================
        // Start
        // =====================================================

        document.addEventListener(
            'DOMContentLoaded',
            loadOrder
        );
    </script>


</body>

</html>