<?php

/**
 * MediCare Pharmacy
 * Admin Category Management
 *
 * File:
 * admin/categories.php
 */

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Categories | MediCare Admin</title>

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
            --danger: #d95353;

            --shadow:
                0 15px 45px rgba(18, 107, 91, .08);

            --sidebar-width: 270px;
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
        input {
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

            border-bottom:
                1px solid rgba(255, 255, 255, .08);
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

            padding:
                0 12px 10px;
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
            background:
                rgba(255, 255, 255, .07);
        }


        .nav-item.active {
            color: #fff;

            background:
                rgba(255, 255, 255, .12);

            box-shadow:
                inset 3px 0 0 #74d3bd;
        }


        .sidebar-footer {
            padding: 15px;

            border-top:
                1px solid rgba(255, 255, 255, .08);
        }


        .admin-mini {
            display: flex;
            align-items: center;

            gap: 11px;

            padding: 11px;

            border-radius: 13px;

            background:
                rgba(255, 255, 255, .06);
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

            color:
                rgba(255, 255, 255, .45);

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
            width:
                calc(100% - var(--sidebar-width));

            margin-left:
                var(--sidebar-width);

            min-width: 0;
        }


        .topbar {
            height: 76px;

            background:
                rgba(255, 255, 255, .95);

            backdrop-filter: blur(15px);

            border-bottom:
                1px solid var(--border);

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding:
                0 clamp(18px, 3vw, 42px);

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

            border:
                1px solid var(--border);

            border-radius: 11px;

            background: #fff;
            color: var(--dark);
        }


        .page-heading h1 {
            font-family:
                "Manrope", sans-serif;

            font-size:
                clamp(19px, 2vw, 25px);

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

            border:
                1px solid var(--border);

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
            padding:
                clamp(20px, 3vw, 42px);

            max-width: 1500px;

            margin: auto;
        }


        .page-title {
            margin-bottom: 25px;
        }


        .page-title h2 {
            font-family:
                "Manrope", sans-serif;

            font-size:
                clamp(22px, 2.4vw, 31px);

            font-weight: 800;

            color: var(--dark);
        }


        .page-title p {
            color: var(--muted);

            font-size: 13px;

            margin-top: 6px;
        }


        /* =========================
           GRID
        ========================== */

        .category-layout {
            display: grid;

            grid-template-columns:
                350px minmax(0, 1fr);

            gap: 20px;

            align-items: start;
        }


        .card {
            background: #fff;

            border:
                1px solid var(--border);

            border-radius: 18px;

            box-shadow: var(--shadow);

            overflow: hidden;
        }


        .card-header {
            padding: 20px 23px;

            border-bottom:
                1px solid var(--border);
        }


        .card-header h3 {
            font-family:
                "Manrope", sans-serif;

            font-size: 17px;

            font-weight: 800;

            color: var(--dark);
        }


        .card-header p {
            color: var(--muted);

            font-size: 11px;

            margin-top: 4px;
        }


        .card-body {
            padding: 23px;
        }


        /* =========================
           FORM
        ========================== */

        .form-group {
            margin-bottom: 18px;
        }


        .form-label {
            display: block;

            color: var(--dark);

            font-size: 12px;
            font-weight: 700;

            margin-bottom: 7px;
        }


        .required {
            color: var(--danger);
        }


        .form-input {
            width: 100%;

            height: 46px;

            padding: 0 13px;

            border:
                1px solid var(--border);

            border-radius: 11px;

            background: #fbfdfc;

            color: var(--dark);

            outline: none;

            font-size: 13px;

            transition: .2s ease;
        }


        .form-input:focus {
            background: #fff;

            border-color:
                var(--primary);

            box-shadow:
                0 0 0 3px rgba(18, 107, 91, .08);
        }


        .add-btn {
            width: 100%;

            height: 46px;

            border: 0;

            border-radius: 11px;

            background:
                var(--primary);

            color: #fff;

            font-size: 12px;
            font-weight: 700;

            display: flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            box-shadow:
                0 8px 18px rgba(18, 107, 91, .18);
        }


        .add-btn:hover {
            background:
                var(--primary-dark);
        }


        .add-btn:disabled {
            opacity: .65;
            cursor: not-allowed;
        }


        /* =========================
           CATEGORY LIST
        ========================== */

        .category-list {
            padding: 0;
        }


        .category-row {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;

            padding: 17px 23px;

            border-bottom:
                1px solid #edf3f0;
        }


        .category-row:last-child {
            border-bottom: 0;
        }


        .category-info {
            display: flex;
            align-items: center;

            gap: 13px;

            min-width: 0;
        }


        .category-icon {
            width: 40px;
            height: 40px;

            flex-shrink: 0;

            border-radius: 11px;

            background:
                var(--primary-light);

            color:
                var(--primary);

            display: grid;
            place-items: center;
        }


        .category-name {
            color: var(--dark);

            font-size: 13px;
            font-weight: 700;

            word-break: break-word;
        }


        .category-id {
            color: var(--muted);

            font-size: 10px;

            margin-top: 3px;
        }


        .delete-btn {
            width: 36px;
            height: 36px;

            flex-shrink: 0;

            border: 0;

            border-radius: 10px;

            background:
                #fff0f0;

            color:
                var(--danger);

            display: grid;
            place-items: center;
        }


        .delete-btn:hover {
            background:
                var(--danger);

            color: #fff;
        }


        .empty-state {
            padding: 50px 20px;

            text-align: center;

            color: var(--muted);
        }


        .empty-state i {
            font-size: 30px;

            margin-bottom: 12px;

            opacity: .45;
        }


        .empty-state p {
            font-size: 12px;
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

            background:
                #12211e;

            color: #fff;

            box-shadow:
                0 18px 45px rgba(0, 0, 0, .18);

            display: flex;
            align-items: center;

            gap: 10px;

            font-size: 12px;

            transform:
                translateY(25px);

            opacity: 0;

            pointer-events: none;

            transition: .25s ease;
        }


        .toast.show {
            transform:
                translateY(0);

            opacity: 1;
        }


        .toast.error i {
            color: #ff8b8b;
        }


        .toast.success i {
            color: #74d3bd;
        }


        /* =========================
           OVERLAY
        ========================== */

        .overlay {
            display: none;

            position: fixed;
            inset: 0;

            background:
                rgba(8, 25, 21, .45);

            z-index: 950;
        }


        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width: 900px) {

            .sidebar {
                transform:
                    translateX(-100%);
            }

            .sidebar.open {
                transform:
                    translateX(0);
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

            .category-layout {
                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 600px) {

            .content {
                padding:
                    18px 13px 30px;
            }

            .topbar {
                padding:
                    0 13px;
            }

            .page-heading p {
                display: none;
            }

            .top-avatar {
                display: none;
            }

            .card-body {
                padding: 17px;
            }

            .card-header {
                padding: 17px;
            }

            .category-row {
                padding:
                    14px 17px;
            }

            .toast {
                left: 13px;
                right: 13px;

                min-width: 0;

                bottom: 13px;
            }
        }
    </style>

</head>


<body>

    <div class="app">


        <!-- =========================
         SIDEBAR
    ========================== -->

        <aside
            class="sidebar"
            id="sidebar">

            <div class="sidebar-header">

                <a
                    href="dashboard.php"
                    class="brand">

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


                <a
                    href="dashboard.php"
                    class="nav-item">

                    <i class="fa-solid fa-chart-pie"></i>

                    <span>Dashboard</span>

                </a>


                <div
                    class="nav-label"
                    style="margin-top:22px;">

                    Management

                </div>


                <a
                    href="medicines.php"
                    class="nav-item">

                    <i class="fa-solid fa-pills"></i>

                    <span>Medicines</span>

                </a>


                <a
                    href="orders.php"
                    class="nav-item">

                    <i class="fa-solid fa-bag-shopping"></i>

                    <span>Orders</span>

                </a>

                <a
                    href="categories.php"
                    class="nav-item active">

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
                    class="nav-item">

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
            id="overlay">
        </div>


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

                        <h1>
                            Categories
                        </h1>

                        <p>
                            Manage medicine categories
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
                        Medicine Categories
                    </h2>

                    <p>
                        Add and manage categories used for your medicines.
                    </p>

                </div>


                <div class="category-layout">


                    <!-- =========================
                     ADD CATEGORY
                ========================== -->

                    <div class="card">

                        <div class="card-header">

                            <h3>
                                Add Category
                            </h3>

                            <p>
                                Create a new medicine category.
                            </p>

                        </div>


                        <div class="card-body">

                            <form id="categoryForm">

                                <div class="form-group">

                                    <label
                                        class="form-label"
                                        for="categoryName">

                                        Category Name

                                        <span class="required">
                                            *
                                        </span>

                                    </label>


                                    <input
                                        type="text"
                                        id="categoryName"
                                        name="name"
                                        class="form-input"
                                        placeholder="e.g. Pain Relief"
                                        maxlength="100"
                                        autocomplete="off"
                                        required>

                                </div>


                                <button
                                    type="submit"
                                    class="add-btn"
                                    id="addBtn">

                                    <i
                                        class="fa-solid fa-plus">
                                    </i>

                                    <span>
                                        Add Category
                                    </span>

                                </button>

                            </form>

                        </div>

                    </div>


                    <!-- =========================
                     CATEGORY LIST
                ========================== -->

                    <div class="card">

                        <div class="card-header">

                            <h3>
                                Existing Categories
                            </h3>

                            <p>
                                Categories currently available in your pharmacy.
                            </p>

                        </div>


                        <div
                            class="category-list"
                            id="categoryList">

                            <div class="empty-state">

                                <i
                                    class="fa-solid fa-spinner fa-spin">
                                </i>

                                <p>
                                    Loading categories...
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

        </main>

    </div>


    <!-- =========================
     TOAST
========================== -->

    <div
        class="toast"
        id="toast">

        <i
            class="fa-solid fa-circle-check">
        </i>

        <span id="toastMessage">
            Done
        </span>

    </div>


    <script>
        "use strict";


        /* =========================
           API
        ========================= */

        const CATEGORY_API =
            "../backend/categories/manage-categories.php";


        /* =========================
           ELEMENTS
        ========================= */

        const categoryForm =
            document.getElementById(
                "categoryForm"
            );

        const categoryName =
            document.getElementById(
                "categoryName"
            );

        const categoryList =
            document.getElementById(
                "categoryList"
            );

        const addBtn =
            document.getElementById(
                "addBtn"
            );

        const toast =
            document.getElementById(
                "toast"
            );

        const toastMessage =
            document.getElementById(
                "toastMessage"
            );


        /* =========================
           TOAST
        ========================= */

        function showToast(
            message,
            type = "success"
        ) {

            toastMessage.textContent =
                message;

            toast.classList.remove(
                "error",
                "success"
            );

            toast.classList.add(
                type
            );

            const icon =
                toast.querySelector("i");

            if (type === "error") {

                icon.className =
                    "fa-solid fa-circle-exclamation";

            } else {

                icon.className =
                    "fa-solid fa-circle-check";

            }

            toast.classList.add(
                "show"
            );

            clearTimeout(
                showToast.timer
            );

            showToast.timer =
                setTimeout(() => {

                    toast.classList.remove(
                        "show"
                    );

                }, 3500);
        }


        /* =========================
           ESCAPE HTML
        ========================= */

        function escapeHtml(value) {

            return String(value ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }


        /* =========================
           LOAD CATEGORIES
        ========================= */

        async function loadCategories() {

            categoryList.innerHTML = `
        <div class="empty-state">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Loading categories...</p>
        </div>
    `;


            try {

                const response =
                    await fetch(
                        CATEGORY_API, {
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


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.message ||
                        "Unable to load categories."
                    );
                }


                const categories =
                    Array.isArray(
                        data.categories
                    ) ?
                    data.categories : [];


                if (!categories.length) {

                    categoryList.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-layer-group"></i>
                    <p>No categories found.</p>
                </div>
            `;

                    return;
                }


                categoryList.innerHTML =
                    categories.map(
                        category => {

                            const id =
                                Number(
                                    category.id
                                );

                            const name =
                                escapeHtml(
                                    category.name
                                );


                            return `
                        <div
                            class="category-row"
                            data-id="${id}">

                            <div class="category-info">

                                <div class="category-icon">

                                    <i class="fa-solid fa-layer-group"></i>

                                </div>

                                <div>

                                    <div class="category-name">
                                        ${name}
                                    </div>

                                    <div class="category-id">
                                        Category ID: ${id}
                                    </div>

                                </div>

                            </div>


                            <button
                                type="button"
                                class="delete-btn"
                                title="Delete category"
                                onclick="deleteCategory(${id})">

                                <i class="fa-solid fa-trash"></i>

                            </button>

                        </div>
                    `;

                        }
                    ).join("");


            } catch (error) {

                console.error(
                    "Load categories error:",
                    error
                );


                categoryList.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-circle-exclamation"></i>
                <p>
                    ${escapeHtml(
                        error.message ||
                        "Unable to load categories."
                    )}
                </p>
            </div>
        `;

            }
        }


        /* =========================
           ADD CATEGORY
        ========================= */

        categoryForm.addEventListener(
            "submit",
            async event => {

                event.preventDefault();


                const name =
                    categoryName.value.trim();


                if (!name) {

                    showToast(
                        "Please enter a category name.",
                        "error"
                    );

                    categoryName.focus();

                    return;
                }


                if (name.length < 2) {

                    showToast(
                        "Category name must contain at least 2 characters.",
                        "error"
                    );

                    categoryName.focus();

                    return;
                }


                addBtn.disabled = true;


                addBtn.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin"></i>
            <span>Adding...</span>
        `;


                try {

                    const response =
                        await fetch(
                            CATEGORY_API, {
                                method: "POST",

                                credentials: "same-origin",

                                headers: {
                                    "Content-Type": "application/json",

                                    "Accept": "application/json"
                                },

                                body: JSON.stringify({
                                    name: name
                                })
                            }
                        );


                    const data =
                        await response.json();


                    if (
                        !response.ok ||
                        !data.success
                    ) {

                        throw new Error(
                            data.message ||
                            "Unable to add category."
                        );
                    }


                    showToast(
                        data.message ||
                        "Category added successfully."
                    );


                    categoryForm.reset();


                    await loadCategories();


                } catch (error) {

                    console.error(
                        "Add category error:",
                        error
                    );


                    showToast(
                        error.message ||
                        "Unable to add category.",
                        "error"
                    );

                } finally {

                    addBtn.disabled = false;

                    addBtn.innerHTML = `
                <i class="fa-solid fa-plus"></i>
                <span>Add Category</span>
            `;

                }

            }
        );


        /* =========================
           DELETE CATEGORY
        ========================= */

        async function deleteCategory(id) {

            if (!id) {
                return;
            }


            const confirmed =
                confirm(
                    "Are you sure you want to delete this category?"
                );


            if (!confirmed) {
                return;
            }


            try {

                const response =
                    await fetch(
                        CATEGORY_API, {
                            method: "DELETE",

                            credentials: "same-origin",

                            headers: {
                                "Content-Type": "application/json",

                                "Accept": "application/json"
                            },

                            body: JSON.stringify({
                                id: id
                            })
                        }
                    );


                const data =
                    await response.json();


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.message ||
                        "Unable to delete category."
                    );
                }


                showToast(
                    data.message ||
                    "Category deleted successfully."
                );


                await loadCategories();


            } catch (error) {

                console.error(
                    "Delete category error:",
                    error
                );


                showToast(
                    error.message ||
                    "Unable to delete category.",
                    "error"
                );

            }

        }


        /* =========================
           MOBILE SIDEBAR
        ========================= */

        const sidebar =
            document.getElementById(
                "sidebar"
            );

        const overlay =
            document.getElementById(
                "overlay"
            );

        const mobileMenu =
            document.getElementById(
                "mobileMenu"
            );


        function openSidebar() {

            sidebar.classList.add(
                "open"
            );

            overlay.classList.add(
                "show"
            );

            document.body.style.overflow =
                "hidden";
        }


        function closeSidebar() {

            sidebar.classList.remove(
                "open"
            );

            overlay.classList.remove(
                "show"
            );

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
            getInitials(
                adminName
            );


        document.getElementById(
                "sidebarAvatar"
            ).textContent =
            initials;


        document.getElementById(
                "topAvatar"
            ).textContent =
            initials;


        /* =========================
           INITIAL LOAD
        ========================= */

        loadCategories();
    </script>

</body>

</html>