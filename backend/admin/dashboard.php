<?php

/**
 * MediCare Pharmacy
 * Admin Dashboard API
 *
 * File:
 * backend/admin/dashboard.php
 */

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';


// =========================================================
// ONLY GET REQUESTS
// =========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}


// =========================================================
// ADMIN AUTHENTICATION
// =========================================================

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Admin authentication required.',
        'login_required' => true
    ]);

    exit;
}


$adminId = (int) $_SESSION['admin_id'];

if ($adminId <= 0) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid admin session.',
        'login_required' => true
    ]);

    exit;
}


// =========================================================
// GET SINGLE NUMBER
// =========================================================

function getCount(mysqli $conn, string $sql): int
{
    $result = $conn->query($sql);

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_row();

    return (int) ($row[0] ?? 0);
}


// =========================================================
// GET SINGLE DECIMAL VALUE
// =========================================================

function getAmount(mysqli $conn, string $sql): float
{
    $result = $conn->query($sql);

    if (!$result) {
        return 0.0;
    }

    $row = $result->fetch_row();

    return round((float) ($row[0] ?? 0), 2);
}


// =========================================================
// TOTAL REGISTERED USERS
// =========================================================

$totalUsers = getCount(
    $conn,
    "
        SELECT COUNT(*)
        FROM users
    "
);


// =========================================================
// TOTAL ACTIVE MEDICINES
// =========================================================

$totalMedicines = getCount(
    $conn,
    "
        SELECT COUNT(*)
        FROM medicines
    "
);


// =========================================================
// TOTAL ORDERS
// =========================================================

$totalOrders = getCount(
    $conn,
    "
        SELECT COUNT(*)
        FROM orders
    "
);


// =========================================================
// TOTAL REVENUE
// =========================================================

$totalRevenue = getAmount(
    $conn,
    "
        SELECT COALESCE(
            SUM(total_amount),
            0
        )
        FROM orders
        WHERE status <> 'Cancelled'
    "
);


// =========================================================
// TODAY'S ORDERS
// =========================================================

$todayOrders = getCount(
    $conn,
    "
        SELECT COUNT(*)
        FROM orders
        WHERE DATE(created_at) = CURDATE()
    "
);


// =========================================================
// PENDING ORDERS
// =========================================================

$pendingOrders = getCount(
    $conn,
    "
        SELECT COUNT(*)
        FROM orders
        WHERE status IN (
            'Order Placed',
            'Confirmed',
            'Packed',
            'Shipped',
            'Out for Delivery'
        )
    "
);


// =========================================================
// LOW STOCK
// =========================================================

$lowStockCount = getCount(
    $conn,
    "
        SELECT COUNT(*)
        FROM medicines
        WHERE stock BETWEEN 1 AND 10
    "
);


// =========================================================
// OUT OF STOCK
// =========================================================

$outOfStockCount = getCount(
    $conn,
    "
        SELECT COUNT(*)
        FROM medicines
        WHERE stock = 0
    "
);


// =========================================================
// TOTAL CATEGORIES
// =========================================================

$totalCategories = getCount(
    $conn,
    "
        SELECT COUNT(*)
        FROM categories
    "
);


// =========================================================
// TOTAL STOCK
// =========================================================

$totalStock = getCount(
    $conn,
    "
        SELECT COALESCE(
            SUM(stock),
            0
        )
        FROM medicines
    "
);


// =========================================================
// RECENT ORDERS
// =========================================================

$recentOrders = [];

$sql = "
    SELECT
        o.id,
        o.order_number,
        o.total_amount,
        o.status,
        o.payment_method,
        o.payment_status,
        o.full_name,
        o.city,
        o.state,
        o.created_at,

        COUNT(oi.id) AS unique_items,

        COALESCE(
            SUM(oi.quantity),
            0
        ) AS total_items

    FROM orders o

    LEFT JOIN order_items oi
        ON oi.order_id = o.id

    GROUP BY
        o.id,
        o.order_number,
        o.total_amount,
        o.status,
        o.payment_method,
        o.payment_status,
        o.full_name,
        o.city,
        o.state,
        o.created_at

    ORDER BY o.created_at DESC

    LIMIT 6
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $recentOrders[] = [

            'id' => (int) $row['id'],

            'order_id' => (int) $row['id'],

            'order_number' =>
            $row['order_number'] ?? '',

            'total_amount' =>
            round(
                (float) ($row['total_amount'] ?? 0),
                2
            ),

            'total' =>
            round(
                (float) ($row['total_amount'] ?? 0),
                2
            ),

            'status' =>
            $row['status'] ?? '',

            'payment_method' =>
            $row['payment_method'] ?? '',

            'payment_status' =>
            $row['payment_status'] ?? '',

            'full_name' =>
            $row['full_name'] ?? '',

            'customer_name' =>
            $row['full_name'] ?? '',

            'city' =>
            $row['city'] ?? '',

            'state' =>
            $row['state'] ?? '',

            'unique_items' =>
            (int) ($row['unique_items'] ?? 0),

            'total_items' =>
            (int) ($row['total_items'] ?? 0),

            'created_at' =>
            $row['created_at'] ?? ''
        ];
    }
}


