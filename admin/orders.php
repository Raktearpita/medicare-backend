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

$adminName  = $_SESSION['admin_name'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@medicare.com';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Orders | MediCare Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

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

            --dark: #12211e;
            --text: #344440;
            --muted: #72807c;

            --white: #fff;
            --background: #f5faf8;
            --border: #dfeae6;

            --success: #16805f;
            --success-bg: #eaf8f2;

            --warning: #b87900;
            --warning-bg: #fff6df;

            --danger: #d95353;
            --danger-bg: #fff0f0;

            --blue: #426aa8;
            --blue-bg: #edf3fc;

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
            min-height: 100vh;
            font-family: "DM Sans", sans-serif;
            background: var(--background);
            color: var(--text);
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
            text-decoration: none;
            color: inherit;
        }

        /* =========================================================
   APP
========================================================= */

        .app {
            min-height: 100vh;
            display: flex;
        }

        /* =========================================================
   SIDEBAR
========================================================= */

        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;

            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1000;

            background: #102f29;
            color: #fff;

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
        }

        .brand-text strong {
            display: block;
            font-family: "Manrope", sans-serif;
            font-size: 19px;
            font-weight: 800;
        }

        .brand-text span {
            display: block;
            margin-top: 1px;

            color: rgba(255, 255, 255, .55);
            font-size: 10px;
            letter-spacing: .8px;
        }

        .sidebar-nav {
            padding: 24px 14px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-label {
            color: rgba(255, 255, 255, .38);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;

            padding: 0 12px 10px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 13px;

            padding: 12px 13px;
            margin-bottom: 5px;

            border-radius: 12px;

            color: rgba(255, 255, 255, .65);

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
            color: #fff;
            background: rgba(255, 255, 255, .12);

            box-shadow:
                inset 3px 0 0 #74d3bd;
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

            font-size: 12px;
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

            margin-top: 2px;

            color: rgba(255, 255, 255, .45);
            font-size: 10px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* =========================================================
   MAIN
========================================================= */

        .main {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-width: 0;
        }

        .topbar {
            height: 76px;

            position: sticky;
            top: 0;
            z-index: 900;

            background: rgba(255, 255, 255, .95);
            backdrop-filter: blur(15px);

            border-bottom: 1px solid var(--border);

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 clamp(18px, 3vw, 42px);
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
            border-radius: 11px;

            background: #fff;
            color: var(--dark);
        }

        .page-heading h1 {
            color: var(--dark);
            font-family: "Manrope", sans-serif;
            font-size: clamp(19px, 2vw, 25px);
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

            font-size: 12px;
            font-weight: 800;
        }

        /* =========================================================
   CONTENT
========================================================= */

        .content {
            width: 100%;
            max-width: 1900px;
            margin: auto;

            padding: clamp(20px, 3vw, 42px);
        }

        .content-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;

            margin-bottom: 24px;
        }

        .content-head h2 {
            color: var(--dark);
            font-family: "Manrope", sans-serif;
            font-size: clamp(22px, 2.4vw, 30px);
            font-weight: 800;
        }

        .content-head p {
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
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

        /* =========================================================
   SUMMARY
========================================================= */

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
            background: var(--primary-light);
            color: var(--primary);
        }

        .summary-icon.orange {
            background: var(--warning-bg);
            color: var(--warning);
        }

        .summary-icon.blue {
            background: var(--blue-bg);
            color: var(--blue);
        }

        .summary-icon.red {
            background: var(--danger-bg);
            color: var(--danger);
        }

        .summary-info span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
        }

        .summary-info strong {
            display: block;

            margin-top: 3px;

            color: var(--dark);

            font-family: "Manrope", sans-serif;
            font-size: 21px;
            font-weight: 800;
        }

        /* =========================================================
   ORDER PANEL
========================================================= */

        .panel {
            background: #fff;

            border: 1px solid var(--border);
            border-radius: var(--radius);

            box-shadow: var(--shadow);

            overflow: hidden;
        }

        /* =========================================================
   TOOLBAR
========================================================= */

        .toolbar {
            padding: 18px;

            border-bottom: 1px solid var(--border);

            display: flex;
            align-items: center;
            gap: 11px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;

            min-width: min(370px, 100%);
            flex: 1 1 300px;

            height: 44px;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;

            transform: translateY(-50%);

            color: #9ba9a5;
            font-size: 13px;

            pointer-events: none;
        }

        .search-box input {
            width: 100%;
            height: 100%;

            padding: 0 13px 0 39px;

            border: 1px solid var(--border);
            border-radius: 11px;

            outline: none;

            background: #fbfdfc;
            color: var(--dark);

            font-size: 13px;
        }

        .search-box input:focus {
            background: #fff;
            border-color: var(--primary);

            box-shadow:
                0 0 0 3px rgba(18, 107, 91, .08);
        }

        .filter-select {
            height: 44px;

            min-width: 160px;

            padding: 0 12px;

            border: 1px solid var(--border);
            border-radius: 11px;

            background: #fbfdfc;
            color: var(--text);

            outline: none;

            font-size: 12px;
            font-weight: 600;

            cursor: pointer;
        }

        .filter-select:focus {
            border-color: var(--primary);
        }

        .refresh-btn {
            width: 44px;
            height: 44px;

            border: 1px solid var(--border);
            border-radius: 11px;

            background: #fff;
            color: var(--text);

            transition: .2s ease;
        }

        .refresh-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .refresh-btn.loading i {
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* =========================================================
   TABLE
========================================================= */

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            min-width: 920px;

            border-collapse: collapse;
        }

        .orders-table th {
            padding: 14px 18px;

            background: #fbfdfc;

            border-bottom: 1px solid var(--border);

            color: #82908c;

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .8px;
            text-transform: uppercase;

            text-align: left;
            white-space: nowrap;
        }

        .orders-table td {
            padding: 15px 18px;

            border-bottom: 1px solid #edf3f0;

            font-size: 12px;
            vertical-align: middle;
        }

        .orders-table tbody tr {
            transition: .15s ease;
        }

        .orders-table tbody tr:hover {
            background: #fbfdfc;
        }

        .orders-table tbody tr:last-child td {
            border-bottom: 0;
        }

        /* =========================================================
   ORDER DETAILS
========================================================= */

        .order-id {
            color: var(--dark);

            font-family: "Manrope", sans-serif;
            font-size: 12px;
            font-weight: 800;
        }

        .order-date {
            margin-top: 4px;
            color: #97a39f;
            font-size: 10px;
        }

        .customer {
            display: flex;
            align-items: center;
            gap: 10px;

            min-width: 180px;
        }

        .customer-avatar {
            width: 38px;
            height: 38px;

            border-radius: 11px;

            background: var(--primary-light);
            color: var(--primary);

            display: grid;
            place-items: center;

            font-size: 11px;
            font-weight: 800;

            flex-shrink: 0;
        }

        .customer-info strong {
            display: block;

            color: var(--dark);

            font-size: 12px;
            font-weight: 800;
        }

        .customer-info span {
            display: block;

            margin-top: 3px;

            color: var(--muted);

            font-size: 10px;

            max-width: 180px;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .amount {
            color: var(--dark);
            font-weight: 800;
            white-space: nowrap;
        }

        .payment {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            color: var(--text);
            font-size: 11px;
            font-weight: 700;
        }

        .payment i {
            color: var(--primary);
        }

        /* =========================================================
   STATUS
========================================================= */

        .status {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            padding: 6px 10px;

            border-radius: 50px;

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

        .status.pending {
            color: var(--warning);
            background: var(--warning-bg);
        }

        .status.confirmed,
        .status.delivered {
            color: var(--success);
            background: var(--success-bg);
        }

        .status.packed,
        .status.shipped,
        .status.out_for_delivery {
            color: var(--blue);
            background: var(--blue-bg);
        }

        .status.cancelled {
            color: var(--danger);
            background: var(--danger-bg);
        }

        .status.unknown {
            color: var(--muted);
            background: #f0f3f2;
        }

        /* =========================================================
   STATUS ACTION SELECT
========================================================= */

        .status-select {
            min-width: 125px;
            height: 34px;

            padding: 0 28px 0 10px;

            border: 1px solid var(--border);
            border-radius: 9px;

            background: #fff;
            color: var(--text);

            outline: none;

            font-size: 10px;
            font-weight: 800;

            cursor: pointer;

            transition: .2s ease;
        }

        .status-select:hover {
            border-color: var(--primary);
        }

        .status-select:focus {
            border-color: var(--primary);

            box-shadow:
                0 0 0 3px rgba(18, 107, 91, .08);
        }

        .status-select.pending {
            color: var(--warning);
            background: var(--warning-bg);
        }

        .status-select.accepted {
            color: var(--success);
            background: var(--success-bg);
        }

        .status-select.processing,
        .status-select.shipped {
            color: var(--blue);
            background: var(--blue-bg);
        }

        .status-select.delivered {
            color: var(--success);
            background: var(--success-bg);
        }

        .status-select.cancelled {
            color: var(--danger);
            background: var(--danger-bg);
        }

        .status-select:disabled {
            opacity: .75;
            cursor: not-allowed;
        }

        /* =========================================================
   ACTION
========================================================= */

        .action-btn {
            width: 35px;
            height: 35px;

            border: 1px solid var(--border);
            border-radius: 9px;

            background: #fff;
            color: var(--muted);

            display: grid;
            place-items: center;

            transition: .2s ease;
        }

        .action-btn:hover {
            color: var(--primary);
            border-color: var(--primary);
            background: var(--primary-light);
        }

        /* =========================================================
   STATES
========================================================= */

        .state-box {
            display: none;

            padding: 65px 20px;

            text-align: center;
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

        .loader {
            width: 28px;
            height: 28px;

            margin: 0 auto 13px;

            border: 3px solid #dcece8;
            border-top-color: var(--primary);

            border-radius: 50%;

            animation: spin .7s linear infinite;
        }

        /* =========================================================
   TABLE FOOTER
========================================================= */

        .table-footer {
            padding: 14px 18px;

            border-top: 1px solid var(--border);

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
            color: var(--primary);
            border-color: var(--primary);
        }

        .page-btn.active {
            color: #fff;
            background: var(--primary);
            border-color: var(--primary);
        }

        .page-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        /* =========================================================
   TOAST
========================================================= */

        .toast {
            position: fixed;

            right: 22px;
            bottom: 22px;

            z-index: 3000;

            min-width: 280px;
            max-width: 430px;

            padding: 13px 15px;

            border-radius: 13px;

            background: #12211e;
            color: #fff;

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

        /* =========================================================
   OVERLAY
========================================================= */

        .overlay {
            display: none;

            position: fixed;
            inset: 0;

            z-index: 950;

            background: rgba(8, 25, 21, .45);
        }

        /* =========================================================
   RESPONSIVE
========================================================= */

        @media (max-width:1100px) {

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width:900px) {

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

        @media (max-width:650px) {

            .content {
                padding: 18px 13px 30px;
            }

            .topbar {
                padding: 0 13px;
            }

            .content-head {
                flex-direction: column;
                align-items: stretch;
            }

            .primary-btn {
                width: 100%;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
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

            .table-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .pagination {
                justify-content: center;
            }
        }

        @media (max-width:380px) {

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .page-heading p {
                display: none;
            }

            .top-avatar {
                display: none;
            }
        }

        @media (min-width:1800px) {

            :root {
                --sidebar-width: 300px;
            }

            .content {
                max-width: 2100px;
                padding-left: 55px;
                padding-right: 55px;
            }

            .orders-table th,
            .orders-table td {
                padding-left: 23px;
                padding-right: 23px;
            }
        }

        @media (min-width:2400px) {

            :root {
                --sidebar-width: 340px;
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

        <!-- =====================================================
         SIDEBAR
    ====================================================== -->

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

                <div class="nav-label">
                    Overview
                </div>

                <a href="dashboard.php" class="nav-item">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>

                <div
                    class="nav-label"
                    style="margin-top:22px;">
                    Management
                </div>

                <a href="medicines.php" class="nav-item">
                    <i class="fa-solid fa-pills"></i>
                    <span>Medicines</span>
                </a>

                <a href="orders.php" class="nav-item active">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Orders</span>
                </a>

                <a href="categories.php" class="nav-item">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Categories</span>
                </a>

                <div
                    class="nav-label"
                    style="margin-top:22px;">
                    Quick Access
                </div>

                <a href="add-medicine.php" class="nav-item">
                    <i class="fa-solid fa-circle-plus"></i>
                    <span>Add Medicine</span>
                </a>
                <a
                    href="../index.php"
                    class="nav-item"
                    target="_blank"
                    rel="noopener">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>View Store</span>
                </a>

            </nav>

            <div class="sidebar-footer">

                <div class="admin-mini">

                    <div
                        class="admin-avatar"
                        id="sidebarAvatar">
                        A
                    </div>

                    <div class="admin-mini-info">

                        <strong>
                            <?= htmlspecialchars(
                                $adminName,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </strong>

                        <span>
                            <?= htmlspecialchars(
                                $adminEmail,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </span>

                    </div>

                </div>

            </div>

        </aside>

        <div
            class="overlay"
            id="overlay"></div>

        <!-- =====================================================
         MAIN
    ====================================================== -->

        <main class="main">

            <header class="topbar">

                <div class="topbar-left">

                    <button
                        type="button"
                        class="mobile-menu"
                        id="mobileMenu"
                        aria-label="Open menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <div class="page-heading">

                        <h1>Orders</h1>

                        <p>
                            Manage customer orders
                        </p>

                    </div>

                </div>

                <div class="topbar-right">

                    <a
                        href="../index.php"
                        class="view-store"
                        target="_blank"
                        rel="noopener">
                        <i class="fa-solid fa-store"></i>
                        View Store
                    </a>

                    <div
                        class="top-avatar"
                        id="topAvatar">
                        A
                    </div>

                </div>

            </header>

            <section class="content">

                <!-- PAGE HEADER -->

                <div class="content-head">

                    <div>

                        <h2>
                            Order Management
                        </h2>

                        <p>
                            View customer orders and track their current status.
                        </p>

                    </div>

                </div>

                <!-- SUMMARY -->

                <div class="summary-grid">

                    <div class="summary-card">

                        <div class="summary-icon green">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </div>

                        <div class="summary-info">

                            <span>
                                Total Orders
                            </span>

                            <strong id="totalOrders">
                                —
                            </strong>

                        </div>

                    </div>

                    <div class="summary-card">

                        <div class="summary-icon orange">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div class="summary-info">

                            <span>
                                Pending
                            </span>

                            <strong id="pendingOrders">
                                —
                            </strong>

                        </div>

                    </div>

                    <div class="summary-card">

                        <div class="summary-icon blue">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>

                        <div class="summary-info">

                            <span>
                                Active Delivery
                            </span>

                            <strong id="activeOrders">
                                —
                            </strong>

                        </div>

                    </div>

                    <div class="summary-card">

                        <div class="summary-icon red">
                            <i class="fa-solid fa-ban"></i>
                        </div>

                        <div class="summary-info">

                            <span>
                                Cancelled
                            </span>

                            <strong id="cancelledOrders">
                                —
                            </strong>

                        </div>

                    </div>

                </div>

                <!-- ORDERS PANEL -->

                <div class="panel">

                    <!-- TOOLBAR -->

                    <div class="toolbar">

                        <div class="search-box">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                                type="search"
                                id="searchInput"
                                placeholder="Search order ID or customer..."
                                autocomplete="off">

                        </div>

                        <select
                            id="statusFilter"
                            class="filter-select">

                            <option value="">
                                All Status
                            </option>

                            <option value="Pending">
                                Pending
                            </option>

                            <option value="Accepted">
                                Accepted
                            </option>

                            <option value="Processing">
                                Processing
                            </option>

                            <option value="Shipped">
                                Shipped
                            </option>

                            <option value="Delivered">
                                Delivered
                            </option>

                            <option value="Cancelled">
                                Cancelled
                            </option>

                        </select>

                        <button
                            type="button"
                            class="refresh-btn"
                            id="refreshBtn"
                            title="Refresh orders"
                            aria-label="Refresh orders">
                            <i class="fa-solid fa-rotate"></i>
                        </button>

                    </div>

                    <!-- ERROR -->

                    <div
                        class="state-box"
                        id="errorState">

                        <div class="state-icon">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>

                        <h3>
                            Unable to load orders
                        </h3>

                        <p id="errorMessage">
                            Something went wrong.
                        </p>

                        <button
                            type="button"
                            class="primary-btn"
                            id="retryBtn"
                            style="margin:18px auto 0;">
                            <i class="fa-solid fa-rotate"></i>
                            Try Again
                        </button>

                    </div>

                    <!-- EMPTY -->

                    <div
                        class="state-box"
                        id="emptyState">

                        <div class="state-icon">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </div>

                        <h3>
                            No orders found
                        </h3>

                        <p>
                            Try changing your search or status filter.
                        </p>

                    </div>

                    <!-- TABLE -->

                    <div
                        class="table-wrap"
                        id="tableWrap"
                        style="display:none;">

                        <table class="orders-table">

                            <thead>

                                <tr>

                                    <th>
                                        Order
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Amount
                                    </th>

                                    <th>
                                        Payment
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody
                                id="ordersTableBody"></tbody>

                        </table>

                    </div>

                    <!-- FOOTER -->

                    <div
                        class="table-footer"
                        id="tableFooter"
                        style="display:none;">

                        <div
                            class="results-info"
                            id="resultsInfo">
                            Showing orders
                        </div>

                        <div
                            class="pagination"
                            id="pagination"></div>

                    </div>

                </div>

            </section>

        </main>

    </div>

    <!-- TOAST -->

    <div
        class="toast"
        id="toast">
        <i class="fa-solid fa-circle-check"></i>

        <span id="toastMessage">
            Done
        </span>
    </div>


    <script>
        "use strict";


        /* =========================================================
           CONFIG
        ========================================================= */

        const ORDERS_API =
            "../backend/orders/get-orders.php";

        const UPDATE_STATUS_API =
            "../backend/orders/update-order-status.php";

        const ORDER_DETAILS_PAGE =
            "order-details.php";


        let currentPage = 1;

        let searchTimer = null;


        /* =========================================================
           ELEMENTS
        ========================================================= */

        const elements = {

            sidebar: document.getElementById("sidebar"),

            overlay: document.getElementById("overlay"),

            mobileMenu: document.getElementById("mobileMenu"),

            searchInput: document.getElementById("searchInput"),

            statusFilter: document.getElementById("statusFilter"),

            refreshBtn: document.getElementById("refreshBtn"),

            retryBtn: document.getElementById("retryBtn"),

            errorState: document.getElementById("errorState"),

            errorMessage: document.getElementById("errorMessage"),

            emptyState: document.getElementById("emptyState"),

            tableWrap: document.getElementById("tableWrap"),

            tableBody: document.getElementById("ordersTableBody"),

            tableFooter: document.getElementById("tableFooter"),

            resultsInfo: document.getElementById("resultsInfo"),

            pagination: document.getElementById("pagination"),

            totalOrders: document.getElementById("totalOrders"),

            pendingOrders: document.getElementById("pendingOrders"),

            activeOrders: document.getElementById("activeOrders"),

            cancelledOrders: document.getElementById("cancelledOrders"),

            toast: document.getElementById("toast"),

            toastMessage: document.getElementById("toastMessage")
        };


        /* =========================================================
           HELPERS
        ========================================================= */

        function escapeHtml(value) {

            return String(value ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }


        function formatCurrency(value) {

            return new Intl.NumberFormat(
                "en-IN", {
                    style: "currency",
                    currency: "INR",
                    maximumFractionDigits: 2
                }
            ).format(
                Number(value) || 0
            );
        }


        function formatNumber(value) {

            return new Intl.NumberFormat(
                "en-IN"
            ).format(
                Number(value) || 0
            );
        }


        function getInitials(name) {

            const words =
                String(name || "Customer")
                .trim()
                .split(/\s+/)
                .filter(Boolean);

            if (!words.length) {
                return "CU";
            }

            if (words.length === 1) {

                return words[0]
                    .substring(0, 2)
                    .toUpperCase();
            }

            return (
                words[0].charAt(0) +
                words[words.length - 1].charAt(0)
            ).toUpperCase();
        }


        function normalizeStatus(status) {

            return String(status || "")
                .trim()
                .toLowerCase()
                .replace(/\s+/g, "_");
        }


        function formatStatus(status) {

            if (!status) {
                return "Unknown";
            }

            return String(status)
                .replace(/_/g, " ")
                .replace(/\b\w/g, char => char.toUpperCase());
        }


        function showToast(message) {

            elements.toastMessage.textContent =
                message;

            elements.toast.classList.add("show");

            clearTimeout(showToast.timer);

            showToast.timer =
                setTimeout(() => {

                    elements.toast.classList.remove("show");

                }, 3000);
        }


        /* =========================================================
           STATES
        ========================================================= */

        function hideStates() {

            elements.errorState.classList.remove("show");

            elements.emptyState.classList.remove("show");

            elements.tableWrap.style.display = "none";

            elements.tableFooter.style.display = "none";
        }

        function showError(message) {

            hideStates();

            elements.errorMessage.textContent =
                message ||
                "Unable to load orders.";

            elements.errorState.classList.add("show");
        }


        function showEmpty() {

            hideStates();

            elements.emptyState.classList.add("show");
        }


        /* =========================================================
           STATUS SUMMARY
        ========================================================= */

        function calculateSummary(orders) {

            let pending = 0;

            let active = 0;

            let cancelled = 0;

            orders.forEach(order => {

                const status =
                    normalizeStatus(
                        order.status
                    );

                if (status === "pending") {
                    pending++;
                }

                if (
                    [
                        "confirmed",
                        "packed",
                        "shipped",
                        "out_for_delivery"
                    ].includes(status)
                ) {
                    active++;
                }

                if (status === "cancelled") {
                    cancelled++;
                }
            });

            elements.totalOrders.textContent =
                formatNumber(orders.length);

            elements.pendingOrders.textContent =
                formatNumber(pending);

            elements.activeOrders.textContent =
                formatNumber(active);

            elements.cancelledOrders.textContent =
                formatNumber(cancelled);
        }


        /* =========================================================
           RENDER ORDERS
        ========================================================= */

        function renderOrders(orders) {

            elements.tableBody.innerHTML = "";

            orders.forEach(order => {

                const id =
                    Number(
                        order.id ??
                        order.order_id ??
                        0
                    );

                const customerName =
                    order.customer_name ??
                    order.full_name ??
                    order.user_name ??
                    "Customer";

                const customerEmail =
                    order.customer_email ??
                    order.email ??
                    "";

                const total =
                    Number(
                        order.total ??
                        order.total_amount ??
                        order.grand_total ??
                        0
                    );

                const paymentMethod =
                    order.payment_method ??
                    "COD";

                const status =
                    order.status ??
                    "Pending";

                const statusClass =
                    normalizeStatus(status);

                const orderDate =
                    order.created_at ??
                    order.order_date ??
                    order.date ??
                    "";

                const row =
                    document.createElement("tr");

                row.innerHTML = `

            <td>

                <div class="order-id">
                    #${escapeHtml(id)}
                </div>

                <div class="order-date">
                    ${escapeHtml(
                        formatDate(orderDate)
                    )}
                </div>

            </td>


            <td>

                <div class="customer">

                    <div class="customer-avatar">
                        ${escapeHtml(
                            getInitials(customerName)
                        )}
                    </div>

                    <div class="customer-info">

                        <strong>
                            ${escapeHtml(customerName)}
                        </strong>

                        <span>
                            ${escapeHtml(customerEmail)}
                        </span>

                    </div>

                </div>

            </td>


            <td>

                <span class="amount">
                    ${formatCurrency(total)}
                </span>

            </td>


            <td>

                <span class="payment">

                    <i class="fa-solid fa-money-bill-wave"></i>

                    ${escapeHtml(
                        paymentMethod
                    )}

                </span>

            </td>

<td>

    <select
        class="status-select ${escapeHtml(statusClass)}"
        data-order-id="${escapeHtml(id)}"
        data-current-status="${escapeHtml(status)}"
        onchange="updateOrderStatus(this)"
        ${status === "Delivered" || status === "Cancelled" ? "disabled" : ""}
    >

        <option value="Pending"
            ${status === "Pending" ? "selected" : ""}>
            Pending
        </option>

        <option value="Accepted"
            ${status === "Accepted" ? "selected" : ""}>
            Accepted
        </option>

        <option value="Processing"
            ${status === "Processing" ? "selected" : ""}>
            Processing
        </option>

        <option value="Shipped"
            ${status === "Shipped" ? "selected" : ""}>
            Shipped
        </option>

        <option value="Delivered"
            ${status === "Delivered" ? "selected" : ""}>
            Delivered
        </option>

        <option value="Cancelled"
            ${status === "Cancelled" ? "selected" : ""}>
            Cancelled
        </option>

    </select>

</td>


            <td>

                <button
                    type="button"
                    class="action-btn"
                    title="View order"
                    aria-label="View order"
                    onclick="viewOrder(${id})"
                >
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

            </td>
        `;

                elements.tableBody.appendChild(row);
            });
        }


        /* =========================================================
           DATE
        ========================================================= */

        function formatDate(value) {

            if (!value) {
                return "—";
            }

            const date =
                new Date(
                    String(value).replace(" ", "T")
                );

            if (
                Number.isNaN(
                    date.getTime()
                )
            ) {
                return String(value);
            }

            return new Intl.DateTimeFormat(
                "en-IN", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit"
                }
            ).format(date);
        }


        /* =========================================================
           PAGINATION
        ========================================================= */

        function renderPagination(pagination) {

            elements.pagination.innerHTML = "";

            const current =
                Number(
                    pagination?.current_page ??
                    currentPage
                ) || 1;

            const totalPages =
                Number(
                    pagination?.total_pages
                ) || 1;

            currentPage = current;

            if (totalPages <= 1) {
                return;
            }


            const previous =
                document.createElement("button");

            previous.className =
                "page-btn";

            previous.innerHTML =
                '<i class="fa-solid fa-chevron-left"></i>';

            previous.disabled =
                current <= 1;

            previous.onclick = () => {

                if (current > 1) {
                    loadOrders(current - 1);
                }
            };

            elements.pagination.appendChild(
                previous
            );


            let start =
                Math.max(
                    1,
                    current - 2
                );

            let end =
                Math.min(
                    totalPages,
                    current + 2
                );


            if (current <= 2) {

                end =
                    Math.min(
                        totalPages,
                        5
                    );
            }


            if (
                current >=
                totalPages - 1
            ) {

                start =
                    Math.max(
                        1,
                        totalPages - 4
                    );
            }


            for (
                let page = start; page <= end; page++
            ) {

                const button =
                    document.createElement("button");

                button.className =
                    "page-btn" +
                    (
                        page === current ?
                        " active" :
                        ""
                    );

                button.textContent =
                    page;

                button.onclick = () => {
                    loadOrders(page);
                };

                elements.pagination.appendChild(
                    button
                );
            }


            const next =
                document.createElement("button");

            next.className =
                "page-btn";

            next.innerHTML =
                '<i class="fa-solid fa-chevron-right"></i>';

            next.disabled =
                current >= totalPages;

            next.onclick = () => {

                if (
                    current <
                    totalPages
                ) {
                    loadOrders(current + 1);
                }
            };

            elements.pagination.appendChild(
                next
            );
        }


        /* =========================================================
           LOAD ORDERS
        ========================================================= */

        async function loadOrders(page = 1) {

            currentPage = page;

            elements.refreshBtn.classList.add("loading");

            try {

                const search =
                    elements.searchInput
                    .value
                    .trim();

                const status =
                    elements.statusFilter
                    .value;


                const params =
                    new URLSearchParams();

                params.set(
                    "page",
                    String(page)
                );

                params.set(
                    "limit",
                    "20"
                );


                if (search) {

                    /*
                     * These parameters are included
                     * for compatibility with the
                     * order API.
                     */
                    params.set(
                        "search",
                        search
                    );
                }


                if (status) {

                    params.set(
                        "status",
                        status
                    );
                }


                const response =
                    await fetch(
                        `${ORDERS_API}?${params.toString()}`, {
                            method: "GET",

                            credentials: "same-origin",

                            headers: {
                                "Accept": "application/json"
                            },

                            cache: "no-store"
                        }
                    );


                let data;

                try {

                    data =
                        await response.json();

                } catch {

                    throw new Error(
                        "Invalid response received from order server."
                    );
                }


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.message ||
                        "Unable to load orders."
                    );
                }


                let orders =
                    Array.isArray(
                        data.orders
                    ) ?
                    data.orders : [];


                /*
                 * If the backend returns all orders but
                 * doesn't perform search/status filtering,
                 * apply the filtering here as a fallback.
                 */

                if (search) {

                    const searchLower =
                        search.toLowerCase();

                    orders =
                        orders.filter(order => {

                            const orderId =
                                String(
                                    order.id ??
                                    order.order_id ??
                                    ""
                                ).toLowerCase();

                            const customer =
                                String(
                                    order.customer_name ??
                                    order.full_name ??
                                    order.user_name ??
                                    ""
                                ).toLowerCase();

                            const email =
                                String(
                                    order.customer_email ??
                                    order.email ??
                                    ""
                                ).toLowerCase();

                            return (
                                orderId.includes(searchLower) ||
                                customer.includes(searchLower) ||
                                email.includes(searchLower)
                            );
                        });
                }


                if (status) {

                    orders =
                        orders.filter(order => {

                            const currentStatus =
                                String(
                                    order.status ??
                                    ""
                                ).trim().toLowerCase();

                            return (
                                currentStatus ===
                                status.toLowerCase()
                            );
                        });
                }


                if (!orders.length) {

                    elements.totalOrders.textContent =
                        "0";

                    elements.pendingOrders.textContent =
                        "0";

                    elements.activeOrders.textContent =
                        "0";

                    elements.cancelledOrders.textContent =
                        "0";

                    showEmpty();

                    elements.resultsInfo.textContent =
                        "No orders found.";

                    return;
                }


                renderOrders(orders);

                calculateSummary(orders);


                const pagination =
                    data.pagination || {};


                const total =
                    Number(
                        pagination.total
                    ) || orders.length;


                const current =
                    Number(
                        pagination.current_page
                    ) || page;


                const limit =
                    Number(
                        pagination.limit
                    ) || orders.length;


                const start =
                    ((current - 1) * limit) + 1;


                const end =
                    Math.min(
                        current * limit,
                        total
                    );


                elements.resultsInfo.textContent =
                    `Showing ${formatNumber(start)}–${formatNumber(end)} of ${formatNumber(total)} orders`;


                renderPagination(
                    pagination
                );


                elements.tableWrap.style.display =
                    "block";

                elements.tableFooter.style.display =
                    "flex";


            } catch (error) {

                console.error(
                    "Order loading error:",
                    error
                );

                showError(
                    error.message ||
                    "Unable to load orders."
                );

            } finally {

                elements.refreshBtn.classList.remove(
                    "loading"
                );
            }
        }
        /* =========================================================
           UPDATE ORDER STATUS
        ========================================================= */

        window.updateOrderStatus = async function(select) {

            const orderId = Number(select.dataset.orderId);
            const oldStatus = String(
                select.dataset.currentStatus || "Pending"
            ).trim();

            const newStatus = String(select.value || "").trim();

            if (!orderId || !newStatus) {
                return;
            }

            if (oldStatus.toLowerCase() === newStatus.toLowerCase()) {
                return;
            }

            const confirmed = window.confirm(
                `Change Order #${orderId} status from "${oldStatus}" to "${newStatus}"?`
            );

            if (!confirmed) {
                select.value = oldStatus;
                return;
            }

            select.disabled = true;

            try {

                const response = await fetch(
                    UPDATE_STATUS_API, {
                        method: "POST",
                        credentials: "same-origin",

                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },

                        body: JSON.stringify({
                            order_id: orderId,
                            status: newStatus
                        })
                    }
                );

                const text = await response.text();

                let data;

                try {
                    data = JSON.parse(text);
                } catch (e) {

                    console.error("Server response:", text);

                    throw new Error(
                        "Server returned an invalid response."
                    );
                }

                if (!response.ok || !data.success) {

                    throw new Error(
                        data.message ||
                        "Unable to update order status."
                    );
                }

                // Update current status
                select.dataset.currentStatus = newStatus;

                // Update dropdown color
                select.className =
                    "status-select " +
                    normalizeStatus(newStatus);

                showToast(
                    `Order #${orderId} changed to ${newStatus}.`
                );

                // Reload after successful update
                setTimeout(() => {
                    loadOrders(currentPage);
                }, 500);

            } catch (error) {

                console.error(
                    "Status update error:",
                    error
                );

                select.value = oldStatus;

                showToast(
                    error.message ||
                    "Unable to update order status."
                );

                select.disabled = false;
            }
        };
        /* =========================================================
           VIEW ORDER
        ========================================================= */

        window.viewOrder = function(id) {

            if (!id) {
                return;
            }

            window.location.href =
                `${ORDER_DETAILS_PAGE}?id=${encodeURIComponent(id)}`;
        };


        /* =========================================================
           SEARCH
        ========================================================= */

        elements.searchInput.addEventListener(
            "input",
            () => {

                clearTimeout(
                    searchTimer
                );

                searchTimer =
                    setTimeout(
                        () => {
                            loadOrders(1);
                        },
                        350
                    );
            }
        );


        /* =========================================================
           STATUS FILTER
        ========================================================= */

        elements.statusFilter.addEventListener(
            "change",
            () => {
                loadOrders(1);
            }
        );


        /* =========================================================
           REFRESH
        ========================================================= */

        elements.refreshBtn.addEventListener(
            "click",
            () => {
                loadOrders(currentPage);
            }
        );


        elements.retryBtn.addEventListener(
            "click",
            () => {
                loadOrders(currentPage);
            }
        );


        /* =========================================================
           MOBILE SIDEBAR
        ========================================================= */

        function openSidebar() {

            elements.sidebar.classList.add(
                "open"
            );

            elements.overlay.classList.add(
                "show"
            );

            document.body.style.overflow =
                "hidden";
        }


        function closeSidebar() {

            elements.sidebar.classList.remove(
                "open"
            );

            elements.overlay.classList.remove(
                "show"
            );

            document.body.style.overflow =
                "";
        }


        elements.mobileMenu.addEventListener(
            "click",
            openSidebar
        );


        elements.overlay.addEventListener(
            "click",
            closeSidebar
        );


        document.querySelectorAll(
            ".nav-item"
        ).forEach(item => {

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
        });


        window.addEventListener(
            "resize",
            () => {

                if (
                    window.innerWidth > 900
                ) {
                    closeSidebar();
                }
            }
        );


        /* =========================================================
           ADMIN AVATAR
        ========================================================= */

        const adminName =
            <?= json_encode(
                $adminName,
                JSON_UNESCAPED_UNICODE
            ); ?>;


        const adminInitials =
            getInitials(adminName);


        document.getElementById(
                "sidebarAvatar"
            ).textContent =
            adminInitials;


        document.getElementById(
                "topAvatar"
            ).textContent =
            adminInitials;


        /* =========================================================
           INITIAL LOAD
        ========================================================= */

        loadOrders(1);
    </script>

</body>

</html>