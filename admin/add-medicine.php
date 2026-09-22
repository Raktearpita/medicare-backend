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
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Add Medicine | MediCare Admin</title>

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

            --shadow: 0 15px 45px rgba(18, 107, 91, .08);

            --sidebar-width: 270px;
            --radius: 18px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DM Sans", sans-serif;
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* =========================
           APP
        ========================== */

        .app {
            min-height: 100vh;
            display: flex;
        }

        /* =========================
           SIDEBAR
        ========================== */

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
        }

        .brand-text strong {
            display: block;

            font-family: "Manrope", sans-serif;
            font-size: 19px;
            font-weight: 800;
        }

        .brand-text span {
            display: block;

            color: rgba(255, 255, 255, .55);
            font-size: 10px;
            letter-spacing: .8px;
            margin-top: 1px;
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
            font-weight: 800;
            letter-spacing: 1.4px;

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

            color: rgba(255, 255, 255, .45);
            font-size: 10px;

            margin-top: 2px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* =========================
           MAIN
        ========================== */

        .main {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-width: 0;
        }

        .topbar {
            height: 76px;

            background: rgba(255, 255, 255, .95);
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
            font-family: "Manrope", sans-serif;
            font-size: clamp(19px, 2vw, 25px);
            font-weight: 800;
            color: var(--dark);
        }

        .page-heading p {
            color: var(--muted);
            font-size: 12px;
            margin-top: 2px;
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

        /* =========================
           CONTENT
        ========================== */

        .content {
            padding: clamp(20px, 3vw, 42px);
            max-width: 1700px;
            margin: auto;
        }

        .page-title {
            margin-bottom: 25px;
        }

        .page-title h2 {
            font-family: "Manrope", sans-serif;
            font-size: clamp(22px, 2.4vw, 31px);
            font-weight: 800;
            color: var(--dark);
        }

        .page-title p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 6px;
        }

        /* =========================
           FORM LAYOUT
        ========================== */

        .form-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 350px;
            gap: 20px;
            align-items: start;
        }

        .form-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 23px;
            border-bottom: 1px solid var(--border);
        }

        .card-header h3 {
            font-family: "Manrope", sans-serif;
            color: var(--dark);
            font-size: 17px;
            font-weight: 800;
        }

        .card-header p {
            color: var(--muted);
            font-size: 11px;
            margin-top: 4px;
        }

        .form-body {
            padding: 23px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 19px;
        }

        .form-group {
            min-width: 0;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 3px;

            color: var(--dark);

            font-size: 12px;
            font-weight: 700;

            margin-bottom: 7px;
        }

        .required {
            color: var(--danger);
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap>i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);

            color: #9aa8a4;
            font-size: 13px;

            pointer-events: none;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;

            border: 1px solid var(--border);
            border-radius: 11px;

            background: #fbfdfc;
            color: var(--dark);

            outline: none;

            transition: .2s ease;

            font-size: 13px;
        }

        .form-input,
        .form-select {
            height: 46px;
            padding: 0 13px 0 39px;
        }

        .form-textarea {
            min-height: 125px;
            resize: vertical;
            padding: 13px;
            line-height: 1.55;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            background: #fff;
            border-color: var(--primary);

            box-shadow:
                0 0 0 3px rgba(18, 107, 91, .08);
        }

        .form-select {
            cursor: pointer;
        }

        .input-wrap.no-icon .form-input,
        .input-wrap.no-icon .form-select {
            padding-left: 13px;
        }

        .field-help {
            color: #8a9894;
            font-size: 10px;
            margin-top: 5px;
        }

        /* =========================
           IMAGE UPLOAD
        ========================== */

        .upload-box {
            border: 1.5px dashed #cbded8;
            background: #fbfdfc;
            border-radius: 16px;

            min-height: 315px;

            padding: 16px;

            display: flex;
            align-items: center;
            justify-content: center;

            text-align: center;

            transition: .2s ease;
            position: relative;
            overflow: hidden;
        }

        .upload-box:hover,
        .upload-box.dragover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .upload-icon {
            width: 65px;
            height: 65px;

            border-radius: 18px;

            background: var(--primary-light);
            color: var(--primary);

            display: grid;
            place-items: center;

            font-size: 25px;

            margin-bottom: 15px;
        }

        .upload-placeholder h4 {
            color: var(--dark);
            font-family: "Manrope", sans-serif;
            font-size: 15px;
            font-weight: 800;
        }

        .upload-placeholder p {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.6;
            max-width: 230px;
            margin: 6px auto 14px;
        }

        .choose-image {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            height: 40px;
            padding: 0 14px;

            border-radius: 10px;

            background: var(--primary);
            color: #fff;

            font-size: 11px;
            font-weight: 700;

            cursor: pointer;
        }

        .choose-image:hover {
            background: var(--primary-dark);
        }

        #imageInput {
            display: none;
        }

        .image-preview {
            display: none;

            position: absolute;
            inset: 10px;

            border-radius: 12px;
            overflow: hidden;

            background: #fff;
        }

        .image-preview.show {
            display: block;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #f8fbfa;
        }

        .remove-image {
            position: absolute;
            top: 10px;
            right: 10px;

            width: 35px;
            height: 35px;

            border: 0;
            border-radius: 50%;

            background: rgba(18, 33, 30, .85);
            color: #fff;

            display: grid;
            place-items: center;
        }

        .remove-image:hover {
            background: var(--danger);
        }

        /* =========================
           SIDE INFO
        ========================== */

        .info-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 22px;
        }

        .info-title {
            display: flex;
            align-items: center;
            gap: 10px;

            color: var(--dark);
            font-family: "Manrope", sans-serif;

            font-size: 15px;
            font-weight: 800;

            margin-bottom: 17px;
        }

        .info-title-icon {
            width: 35px;
            height: 35px;

            border-radius: 10px;

            background: var(--primary-light);
            color: var(--primary);

            display: grid;
            place-items: center;
        }

        .tip {
            display: flex;
            gap: 10px;
            padding: 11px 0;

            border-bottom: 1px solid #edf3f0;
        }

        .tip:last-child {
            border-bottom: 0;
        }

        .tip-icon {
            color: var(--primary);
            font-size: 12px;
            padding-top: 2px;
        }

        .tip p {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.55;
        }

        /* =========================
           FORM ACTIONS
        ========================== */

        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;

            padding: 18px 23px;

            border-top: 1px solid var(--border);
            background: #fbfdfc;
        }

        .cancel-btn,
        .submit-btn {
            min-height: 44px;

            border-radius: 11px;

            padding: 0 17px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            font-size: 12px;
            font-weight: 700;
        }

        .cancel-btn {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .cancel-btn:hover {
            border-color: #aabbb6;
        }

        .submit-btn {
            border: 0;
            background: var(--primary);
            color: #fff;

            min-width: 145px;

            box-shadow: 0 8px 18px rgba(18, 107, 91, .18);
        }

        .submit-btn:hover {
            background: var(--primary-dark);
        }

        .submit-btn:disabled {
            opacity: .65;
            cursor: not-allowed;
            transform: none;
        }

        /* =========================
           TOAST
        ========================== */

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

        .toast.error i {
            color: #ff8b8b;
        }

        /* =========================
           OVERLAY
        ========================== */

        .overlay {
            display: none;

            position: fixed;
            inset: 0;

            background: rgba(8, 25, 21, .45);

            z-index: 950;
        }

        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width: 1100px) {
            .form-layout {
                grid-template-columns: 1fr;
            }

            .side-column {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 20px;
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

        @media (max-width: 700px) {
            .content {
                padding: 18px 13px 30px;
            }

            .topbar {
                padding: 0 13px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .side-column {
                grid-template-columns: 1fr;
            }

            .form-body {
                padding: 17px;
            }

            .card-header {
                padding: 17px;
            }

            .form-actions {
                padding: 15px 17px;
            }

            .cancel-btn,
            .submit-btn {
                flex: 1;
            }
        }

        @media (max-width: 480px) {
            .page-heading p {
                display: none;
            }

            .top-avatar {
                display: none;
            }

            .upload-box {
                min-height: 270px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .cancel-btn,
            .submit-btn {
                width: 100%;
            }

            .toast {
                left: 13px;
                right: 13px;
                bottom: 13px;
                min-width: 0;
            }
        }

        @media (max-width: 320px) {
            .content {
                padding-left: 9px;
                padding-right: 9px;
            }

            .form-body {
                padding: 13px;
            }

            .card-header {
                padding: 14px;
            }
        }

        @media (min-width: 1800px) {
            :root {
                --sidebar-width: 300px;
            }

            .content {
                max-width: 1900px;
                padding-left: 55px;
                padding-right: 55px;
            }
        }

        @media (min-width: 2400px) {
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

                <a href="orders.php" class="nav-item">
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

                <a
                    href="add-medicine.php"
                    class="nav-item active">
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

        <!-- =========================
         MAIN
    ========================== -->

        <main class="main">

            <header class="topbar">

                <div class="topbar-left">

                    <button
                        class="mobile-menu"
                        id="mobileMenu"
                        aria-label="Open menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <div class="page-heading">

                        <h1>Add Medicine</h1>

                        <p>
                            Add a new medicine to your inventory
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

                <div class="page-title">

                    <h2>
                        Add New Medicine
                    </h2>

                    <p>
                        Enter medicine details and upload a product image.
                    </p>

                </div>

                <form
                    id="medicineForm"
                    enctype="multipart/form-data"
                    novalidate>

                    <div class="form-layout">

                        <!-- =========================
                         MAIN FORM
                    ========================== -->

                        <div class="form-card">

                            <div class="card-header">

                                <h3>
                                    Medicine Information
                                </h3>

                                <p>
                                    Basic information about the medicine.
                                </p>

                            </div>

                            <div class="form-body">

                                <div class="form-grid">

                                    <!-- NAME -->

                                    <div class="form-group full">

                                        <label
                                            class="form-label"
                                            for="name">
                                            Medicine Name
                                            <span class="required">*</span>
                                        </label>

                                        <div class="input-wrap">

                                            <i class="fa-solid fa-pills"></i>

                                            <input
                                                type="text"
                                                id="name"
                                                name="name"
                                                class="form-input"
                                                placeholder="e.g. Paracetamol 500mg"
                                                maxlength="150"
                                                required>

                                        </div>

                                    </div>

                                    <!-- CATEGORY -->

                                    <div class="form-group">

                                        <label
                                            class="form-label"
                                            for="category_id">
                                            Category
                                            <span class="required">*</span>
                                        </label>

                                        <div class="input-wrap">

                                            <i class="fa-solid fa-layer-group"></i>

                                            <select
                                                id="category_id"
                                                name="category_id"
                                                class="form-select"
                                                required>
                                                <option value="">
                                                    Loading categories...
                                                </option>
                                            </select>

                                        </div>

                                    </div>

                                    <!-- PRICE -->

                                    <div class="form-group">

                                        <label
                                            class="form-label"
                                            for="price">
                                            Price
                                            <span class="required">*</span>
                                        </label>

                                        <div class="input-wrap">

                                            <i class="fa-solid fa-indian-rupee-sign"></i>

                                            <input
                                                type="number"
                                                id="price"
                                                name="price"
                                                class="form-input"
                                                placeholder="e.g. 45.00"
                                                min="0.01"
                                                max="9999999"
                                                step="0.01"
                                                required>

                                        </div>

                                    </div>

                                    <!-- STOCK -->

                                    <div class="form-group">

                                        <label
                                            class="form-label"
                                            for="stock">
                                            Stock Quantity
                                            <span class="required">*</span>
                                        </label>

                                        <div class="input-wrap">

                                            <i class="fa-solid fa-boxes-stacked"></i>

                                            <input
                                                type="number"
                                                id="stock"
                                                name="stock"
                                                class="form-input"
                                                placeholder="e.g. 100"
                                                min="0"
                                                max="9999999"
                                                step="1"
                                                required>

                                        </div>

                                        <div class="field-help">
                                            Set 0 if the medicine is currently unavailable.
                                        </div>

                                    </div>

                                    <!-- DESCRIPTION -->

                                    <div class="form-group full">

                                        <label
                                            class="form-label"
                                            for="description">
                                            Description
                                            <span class="required">*</span>
                                        </label>

                                        <div class="input-wrap no-icon">

                                            <textarea
                                                id="description"
                                                name="description"
                                                class="form-textarea"
                                                placeholder="Enter medicine description, uses, dosage information, etc."
                                                maxlength="2000"
                                                required></textarea>

                                        </div>

                                        <div
                                            class="field-help"
                                            id="descriptionCount">
                                            0 / 2000 characters
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div class="form-actions">

                                <a
                                    href="medicines.php"
                                    class="cancel-btn">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    Cancel
                                </a>

                                <button
                                    type="submit"
                                    class="submit-btn"
                                    id="submitBtn">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Add Medicine</span>
                                </button>

                            </div>

                        </div>

                        <!-- =========================
                         SIDE COLUMN
                    ========================== -->

                        <div class="side-column">

                            <!-- IMAGE -->

                            <div class="form-card">

                                <div class="card-header">

                                    <h3>
                                        Medicine Image
                                    </h3>

                                    <p>
                                        Upload a clear product image.
                                    </p>

                                </div>

                                <div class="form-body">

                                    <div
                                        class="upload-box"
                                        id="uploadBox">

                                        <input
                                            type="file"
                                            id="imageInput"
                                            name="image"
                                            accept="image/jpeg,image/png,image/webp,image/jpg">

                                        <div
                                            class="upload-placeholder"
                                            id="uploadPlaceholder">

                                            <div class="upload-icon">
                                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                            </div>

                                            <h4>
                                                Upload medicine image
                                            </h4>

                                            <p>
                                                JPG, JPEG, PNG or WEBP.
                                                Maximum recommended size: 2 MB.
                                            </p>

                                            <label
                                                for="imageInput"
                                                class="choose-image">
                                                <i class="fa-solid fa-image"></i>
                                                Choose Image
                                            </label>

                                        </div>

                                        <div
                                            class="image-preview"
                                            id="imagePreview">

                                            <img
                                                id="previewImage"
                                                src=""
                                                alt="Medicine preview">

                                            <button
                                                type="button"
                                                class="remove-image"
                                                id="removeImage"
                                                title="Remove image">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- TIPS -->

                            <div
                                class="info-card"
                                style="margin-top:20px;">

                                <div class="info-title">

                                    <div class="info-title-icon">
                                        <i class="fa-solid fa-lightbulb"></i>
                                    </div>

                                    Helpful Tips

                                </div>

                                <div class="tip">

                                    <div class="tip-icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>

                                    <p>
                                        Use the exact medicine name shown on its packaging.
                                    </p>

                                </div>

                                <div class="tip">

                                    <div class="tip-icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>

                                    <p>
                                        Upload a clear image with the medicine packaging visible.
                                    </p>

                                </div>

                                <div class="tip">

                                    <div class="tip-icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>

                                    <p>
                                        Double-check price and stock quantity before saving.
                                    </p>

                                </div>

                                <div class="tip">

                                    <div class="tip-icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>

                                    <p>
                                        Avoid duplicate medicine names in your inventory.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </form>

            </section>

        </main>

    </div>

    <!-- TOAST -->

    <div
        class="toast"
        id="toast">
        <i class="fa-solid fa-circle-check"></i>
        <span id="toastMessage">Done</span>
    </div>

    <script>
        "use strict";

        /*
         * Existing medicine API.
         */
        const ADD_MEDICINE_API =
            "../backend/medicines/add-medicine.php";

        /*
         * The project currently does not have a dedicated
         * category-list API, so we load categories from
         * the medicine listing endpoint.
         */
        const CATEGORY_API =
            "../backend/categories/manage-categories.php";

        const LOW_IMAGE_SIZE =
            2 * 1024 * 1024;

        const form =
            document.getElementById("medicineForm");

        const imageInput =
            document.getElementById("imageInput");

        const uploadBox =
            document.getElementById("uploadBox");

        const uploadPlaceholder =
            document.getElementById("uploadPlaceholder");

        const imagePreview =
            document.getElementById("imagePreview");

        const previewImage =
            document.getElementById("previewImage");

        const removeImage =
            document.getElementById("removeImage");

        const categorySelect =
            document.getElementById("category_id");

        const submitBtn =
            document.getElementById("submitBtn");

        const description =
            document.getElementById("description");

        const descriptionCount =
            document.getElementById("descriptionCount");

        const toast =
            document.getElementById("toast");

        const toastMessage =
            document.getElementById("toastMessage");


        /* =========================
           HELPERS
        ========================= */

        function escapeHtml(value) {

            return String(value ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }


        function showToast(
            message,
            type = "success"
        ) {

            toastMessage.textContent =
                message;

            toast.classList.remove("error");

            const icon =
                toast.querySelector("i");

            if (type === "error") {

                toast.classList.add("error");

                icon.className =
                    "fa-solid fa-circle-exclamation";

            } else {

                icon.className =
                    "fa-solid fa-circle-check";
            }

            toast.classList.add("show");

            clearTimeout(showToast.timer);

            showToast.timer =
                setTimeout(() => {
                    toast.classList.remove("show");
                }, 3500);
        }


        /* =========================
           LOAD CATEGORIES
        ========================= */

        async function loadCategories() {

            categorySelect.innerHTML = `
        <option value="">
            Loading categories...
        </option>
    `;

            try {

                const response = await fetch(
                    CATEGORY_API, {
                        method: "GET",
                        credentials: "same-origin",
                        headers: {
                            "Accept": "application/json"
                        },
                        cache: "no-store"
                    }
                );

                const data = await response.json();

                if (!response.ok || !data.success) {

                    throw new Error(
                        data.message ||
                        "Unable to load categories."
                    );
                }

                const categories =
                    Array.isArray(data.categories) ?
                    data.categories :
                    [];

                categorySelect.innerHTML = `
            <option value="">
                Select category
            </option>
        `;

                if (!categories.length) {

                    categorySelect.innerHTML += `
                <option value="" disabled>
                    No categories available
                </option>
            `;

                    return;
                }

                categories.forEach(category => {

                    const option =
                        document.createElement("option");

                    option.value =
                        String(category.id);

                    option.textContent =
                        String(category.name);

                    categorySelect.appendChild(option);
                });

            } catch (error) {

                console.error(
                    "Category loading error:",
                    error
                );

                categorySelect.innerHTML = `
            <option value="">
                Unable to load categories
            </option>
        `;

                showToast(
                    error.message ||
                    "Unable to load categories.",
                    "error"
                );
            }
        }

        /* =========================
           IMAGE PREVIEW
        ========================= */

        imageInput.addEventListener(
            "change",
            handleImage
        );


        function handleImage() {

            const file =
                imageInput.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = [
                "image/jpeg",
                "image/jpg",
                "image/png",
                "image/webp"
            ];

            if (
                !allowedTypes.includes(
                    file.type
                )
            ) {

                imageInput.value = "";

                showToast(
                    "Please select JPG, JPEG, PNG or WEBP image.",
                    "error"
                );

                return;
            }

            if (
                file.size >
                LOW_IMAGE_SIZE
            ) {

                imageInput.value = "";

                showToast(
                    "Image size must be 2 MB or less.",
                    "error"
                );

                return;
            }

            const reader =
                new FileReader();

            reader.onload = function(event) {

                previewImage.src =
                    event.target.result;

                imagePreview.classList.add(
                    "show"
                );

                uploadPlaceholder.style.display =
                    "none";
            };

            reader.readAsDataURL(file);
        }


        /* =========================
           REMOVE IMAGE
        ========================= */

        removeImage.addEventListener(
            "click",
            () => {

                imageInput.value = "";

                previewImage.src = "";

                imagePreview.classList.remove(
                    "show"
                );

                uploadPlaceholder.style.display =
                    "flex";
            }
        );


        /* =========================
           DRAG & DROP
        ========================= */

        [
            "dragenter",
            "dragover"
        ].forEach(eventName => {

            uploadBox.addEventListener(
                eventName,
                event => {

                    event.preventDefault();

                    uploadBox.classList.add(
                        "dragover"
                    );
                }
            );
        });


        [
            "dragleave",
            "drop"
        ].forEach(eventName => {

            uploadBox.addEventListener(
                eventName,
                event => {

                    event.preventDefault();

                    uploadBox.classList.remove(
                        "dragover"
                    );
                }
            );
        });


        uploadBox.addEventListener(
            "drop",
            event => {

                const files =
                    event.dataTransfer.files;

                if (!files.length) {
                    return;
                }

                imageInput.files =
                    files;

                imageInput.dispatchEvent(
                    new Event("change")
                );
            }
        );


        /* =========================
           DESCRIPTION COUNTER
        ========================= */

        description.addEventListener(
            "input",
            () => {

                descriptionCount.textContent =
                    `${description.value.length} / 2000 characters`;
            }
        );


        /* =========================
           FORM VALIDATION
        ========================= */

        function validateForm() {

            const name =
                document.getElementById("name")
                .value
                .trim();

            const category =
                categorySelect.value;

            const price =
                Number(
                    document.getElementById("price").value
                );

            const stock =
                Number(
                    document.getElementById("stock").value
                );

            const descriptionValue =
                description.value.trim();

            if (!name) {

                showToast(
                    "Please enter the medicine name.",
                    "error"
                );

                document.getElementById(
                    "name"
                ).focus();

                return false;
            }

            if (!category) {

                showToast(
                    "Please select a medicine category.",
                    "error"
                );

                categorySelect.focus();

                return false;
            }

            if (
                !Number.isFinite(price) ||
                price <= 0
            ) {

                showToast(
                    "Please enter a valid medicine price.",
                    "error"
                );

                document.getElementById(
                    "price"
                ).focus();

                return false;
            }

            if (
                !Number.isInteger(stock) ||
                stock < 0
            ) {

                showToast(
                    "Please enter a valid stock quantity.",
                    "error"
                );

                document.getElementById(
                    "stock"
                ).focus();

                return false;
            }

            if (!descriptionValue) {

                showToast(
                    "Please enter a medicine description.",
                    "error"
                );

                description.focus();

                return false;
            }

            return true;
        }


        /* =========================
           SUBMIT
        ========================= */

        form.addEventListener(
            "submit",
            async event => {

                event.preventDefault();

                if (!validateForm()) {
                    return;
                }

                submitBtn.disabled = true;

                submitBtn.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin"></i>
            <span>Adding Medicine...</span>
        `;

                try {

                    /*
                     * FormData is intentionally used because
                     * the medicine image is an actual file.
                     *
                     * The backend will be updated next to
                     * accept multipart/form-data.
                     */
                    const formData =
                        new FormData(form);

                    const response =
                        await fetch(
                            ADD_MEDICINE_API, {
                                method: "POST",
                                credentials: "same-origin",
                                body: formData
                            }
                        );

                    let data;

                    try {
                        data =
                            await response.json();
                    } catch {
                        throw new Error(
                            "Invalid response from server."
                        );
                    }

                    if (
                        !response.ok ||
                        !data.success
                    ) {

                        throw new Error(
                            data.message ||
                            "Unable to add medicine."
                        );
                    }

                    showToast(
                        data.message ||
                        "Medicine added successfully."
                    );

                    setTimeout(() => {

                        window.location.href =
                            "medicines.php";

                    }, 900);

                } catch (error) {

                    console.error(
                        "Add medicine error:",
                        error
                    );

                    showToast(
                        error.message ||
                        "Unable to add medicine.",
                        "error"
                    );

                    submitBtn.disabled = false;

                    submitBtn.innerHTML = `
                <i class="fa-solid fa-plus"></i>
                <span>Add Medicine</span>
            `;
                }
            }
        );


        /* =========================
           MOBILE SIDEBAR
        ========================= */

        const sidebar =
            document.getElementById("sidebar");

        const overlay =
            document.getElementById("overlay");

        const mobileMenu =
            document.getElementById("mobileMenu");


        function openSidebar() {

            sidebar.classList.add("open");

            overlay.classList.add("show");

            document.body.style.overflow =
                "hidden";
        }


        function closeSidebar() {

            sidebar.classList.remove("open");

            overlay.classList.remove("show");

            document.body.style.overflow =
                "";
        }


        mobileMenu.addEventListener(
            "click",
            openSidebar
        );


        overlay.addEventListener(
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


        /* =========================
           ADMIN INITIALS
        ========================= */

        const adminName =
            <?= json_encode(
                $adminName,
                JSON_UNESCAPED_UNICODE
            ); ?>;


        function getInitials(name) {

            const words =
                String(name || "Admin")
                .trim()
                .split(/\s+/)
                .filter(Boolean);

            if (!words.length) {
                return "A";
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


        const initials =
            getInitials(adminName);


        document.getElementById(
            "sidebarAvatar"
        ).textContent = initials;


        document.getElementById(
            "topAvatar"
        ).textContent = initials;


        /* =========================
           INITIALIZATION
        ========================= */

        loadCategories();
    </script>

</body>

</html>