// =========================================================
// LOW STOCK MEDICINES
// =========================================================

$lowStock = [];

$sql = "
    SELECT
        m.id,
        m.name,
        m.stock,
        m.price,
        m.image,
        c.name AS category

    FROM medicines m

    LEFT JOIN categories c
        ON c.id = m.category_id

    WHERE m.stock <= 10

    ORDER BY
        m.stock ASC,
        m.name ASC

    LIMIT 6
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $stock = (int) $row['stock'];

        if ($stock === 0) {
            $stockStatus = 'out_of_stock';
        } elseif ($stock <= 5) {
            $stockStatus = 'critical';
        } else {
            $stockStatus = 'low';
        }

        $lowStock[] = [

            'id' =>
            (int) $row['id'],

            'name' =>
            $row['name'] ?? '',

            'stock' =>
            $stock,

            'price' =>
            round(
                (float) ($row['price'] ?? 0),
                2
            ),

            'image' =>
            $row['image'] ?? '',

            'category' =>
            $row['category'] ?? '',

            'stock_status' =>
            $stockStatus
        ];
    }
}


// =========================================================
// TOP SELLING MEDICINES
// =========================================================

$topSelling = [];

$sql = "
    SELECT
        m.id,
        m.name,

        COALESCE(
            SUM(oi.quantity),
            0
        ) AS units_sold,

        COALESCE(
            SUM(
                oi.quantity * oi.price
            ),
            0
        ) AS sales_amount

    FROM order_items oi

    INNER JOIN medicines m
        ON m.id = oi.medicine_id

    INNER JOIN orders o
        ON o.id = oi.order_id

    WHERE o.status <> 'Cancelled'

    GROUP BY
        m.id,
        m.name

    ORDER BY
        units_sold DESC

    LIMIT 5
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $topSelling[] = [

            'id' =>
            (int) $row['id'],

            'name' =>
            $row['name'] ?? '',

            'units_sold' =>
            (int) $row['units_sold'],

            'sales_amount' =>
            round(
                (float) $row['sales_amount'],
                2
            )
        ];
    }
}


// =========================================================
// ORDER STATUS SUMMARY
// =========================================================

$orderStatusSummary = [];

$sql = "
    SELECT
        status,
        COUNT(*) AS total

    FROM orders

    GROUP BY status

    ORDER BY total DESC
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $orderStatusSummary[] = [

            'status' =>
            $row['status'] ?? '',

            'total' =>
            (int) $row['total']
        ];
    }
}


// =========================================================
// ADMIN INFORMATION
// =========================================================

$admin = [

    'id' => $adminId,

    'full_name' =>
    $_SESSION['admin_name'] ?? 'Administrator',

    'email' =>
    $_SESSION['admin_email'] ?? '',

    'status' => 'active'
];


// =========================================================
// FINAL RESPONSE
// =========================================================

echo json_encode(

    [

        'success' => true,

        'message' =>
        'Admin dashboard loaded successfully.',

        'admin' =>
        $admin,

        'dashboard' => [

            // Main cards

            'total_orders' =>
            $totalOrders,

            'total_revenue' =>
            $totalRevenue,

            'total_medicines' =>
            $totalMedicines,

            'total_users' =>
            $totalUsers,


            // Additional statistics

            'today_orders' =>
            $todayOrders,

            'pending_orders' =>
            $pendingOrders,

            'low_stock_count' =>
            $lowStockCount,

            'out_of_stock_count' =>
            $outOfStockCount,

            'total_categories' =>
            $totalCategories,

            'total_stock' =>
            $totalStock,


            // Lists

            'recent_orders' =>
            $recentOrders,

            'low_stock' =>
            $lowStock,

            'top_selling' =>
            $topSelling,

            'order_status_summary' =>
            $orderStatusSummary
        ]
    ],

    JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
);

exit;
