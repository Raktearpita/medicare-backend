<?php

/**
 * MediCare Pharmacy
 * Admin Dashboard
 *
 * File:
 * admin/dashboard.php
 */

declare(strict_types=1);

session_start();


// =========================================================
// ADMIN AUTHENTICATION
// =========================================================

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    header('Location: ../login.php');
    exit;
}


$adminId = (int)$_SESSION['admin_id'];

$adminName = trim(
    (string)($_SESSION['admin_name'] ?? 'Administrator')
);

$adminEmail = trim(
    (string)($_SESSION['admin_email'] ?? '')
);

$initial = strtoupper(
    substr(
        $adminName !== '' ? $adminName : 'A',
        0,
        1
    )
);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="MediCare Pharmacy Admin Dashboard">

    <title>Admin Dashboard | MediCare Pharmacy</title>


    <!-- ===================================================
         FONT AWESOME
    ==================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


    <!-- ===================================================
         GOOGLE FONTS
    ==================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap"
        rel="stylesheet">


    <style>
        /* =================================================
           ROOT
        ================================================= */

        :root {

            --primary: #126b5b;
            --primary-dark: #0b5145;
            --primary-light: #e8f6f2;

            --secondary: #f4a261;

            --dark: #12211e;
            --text: #344440;
            --muted: #72807c;

            --white: #ffffff;

            --background: #f6faf8;
            --soft: #f0f7f4;

            --border: #dfeae6;

            --danger: #d95353;
            --success: #16805f;
            --warning: #c17b16;

            --sidebar-width: 270px;

            --shadow:
                0 12px 35px rgba(18, 107, 91, 0.08);

            --shadow-large:
                0 20px 60px rgba(18, 107, 91, 0.12);
        }


        /* =================================================
           RESET
        ================================================= */

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

            background: var(--background);

            color: var(--text);

            font-family:
                "DM Sans",
                Arial,
                sans-serif;

            overflow-x: hidden;
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


        /* =================================================
           LAYOUT
        ================================================= */

        .admin-layout {

            min-height: 100vh;

            display: flex;
        }


        /* =================================================
           SIDEBAR
        ================================================= */

        .sidebar {

            position: fixed;

            top: 0;
            left: 0;
            bottom: 0;

            width: var(--sidebar-width);

            background: var(--white);

            border-right:
                1px solid var(--border);

            display: flex;

            flex-direction: column;

            z-index: 1000;

            transition:
                transform 0.3s ease;
        }


        .sidebar-brand {

            height: 82px;

            padding:
                0 24px;

            display: flex;

            align-items: center;

            gap: 12px;

            border-bottom:
                1px solid var(--border);
        }


        .brand-icon {

            width: 42px;
            height: 42px;

            border-radius: 12px;

            display: flex;

            align-items: center;
            justify-content: center;

            background:
                var(--primary);

            color:
                var(--white);

            font-size: 19px;

            box-shadow:
                0 8px 18px rgba(18, 107, 91, 0.18);
        }


        .brand-text {

            font-family:
                "Manrope",
                sans-serif;

            font-size: 20px;

            font-weight: 800;

            color: var(--dark);
        }


        .brand-text span {

            color:
                var(--primary);
        }


        .admin-label {

            margin-left: auto;

            padding:
                4px 8px;

            border-radius: 6px;

            background:
                var(--primary-light);

            color:
                var(--primary);

            font-size: 10px;

            font-weight: 800;

            text-transform:
                uppercase;

            letter-spacing:
                0.5px;
        }


        .sidebar-content {

            flex: 1;

            overflow-y: auto;

            padding:
                22px 14px;
        }


        .nav-title {

            padding:
                0 12px 10px;

            color:
                var(--muted);

            font-size: 10px;

            font-weight: 700;

            letter-spacing:
                1.1px;

            text-transform:
                uppercase;
        }


        .sidebar-nav {

            display: flex;

            flex-direction: column;

            gap: 5px;
        }


        .nav-link {

            min-height: 46px;

            padding:
                0 14px;

            display: flex;

            align-items: center;

            gap: 12px;

            border-radius: 10px;

            color:
                var(--text);

            font-size: 14px;

            font-weight: 600;

            transition:
                0.2s ease;
        }


        .nav-link i {

            width: 20px;

            text-align: center;

            color:
                var(--muted);

            font-size: 15px;
        }


        .nav-link:hover {

            background:
                var(--soft);

            color:
                var(--primary);
        }


        .nav-link:hover i {

            color:
                var(--primary);
        }


        .nav-link.active {

            background:
                var(--primary-light);

            color:
                var(--primary);
        }


        .nav-link.active i {

            color:
                var(--primary);
        }


        .nav-badge {

            margin-left: auto;

            min-width: 22px;

            height: 22px;

            padding: 0 6px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            border-radius: 20px;

            background:
                var(--primary);

            color:
                var(--white);

            font-size: 10px;

            font-weight: 800;
        }


        .sidebar-bottom {

            padding:
                15px 14px;

            border-top:
                1px solid var(--border);
        }


        .admin-mini {

            padding:
                12px;

            display: flex;

            align-items: center;

            gap: 10px;

            border-radius: 12px;

            background:
                var(--soft);
        }


        .admin-avatar {

            flex-shrink: 0;

            width: 38px;
            height: 38px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                var(--primary);

            color:
                var(--white);

            font-weight: 800;

            font-size: 14px;
        }


        .admin-mini-info {

            min-width: 0;

            flex: 1;
        }


        .admin-mini-name {

            display: block;

            color:
                var(--dark);

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .admin-mini-role {

            display: block;

            margin-top: 2px;

            color:
                var(--muted);

            font-size: 10px;
        }


        /* =================================================
           MAIN
        ================================================= */

        .main {

            width: calc(100% - var(--sidebar-width));

            margin-left:
                var(--sidebar-width);

            min-height: 100vh;
        }


        /* =================================================
           TOPBAR
        ================================================= */

        .topbar {

            position: sticky;

            top: 0;

            height: 82px;

            padding:
                0 30px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            background:
                rgba(255, 255, 255, 0.94);

            border-bottom:
                1px solid var(--border);

            backdrop-filter:
                blur(14px);

            z-index: 900;
        }


        .topbar-left {

            display: flex;

            align-items: center;

            gap: 15px;
        }


        .mobile-menu-btn {

            display: none;

            width: 42px;
            height: 42px;

            border: 1px solid var(--border);

            border-radius: 10px;

            background:
                var(--white);

            color:
                var(--dark);

            font-size: 17px;
        }


        .page-heading h1 {

            font-family:
                "Manrope",
                sans-serif;

            color:
                var(--dark);

            font-size: 22px;

            font-weight: 800;
        }


        .page-heading p {

            margin-top: 3px;

            color:
                var(--muted);

            font-size: 12px;
        }


        .topbar-right {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .topbar-icon {

            position: relative;

            width: 42px;
            height: 42px;

            border:
                1px solid var(--border);

            border-radius: 10px;

            background:
                var(--white);

            color:
                var(--text);

            display: flex;

            align-items: center;
            justify-content: center;

            transition:
                0.2s ease;
        }


        .topbar-icon:hover {

            border-color:
                var(--primary);

            color:
                var(--primary);

            background:
                var(--primary-light);
        }


        .notification-dot {

            position: absolute;

            top: 8px;
            right: 8px;

            width: 7px;
            height: 7px;

            border-radius: 50%;

            background:
                var(--secondary);

            border:
                2px solid var(--white);
        }


        .top-admin {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-left: 5px;

            padding-left: 14px;

            border-left:
                1px solid var(--border);
        }


        .top-admin-avatar {

            width: 40px;
            height: 40px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                var(--primary-light);

            color:
                var(--primary);

            font-size: 14px;

            font-weight: 800;
        }


        .top-admin-info strong {

            display: block;

            color:
                var(--dark);

            font-size: 12px;
        }


        .top-admin-info span {

            display: block;

            margin-top: 2px;

            color:
                var(--muted);

            font-size: 10px;
        }


        /* =================================================
           CONTENT
        ================================================= */

        .content {

            width: min(100%,
                    1900px);

            margin: 0 auto;

            padding:
                30px;
        }


        /* =================================================
           WELCOME
        ================================================= */

        .welcome-card {

            position: relative;

            overflow: hidden;

            padding:
                28px 30px;

            margin-bottom: 24px;

            border-radius: 18px;

            background:
                linear-gradient(135deg,
                    #126b5b,
                    #0b5145);

            color:
                var(--white);

            box-shadow:
                var(--shadow-large);
        }


        .welcome-card::before {

            content: "";

            position: absolute;

            width: 230px;
            height: 230px;

            top: -120px;
            right: 10%;

            border-radius: 50%;

            border:
                1px solid rgba(255, 255, 255, 0.13);
        }


        .welcome-card::after {

            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            right: -80px;
            bottom: -100px;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, 0.06);
        }


        .welcome-content {

            position: relative;

            z-index: 2;
        }


        .welcome-content h2 {

            font-family:
                "Manrope",
                sans-serif;

            font-size: 25px;

            font-weight: 800;
        }


        .welcome-content p {

            max-width: 650px;

            margin-top: 8px;

            color:
                rgba(255, 255, 255, 0.82);

            font-size: 13px;

            line-height: 1.6;
        }


        .welcome-date {

            margin-top: 17px;

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding:
                7px 11px;

            border-radius: 8px;

            background:
                rgba(255, 255, 255, 0.1);

            color:
                rgba(255, 255, 255, 0.9);

            font-size: 11px;

            font-weight: 600;
        }


        /* =================================================
           STAT CARDS
        ================================================= */

        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 24px;
        }


        .stat-card {

            min-width: 0;

            padding: 21px;

            background:
                var(--white);

            border:
                1px solid var(--border);

            border-radius: 15px;

            box-shadow:
                var(--shadow);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .stat-card:hover {

            transform:
                translateY(-3px);

            box-shadow:
                var(--shadow-large);
        }


        .stat-top {

            display: flex;

            align-items: flex-start;

            justify-content: space-between;
        }


        .stat-icon {

            width: 46px;
            height: 46px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background:
                var(--primary-light);

            color:
                var(--primary);

            font-size: 17px;
        }


        .stat-icon.orange {

            background:
                #fff3e8;

            color:
                #d9822b;
        }


        .stat-icon.blue {

            background:
                #edf4ff;

            color:
                #4478bd;
        }


        .stat-icon.red {

            background:
                #fff0f0;

            color:
                var(--danger);
        }


        .stat-change {

            padding:
                4px 7px;

            border-radius: 6px;

            background:
                #eaf8f2;

            color:
                var(--success);

            font-size: 9px;

            font-weight: 800;
        }


        .stat-label {

            margin-top: 17px;

            color:
                var(--muted);

            font-size: 12px;

            font-weight: 600;
        }


        .stat-value {

            margin-top: 4px;

            color:
                var(--dark);

            font-family:
                "Manrope",
                sans-serif;

            font-size: 25px;

            font-weight: 800;
        }


        .stat-footer {

            margin-top: 7px;

            color:
                var(--muted);

            font-size: 10px;
        }


        /* =================================================
           TWO COLUMN
        ================================================= */

        .dashboard-grid {

            display: grid;

            grid-template-columns:
                minmax(0, 1.7fr) minmax(280px, 1fr);

            gap: 20px;

            margin-bottom: 24px;
        }


        .panel {

            min-width: 0;

            background:
                var(--white);

            border:
                1px solid var(--border);

            border-radius: 15px;

            box-shadow:
                var(--shadow);

            overflow: hidden;
        }


        .panel-header {

            min-height: 68px;

            padding:
                15px 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            border-bottom:
                1px solid var(--border);
        }


        .panel-title h3 {

            color:
                var(--dark);

            font-family:
                "Manrope",
                sans-serif;

            font-size: 15px;

            font-weight: 800;
        }


        .panel-title p {

            margin-top: 3px;

            color:
                var(--muted);

            font-size: 10px;
        }


        .panel-action {

            color:
                var(--primary);

            font-size: 11px;

            font-weight: 700;
        }


        .panel-action:hover {

            text-decoration:
                underline;
        }


        /* =================================================
           QUICK ACTIONS
        ================================================= */

        .quick-actions {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 10px;

            padding: 20px;
        }


        .quick-action {

            min-height: 92px;

            padding: 15px;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

            border:
                1px solid var(--border);

            border-radius: 11px;

            background:
                var(--white);

            transition:
                0.2s ease;
        }


        .quick-action:hover {

            border-color:
                var(--primary);

            background:
                var(--primary-light);

            transform:
                translateY(-2px);
        }


        .quick-action-icon {

            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 8px;

            background:
                var(--soft);

            color:
                var(--primary);

            font-size: 12px;
        }


        .quick-action strong {

            margin-top: 9px;

            color:
                var(--dark);

            font-size: 11px;
        }


        .quick-action span {

            margin-top: 2px;

            color:
                var(--muted);

            font-size: 9px;
        }


        /* =================================================
           ORDERS
        ================================================= */

        .orders-list {

            padding:
                5px 20px 15px;
        }


        .order-row {

            min-height: 65px;

            display: grid;

            grid-template-columns:
                1.3fr 1fr 0.8fr 0.7fr;

            align-items: center;

            gap: 12px;

            border-bottom:
                1px solid var(--border);
        }


        .order-row:last-child {

            border-bottom: none;
        }


        .order-number {

            color:
                var(--primary);

            font-size: 11px;

            font-weight: 800;
        }


        .customer-name {

            margin-top: 3px;

            color:
                var(--dark);

            font-size: 11px;

            font-weight: 700;
        }


        .order-date {

            color:
                var(--muted);

            font-size: 10px;
        }


        .order-amount {

            color:
                var(--dark);

            font-size: 11px;

            font-weight: 800;
        }


        .status-badge {

            width: fit-content;

            padding:
                5px 8px;

            border-radius: 20px;

            background:
                var(--primary-light);

            color:
                var(--primary);

            font-size: 9px;

            font-weight: 800;

            white-space: nowrap;
        }


        .status-badge.pending {

            background:
                #fff6e7;

            color:
                #a66a00;
        }


        .status-badge.shipped {

            background:
                #edf4ff;

            color:
                #4478bd;
        }


        .status-badge.delivered {

            background:
                #eaf8f2;

            color:
                var(--success);
        }


        .status-badge.cancelled {

            background:
                #fff0f0;

            color:
                var(--danger);
        }


        .empty-orders {

            padding:
                35px 20px;

            text-align: center;

            color:
                var(--muted);

            font-size: 12px;
        }


        .empty-orders i {

            margin-bottom: 10px;

            font-size: 25px;

            color:
                var(--border);
        }


        /* =================================================
           INVENTORY
        ================================================= */

        .inventory-list {

            padding:
                10px 20px 20px;
        }


        .inventory-item {

            padding:
                13px 0;

            border-bottom:
                1px solid var(--border);
        }


        .inventory-item:last-child {

            border-bottom: none;
        }


        .inventory-top {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;
        }


        .medicine-name {

            color:
                var(--dark);

            font-size: 11px;

            font-weight: 700;
        }


        .stock-count {

            color:
                var(--muted);

            font-size: 10px;

            font-weight: 600;
        }


        .stock-bar {

            height: 6px;

            margin-top: 8px;

            overflow: hidden;

            border-radius: 10px;

            background:
                var(--soft);
        }


        .stock-progress {

            height: 100%;

            border-radius: inherit;

            background:
                var(--primary);

            width: 50%;
        }


        .stock-progress.low {

            background:
                var(--secondary);
        }


        .stock-progress.out {

            background:
                var(--danger);
        }


        /* =================================================
           BOTTOM GRID
        ================================================= */

        .bottom-grid {

            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 18px;
        }


        .info-card {

            padding: 22px;

            background:
                var(--white);

            border:
                1px solid var(--border);

            border-radius: 15px;

            box-shadow:
                var(--shadow);
        }


        .info-card-icon {

            width: 42px;
            height: 42px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 11px;

            background:
                var(--primary-light);

            color:
                var(--primary);
        }


        .info-card h3 {

            margin-top: 15px;

            color:
                var(--dark);

            font-family:
                "Manrope",
                sans-serif;

            font-size: 14px;

            font-weight: 800;
        }


        .info-card p {

            margin-top: 5px;

            color:
                var(--muted);

            font-size: 11px;

            line-height: 1.6;
        }


        .info-card a {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            margin-top: 14px;

            color:
                var(--primary);

            font-size: 10px;

            font-weight: 800;
        }


        /* =================================================
           OVERLAY
        ================================================= */

        .sidebar-overlay {

            display: none;

            position: fixed;

            inset: 0;

            background:
                rgba(18, 33, 30, 0.4);

            z-index: 950;
        }


        /* =================================================
           TOAST
        ================================================= */

        .toast {

            position: fixed;

            right: 24px;

            bottom: 24px;

            max-width: 360px;

            padding:
                13px 16px;

            display: flex;

            align-items: center;

            gap: 10px;

            border-radius: 11px;

            background:
                var(--dark);

            color:
                var(--white);

            box-shadow:
                var(--shadow-large);

            font-size: 12px;

            transform:
                translateY(120px);

            opacity: 0;

            pointer-events: none;

            transition:
                0.3s ease;

            z-index: 2000;
        }


        .toast.show {

            transform:
                translateY(0);

            opacity: 1;
        }


        .toast i {

            color:
                #65d5b3;
        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (min-width: 1800px) {

            :root {
                --sidebar-width: 300px;
            }

            .content {
                padding: 38px 42px;
            }

            .stat-card {
                padding: 25px;
            }

            .stat-value {
                font-size: 29px;
            }
        }


        @media (min-width: 2300px) {

            :root {
                --sidebar-width: 330px;
            }

            .content {
                max-width: 2200px;
            }

            .topbar {
                padding-left: 42px;
                padding-right: 42px;
            }
        }


        @media (max-width: 1150px) {

            .stats-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .dashboard-grid {
                grid-template-columns:
                    1fr;
            }

            .bottom-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }


        @media (max-width: 900px) {

            :root {
                --sidebar-width: 270px;
            }

            .sidebar {
                transform:
                    translateX(-100%);
            }

            .sidebar.open {
                transform:
                    translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            .main {
                width: 100%;
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .topbar {
                padding: 0 20px;
            }

            .content {
                padding: 22px 20px;
            }
        }


        @media (max-width: 700px) {

            .top-admin-info {
                display: none;
            }

            .top-admin {
                padding-left: 8px;
            }

            .order-row {
                grid-template-columns:
                    1fr auto;

                padding:
                    12px 0;
            }

            .order-row>div:nth-child(2) {
                display: none;
            }

            .order-row>div:nth-child(3) {
                text-align: right;
            }

            .bottom-grid {
                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 550px) {

            .topbar {
                height: 70px;
            }

            .page-heading h1 {
                font-size: 18px;
            }

            .page-heading p {
                display: none;
            }

            .topbar-icon {
                width: 38px;
                height: 38px;
            }

            .top-admin-avatar {
                width: 36px;
                height: 36px;
            }

            .content {
                padding:
                    16px 14px 25px;
            }

            .welcome-card {
                padding:
                    22px 20px;
            }

            .welcome-content h2 {
                font-size: 21px;
            }

            .stats-grid {
                grid-template-columns:
                    1fr 1fr;

                gap: 10px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-icon {
                width: 39px;
                height: 39px;
                font-size: 14px;
            }

            .stat-value {
                font-size: 21px;
            }

            .quick-actions {
                grid-template-columns:
                    1fr 1fr;

                padding: 14px;
            }

            .panel-header {
                padding:
                    14px 15px;
            }

            .orders-list {
                padding:
                    5px 15px 15px;
            }

            .inventory-list {
                padding:
                    8px 15px 15px;
            }

            .toast {
                left: 14px;
                right: 14px;
                bottom: 14px;
                max-width: none;
            }
        }


        @media (max-width: 380px) {

            .stats-grid {
                grid-template-columns:
                    1fr;
            }

            .quick-actions {
                grid-template-columns:
                    1fr;
            }

            .welcome-content h2 {
                font-size: 19px;
            }

            .stat-value {
                font-size: 23px;
            }
        }
    </style>

</head>


<body>


    <div class="admin-layout">


        <!-- =====================================================
         SIDEBAR
    ====================================================== -->

        <aside
            class="sidebar"
            id="sidebar">

            <div class="sidebar-brand">

                <div class="brand-icon">
                    <i class="fa-solid fa-plus"></i>
                </div>

                <div class="brand-text">
                    Medi<span>Care</span>
                </div>

                <span class="admin-label">
                    Admin
                </span>

            </div>


            <div class="sidebar-content">

                <div class="nav-title">
                    Main Menu
                </div>


                <nav class="sidebar-nav">

                    <a
                        href="dashboard.php"
                        class="nav-link active">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>


                    <a
                        href="medicines.php"
                        class="nav-link">
                        <i class="fa-solid fa-pills"></i>
                        <span>Medicines</span>
                    </a>


                    <a
                        href="orders.php"
                        class="nav-link">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>Orders</span>
                    </a>
                </nav>


                <div
                    class="nav-title"
                    style="margin-top:28px;">
                    Management
                </div>


                <nav class="sidebar-nav">

                    <a
                        href="add-medicine.php"
                        class="nav-link">
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Add Medicine</span>
                    </a>


                    <a
                        href="categories.php"
                        class="nav-link">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>Categories</span>
                    </a>
                </nav>


                <div
                    class="nav-title"
                    style="margin-top:28px;">
                    System
                </div>


                <nav class="sidebar-nav">
                    <button
                        type="button"
                        class="nav-link"
                        id="logoutButton"
                        style="
                        border:0;
                        background:transparent;
                        width:100%;
                        text-align:left;
                    ">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>

                </nav>

            </div>


            <div class="sidebar-bottom">

                <div class="admin-mini">

                    <div class="admin-avatar">
                        <?= htmlspecialchars(
                            $initial,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </div>

                    <div class="admin-mini-info">

                        <span class="admin-mini-name">
                            <?= htmlspecialchars(
                                $adminName,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>

                        <span class="admin-mini-role">
                            Administrator
                        </span>

                    </div>

                </div>

            </div>

        </aside>


        <!-- =====================================================
         SIDEBAR OVERLAY
    ====================================================== -->

        <div
            class="sidebar-overlay"
            id="sidebarOverlay"></div>


        <!-- =====================================================
         MAIN
    ====================================================== -->

        <main class="main">


            <!-- =================================================
             TOPBAR
        ================================================== -->

            <header class="topbar">

                <div class="topbar-left">

                    <button
                        type="button"
                        class="mobile-menu-btn"
                        id="mobileMenuButton"
                        aria-label="Open menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>


                    <div class="page-heading">

                        <h1>
                            Dashboard
                        </h1>

                        <p>
                            Pharmacy management overview
                        </p>

                    </div>

                </div>


                <div class="topbar-right">

                    <a
                        href="../index.php"
                        target="_blank"
                        class="topbar-icon"
                        title="View Store">
                        <i class="fa-solid fa-store"></i>
                    </a>


                    <button
                        type="button"
                        class="topbar-icon"
                        id="notificationButton"
                        title="Notifications">
                        <i class="fa-regular fa-bell"></i>

                        <span class="notification-dot"></span>
                    </button>


                    <div class="top-admin">

                        <div class="top-admin-avatar">
                            <?= htmlspecialchars(
                                $initial,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>

                        <div class="top-admin-info">

                            <strong>
                                <?= htmlspecialchars(
                                    $adminName,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                            <span>
                                Administrator
                            </span>

                        </div>

                    </div>

                </div>

            </header>


            <!-- =================================================
             CONTENT
        ================================================== -->

            <section class="content">


                <!-- =============================================
                 WELCOME
            ============================================== -->

                <div class="welcome-card">

                    <div class="welcome-content">

                        <h2>
                            Welcome back,
                            <?= htmlspecialchars(
                                $adminName,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>! 👋
                        </h2>

                        <p>
                            Manage your pharmacy, medicines,
                            customers and orders from one place.
                            Here's your store overview.
                        </p>

                        <div class="welcome-date">

                            <i class="fa-regular fa-calendar"></i>

                            <span id="currentDate">
                                Loading date...
                            </span>

                        </div>

                    </div>

                </div>


                <!-- =============================================
                 STATISTICS
            ============================================== -->

                <div class="stats-grid">


                    <div class="stat-card">

                        <div class="stat-top">

                            <div class="stat-icon">

                                <i class="fa-solid fa-bag-shopping"></i>

                            </div>

                            <span class="stat-change">
                                Today
                            </span>

                        </div>

                        <div class="stat-label">
                            Total Orders
                        </div>

                        <div
                            class="stat-value"
                            id="totalOrders">
                            —
                        </div>

                        <div class="stat-footer">
                            Orders received
                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-top">

                            <div class="stat-icon orange">

                                <i class="fa-solid fa-indian-rupee-sign"></i>

                            </div>

                            <span class="stat-change">
                                Revenue
                            </span>

                        </div>

                        <div class="stat-label">
                            Total Revenue
                        </div>

                        <div
                            class="stat-value"
                            id="totalRevenue">
                            —
                        </div>

                        <div class="stat-footer">
                            Completed store sales
                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-top">

                            <div class="stat-icon blue">

                                <i class="fa-solid fa-pills"></i>

                            </div>

                            <span class="stat-change">
                                Inventory
                            </span>

                        </div>

                        <div class="stat-label">
                            Medicines
                        </div>

                        <div
                            class="stat-value"
                            id="totalMedicines">
                            —
                        </div>

                        <div class="stat-footer">
                            Products in catalogue
                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-top">

                            <div class="stat-icon red">

                                <i class="fa-solid fa-users"></i>

                            </div>

                            <span class="stat-change">
                                Customers
                            </span>

                        </div>

                        <div class="stat-label">
                            Registered Users
                        </div>

                        <div
                            class="stat-value"
                            id="totalUsers">
                            —
                        </div>

                        <div class="stat-footer">
                            Active customer accounts
                        </div>

                    </div>

                </div>


                <!-- =============================================
                 DASHBOARD GRID
            ============================================== -->

                <div class="dashboard-grid">


                    <!-- =========================================
                     RECENT ORDERS
                ========================================== -->

                    <div class="panel">

                        <div class="panel-header">

                            <div class="panel-title">

                                <h3>
                                    Recent Orders
                                </h3>

                                <p>
                                    Latest customer orders
                                </p>

                            </div>

                            <a
                                href="orders.php"
                                class="panel-action">
                                View All
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                        </div>


                        <div
                            class="orders-list"
                            id="recentOrders">

                            <div class="empty-orders">

                                <i class="fa-solid fa-spinner fa-spin"></i>

                                <div>
                                    Loading orders...
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- =========================================
                     QUICK ACTIONS
                ========================================== -->

                    <div class="panel">

                        <div class="panel-header">

                            <div class="panel-title">

                                <h3>
                                    Quick Actions
                                </h3>

                                <p>
                                    Common admin tasks
                                </p>

                            </div>

                        </div>


                        <div class="quick-actions">


                            <a
                                href="add-medicine.php"
                                class="quick-action">

                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-plus"></i>
                                </div>

                                <div>
                                    <strong>
                                        Add Medicine
                                    </strong>

                                    <span>
                                        Add new product
                                    </span>
                                </div>

                            </a>


                            <a
                                href="medicines.php"
                                class="quick-action">

                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>

                                <div>
                                    <strong>
                                        Manage Stock
                                    </strong>

                                    <span>
                                        Check inventory
                                    </span>
                                </div>

                            </a>


                            <a
                                href="orders.php"
                                class="quick-action">

                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-truck-fast"></i>
                                </div>

                                <div>
                                    <strong>
                                        Manage Orders
                                    </strong>

                                    <span>
                                        Update deliveries
                                    </span>
                                </div>

                            </a>

                            <a
                                href="categories.php"
                                class="quick-action">

                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>

                                <div>
                                    <strong>
                                        Categories
                                    </strong>

                                    <span>
                                        Manage categories
                                    </span>
                                </div>

                </a>

                        </div>

                    </div>

                </div>


                <!-- =============================================
                 INVENTORY + SYSTEM
            ============================================== -->

                <div class="dashboard-grid">


                    <!-- =========================================
                     LOW STOCK
                ========================================== -->

                    <div class="panel">

                        <div class="panel-header">

                            <div class="panel-title">

                                <h3>
                                    Inventory Overview
                                </h3>

                                <p>
                                    Medicines requiring attention
                                </p>

                            </div>

                            <a
                                href="medicines.php"
                                class="panel-action">
                                Manage
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                        </div>


                        <div
                            class="inventory-list"
                            id="inventoryList">

                            <div class="empty-orders">

                                <i class="fa-solid fa-spinner fa-spin"></i>

                                <div>
                                    Loading inventory...
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- =========================================
                     ADMIN ACCOUNT
                ========================================== -->

                    <div class="panel">

                        <div class="panel-header">

                            <div class="panel-title">

                                <h3>
                                    Admin Account
                                </h3>

                                <p>
                                    Current administrator
                                </p>

                            </div>

                            <a
                                href="profile.php"
                                class="panel-action">
                                Profile
                            </a>

                        </div>


                        <div style="padding:20px;">

                            <div
                                style="
                                display:flex;
                                align-items:center;
                                gap:14px;
                                padding:15px;
                                background:var(--soft);
                                border-radius:12px;
                            ">

                                <div
                                    style="
                                    width:52px;
                                    height:52px;
                                    flex-shrink:0;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    border-radius:50%;
                                    background:var(--primary);
                                    color:#fff;
                                    font-size:18px;
                                    font-weight:800;
                                ">
                                    <?= htmlspecialchars(
                                        $initial,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>


                                <div style="min-width:0;">

                                    <strong
                                        style="
                                        display:block;
                                        color:var(--dark);
                                        font-size:13px;
                                    ">
                                        <?= htmlspecialchars(
                                            $adminName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>

                                    <span
                                        style="
                                        display:block;
                                        margin-top:4px;
                                        color:var(--muted);
                                        font-size:10px;
                                        word-break:break-word;
                                    ">
                                        <?= htmlspecialchars(
                                            $adminEmail,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>

                                </div>

                            </div>


                            <div
                                style="
                                margin-top:15px;
                                padding:12px 14px;
                                border:1px solid var(--border);
                                border-radius:10px;
                                display:flex;
                                justify-content:space-between;
                                gap:10px;
                            ">

                                <span
                                    style="
                                    color:var(--muted);
                                    font-size:10px;
                                ">
                                    Account ID
                                </span>

                                <strong
                                    style="
                                    color:var(--dark);
                                    font-size:10px;
                                ">
                                    #<?= $adminId ?>
                                </strong>

                            </div>


                            <div
                                style="
                                margin-top:10px;
                                padding:12px 14px;
                                border:1px solid var(--border);
                                border-radius:10px;
                                display:flex;
                                justify-content:space-between;
                                gap:10px;
                            ">

                                <span
                                    style="
                                    color:var(--muted);
                                    font-size:10px;
                                ">
                                    Account Status
                                </span>

                                <strong
                                    style="
                                    color:var(--success);
                                    font-size:10px;
                                ">
                                    <i class="fa-solid fa-circle"
                                        style="font-size:6px;"></i>
                                    Active
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =============================================
                 INFORMATION CARDS
            ============================================== -->

                <div class="bottom-grid">


                    <div class="info-card">

                        <div class="info-card-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>

                        <h3>
                            Secure Administration
                        </h3>

                        <p>
                            Your administrator session is protected
                            with a separate admin authentication
                            session.
                        </p>

                        <a href="profile.php">
                            Account Security
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>


                    <div class="info-card">

                        <div class="info-card-icon">

                            <i class="fa-solid fa-box-open"></i>

                        </div>

                        <h3>
                            Inventory Management
                        </h3>

                        <p>
                            Add medicines, update product details,
                            manage stock and keep your catalogue
                            organized.
                        </p>

                        <a href="medicines.php">
                            Manage Medicines
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>


                    <div class="info-card">

                        <div class="info-card-icon">

                            <i class="fa-solid fa-truck"></i>

                        </div>

                        <h3>
                            Order Management
                        </h3>

                        <p>
                            Review customer orders and update
                            delivery status throughout the order
                            lifecycle.
                        </p>

                        <a href="orders.php">
                            Manage Orders
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>

                </div>

            </section>

        </main>

    </div>


    <!-- =========================================================
     TOAST
========================================================== -->

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


        // =====================================================
        // API BASE
        // =====================================================

        const API_BASE = "../backend/";


        // =====================================================
        // ELEMENTS
        // =====================================================

        const sidebar =
            document.getElementById("sidebar");

        const sidebarOverlay =
            document.getElementById("sidebarOverlay");

        const mobileMenuButton =
            document.getElementById("mobileMenuButton");

        const logoutButton =
            document.getElementById("logoutButton");

        const toast =
            document.getElementById("toast");

        const toastMessage =
            document.getElementById("toastMessage");


        // =====================================================
        // MOBILE SIDEBAR
        // =====================================================

        function openSidebar() {

            sidebar.classList.add("open");

            sidebarOverlay.classList.add("show");

            document.body.style.overflow = "hidden";
        }


        function closeSidebar() {

            sidebar.classList.remove("open");

            sidebarOverlay.classList.remove("show");

            document.body.style.overflow = "";
        }


        if (mobileMenuButton) {

            mobileMenuButton.addEventListener(
                "click",
                openSidebar
            );
        }


        if (sidebarOverlay) {

            sidebarOverlay.addEventListener(
                "click",
                closeSidebar
            );
        }


        document.querySelectorAll(".nav-link").forEach(
            function(link) {

                link.addEventListener(
                    "click",
                    function() {

                        if (
                            window.innerWidth <= 900
                        ) {

                            closeSidebar();
                        }
                    }
                );
            }
        );


        // =====================================================
        // TOAST
        // =====================================================

        let toastTimer = null;


        function showToast(
            message
        ) {

            if (!toast || !toastMessage) {
                return;
            }

            toastMessage.textContent =
                message;

            toast.classList.add("show");

            clearTimeout(toastTimer);

            toastTimer =
                setTimeout(
                    function() {

                        toast.classList.remove(
                            "show"
                        );

                    },
                    3000
                );
        }


        // =====================================================
        // ESCAPE HTML
        // =====================================================

        function escapeHtml(
            value
        ) {

            return String(
                    value ?? ""
                )
                .replace(
                    /&/g,
                    "&amp;"
                )
                .replace(
                    /</g,
                    "&lt;"
                )
                .replace(
                    />/g,
                    "&gt;"
                )
                .replace(
                    /"/g,
                    "&quot;"
                )
                .replace(
                    /'/g,
                    "&#039;"
                );
        }


        // =====================================================
        // FORMAT CURRENCY
        // =====================================================

        function formatCurrency(
            amount
        ) {

            const number =
                Number(amount || 0);

            return "₹" +
                number.toLocaleString(
                    "en-IN", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );
        }


        // =====================================================
        // FORMAT DATE
        // =====================================================

        function formatDate(
            dateValue
        ) {

            if (!dateValue) {
                return "—";
            }

            const date =
                new Date(
                    String(
                        dateValue
                    ).replace(
                        " ",
                        "T"
                    )
                );

            if (
                Number.isNaN(
                    date.getTime()
                )
            ) {
                return escapeHtml(
                    dateValue
                );
            }

            return date.toLocaleDateString(
                "en-IN", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric"
                }
            );
        }


        // =====================================================
        // CURRENT DATE
        // =====================================================

        function setCurrentDate() {

            const element =
                document.getElementById(
                    "currentDate"
                );

            if (!element) {
                return;
            }

            const now =
                new Date();

            element.textContent =
                now.toLocaleDateString(
                    "en-IN", {
                        weekday: "long",
                        day: "numeric",
                        month: "long",
                        year: "numeric"
                    }
                );
        }


        // =====================================================
        // API REQUEST HELPER
        // =====================================================

        async function apiRequest(
            endpoint,
            options = {}
        ) {

            const response =
                await fetch(
                    API_BASE + endpoint, {
                        credentials: "same-origin",

                        ...options,

                        headers: {
                            "Accept": "application/json",

                            ...(options.headers || {})
                        }
                    }
                );


            const text =
                await response.text();

            let data = null;


            try {

                data =
                    JSON.parse(text);

            } catch (error) {

                throw new Error(
                    "Invalid server response."
                );
            }


            if (
                response.status === 401 ||
                data.login_required
            ) {

                window.location.href =
                    "../login.php";

                return null;
            }


            if (!response.ok) {

                throw new Error(
                    data.message ||
                    "Unable to complete request."
                );
            }


            return data;
        }


        // =====================================================
        // LOAD DASHBOARD
        // =====================================================

        async function loadDashboard() {

            /*
             * The dashboard API will be connected
             * when backend/admin/dashboard.php
             * is created.
             *
             * For now the page displays a clean
             * frontend state.
             */

            try {

                const data =
                    await apiRequest(
                        "admin/dashboard.php"
                    );


                if (!data) {
                    return;
                }


                if (
                    data.success &&
                    data.dashboard
                ) {

                    updateDashboard(
                        data.dashboard
                    );
                }

            } catch (error) {

                /*
                 * Backend dashboard API has not
                 * been created yet.
                 *
                 * Keep the dashboard usable while
                 * frontend development continues.
                 */

                showFrontendFallback();
            }
        }


        // =====================================================
        // UPDATE DASHBOARD
        // =====================================================

        function updateDashboard(
            dashboard
        ) {

            const ordersElement =
                document.getElementById(
                    "totalOrders"
                );

            const revenueElement =
                document.getElementById(
                    "totalRevenue"
                );

            const medicinesElement =
                document.getElementById(
                    "totalMedicines"
                );

            const usersElement =
                document.getElementById(
                    "totalUsers"
                );


            if (ordersElement) {

                ordersElement.textContent =
                    Number(
                        dashboard.total_orders || 0
                    ).toLocaleString(
                        "en-IN"
                    );
            }


            if (revenueElement) {

                revenueElement.textContent =
                    formatCurrency(
                        dashboard.total_revenue
                    );
            }


            if (medicinesElement) {

                medicinesElement.textContent =
                    Number(
                        dashboard.total_medicines || 0
                    ).toLocaleString(
                        "en-IN"
                    );
            }


            if (usersElement) {

                usersElement.textContent =
                    Number(
                        dashboard.total_users || 0
                    ).toLocaleString(
                        "en-IN"
                    );
            }


            if (
                Array.isArray(
                    dashboard.recent_orders
                )
            ) {

                renderRecentOrders(
                    dashboard.recent_orders
                );
            }


            if (
                Array.isArray(
                    dashboard.low_stock
                )
            ) {

                renderInventory(
                    dashboard.low_stock
                );
            }
        }


        // =====================================================
        // RECENT ORDERS
        // =====================================================

        function renderRecentOrders(
            orders
        ) {

            const container =
                document.getElementById(
                    "recentOrders"
                );

            if (!container) {
                return;
            }


            if (!orders.length) {

                container.innerHTML = `
                <div class="empty-orders">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <div>No orders found.</div>
                </div>
            `;

                return;
            }


            container.innerHTML =
                orders
                .slice(0, 6)
                .map(
                    function(order) {

                        const status =
                            String(
                                order.status ||
                                "Order Placed"
                            );

                        const statusClass =
                            getStatusClass(
                                status
                            );

                        const orderNumber =
                            order.order_number ||
                            order.order_id ||
                            ("#" + order.id);


                        return `
                            <a
                                href="order-details.php?id=${encodeURIComponent(
                                    order.id || ""
                                )}"
                                class="order-row"
                            >

                                <div>
                                    <div class="order-number">
                                        ${escapeHtml(
                                            orderNumber
                                        )}
                                    </div>

                                    <div class="customer-name">
                                        ${escapeHtml(
                                            order.full_name ||
                                            order.customer_name ||
                                            "Customer"
                                        )}
                                    </div>
                                </div>


                                <div class="order-date">
                                    ${formatDate(
                                        order.created_at
                                    )}
                                </div>


                                <div class="order-amount">
                                    ${formatCurrency(
                                        order.total_amount ??
                                        order.total ??
                                        0
                                    )}
                                </div>


                                <div>
                                    <span
                                        class="status-badge ${statusClass}"
                                    >
                                        ${escapeHtml(
                                            status
                                        )}
                                    </span>
                                </div>

                            </a>
                        `;
                    }
                )
                .join("");
        }


        // =====================================================
        // INVENTORY
        // =====================================================

        function renderInventory(
            medicines
        ) {

            const container =
                document.getElementById(
                    "inventoryList"
                );

            if (!container) {
                return;
            }


            if (!medicines.length) {

                container.innerHTML = `
                <div class="empty-orders">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>
                        No low-stock medicines.
                    </div>
                </div>
            `;

                return;
            }


            container.innerHTML =
                medicines
                .slice(0, 6)
                .map(
                    function(medicine) {

                        const stock =
                            Math.max(
                                0,
                                Number(
                                    medicine.stock || 0
                                )
                            );

                        let progress =
                            Math.min(
                                100,
                                Math.max(
                                    5,
                                    stock
                                )
                            );

                        let progressClass =
                            "";

                        if (stock === 0) {

                            progressClass =
                                "out";

                        } else if (stock <= 10) {

                            progressClass =
                                "low";
                        }


                        return `
                            <div class="inventory-item">

                                <div class="inventory-top">

                                    <span
                                        class="medicine-name"
                                    >
                                        ${escapeHtml(
                                            medicine.name ||
                                            "Medicine"
                                        )}
                                    </span>

                                    <span
                                        class="stock-count"
                                    >
                                        ${stock}
                                        units
                                    </span>

                                </div>

                                <div class="stock-bar">

                                    <div
                                        class="stock-progress ${progressClass}"
                                        style="width:${progress}%"
                                    ></div>

                                </div>

                            </div>
                        `;
                    }
                )
                .join("");
        }


        // =====================================================
        // STATUS CLASS
        // =====================================================

        function getStatusClass(
            status
        ) {

            const normalized =
                String(
                    status || ""
                ).toLowerCase();


            if (
                normalized.includes(
                    "cancel"
                )
            ) {

                return "cancelled";
            }


            if (
                normalized.includes(
                    "deliver"
                )
            ) {

                return "delivered";
            }


            if (
                normalized.includes(
                    "ship"
                ) ||
                normalized.includes(
                    "delivery"
                )
            ) {

                return "shipped";
            }


            if (
                normalized.includes(
                    "pending"
                )
            ) {

                return "pending";
            }


            return "";
        }


        // =====================================================
        // FRONTEND FALLBACK
        // =====================================================

        function showFrontendFallback() {

            /*
             * Backend dashboard endpoint will be
             * connected next.
             *
             * Don't show fake statistics.
             */

            const orders =
                document.getElementById(
                    "totalOrders"
                );

            const revenue =
                document.getElementById(
                    "totalRevenue"
                );

            const medicines =
                document.getElementById(
                    "totalMedicines"
                );

            const users =
                document.getElementById(
                    "totalUsers"
                );


            if (orders) {
                orders.textContent = "—";
            }

            if (revenue) {
                revenue.textContent = "—";
            }

            if (medicines) {
                medicines.textContent = "—";
            }

            if (users) {
                users.textContent = "—";
            }


            const recentOrders =
                document.getElementById(
                    "recentOrders"
                );

            if (recentOrders) {

                recentOrders.innerHTML = `
                <div class="empty-orders">
                    <i class="fa-solid fa-chart-line"></i>
                    <div>
                        Dashboard statistics will appear
                        after the admin dashboard API
                        is connected.
                    </div>
                </div>
            `;
            }


            const inventory =
                document.getElementById(
                    "inventoryList"
                );

            if (inventory) {

                inventory.innerHTML = `
                <div class="empty-orders">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <div>
                        Inventory information will appear
                        here automatically.
                    </div>
                </div>
            `;
            }
        }


        // =====================================================
        // LOGOUT
        // =====================================================

        async function logout() {

            if (
                !confirm(
                    "Are you sure you want to logout?"
                )
            ) {

                return;
            }


            if (logoutButton) {

                logoutButton.disabled =
                    true;
            }


            try {

                const response =
                    await fetch(
                        "../backend/auth/logout.php", {
                            method: "POST",

                            credentials: "same-origin",

                            headers: {
                                "Accept": "application/json"
                            }
                        }
                    );


                let data = null;

                try {

                    data =
                        await response.json();

                } catch (error) {
                    data = null;
                }


                window.location.href =
                    "../login.php";

            } catch (error) {

                /*
                 * Redirect anyway so a stale
                 * frontend state cannot remain.
                 */

                window.location.href =
                    "../login.php";
            }
        }


        if (logoutButton) {

            logoutButton.addEventListener(
                "click",
                logout
            );
        }


        // =====================================================
        // NOTIFICATION BUTTON
        // =====================================================

        const notificationButton =
            document.getElementById(
                "notificationButton"
            );


        if (notificationButton) {

            notificationButton.addEventListener(
                "click",
                function() {

                    showToast(
                        "Notifications will appear here."
                    );

                }
            );
        }


        // =====================================================
        // RESIZE
        // =====================================================

        window.addEventListener(
            "resize",
            function() {

                if (
                    window.innerWidth > 900
                ) {

                    closeSidebar();
                }
            }
        );


        // =====================================================
        // INITIALIZE
        // =====================================================

        document.addEventListener(
            "DOMContentLoaded",
            function() {

                setCurrentDate();

                /*
                 * This will attempt to load the
                 * dashboard API. Until the API is
                 * created, the page remains clean
                 * without fake data.
                 */

                loadDashboard();

            }
        );
    </script>

</body>

</html>