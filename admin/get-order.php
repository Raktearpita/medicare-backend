<?php

/**
 * MediCare Pharmacy
 * Admin - Get Order Details API
 *
 * File:
 * backend/admin/get-order.php
 */

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';


// ---------------------------------------------------------
// Helper function
// ---------------------------------------------------------

function responseJson(
    bool $success,
    string $message,
    array $data = [],
    int $statusCode = 200
): never {
    http_response_code($statusCode);

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $data
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


// ---------------------------------------------------------
// Admin authentication
// ---------------------------------------------------------

if (
    empty($_SESSION['admin_id']) ||
    ($_SESSION['admin_logged_in'] ?? false) !== true ||
    ($_SESSION['login_type'] ?? '') !== 'admin'
) {
    responseJson(
        false,
        'Admin login required.',
        [],
        401
    );
}

$adminId = (int) $_SESSION['admin_id'];


// ---------------------------------------------------------
// Verify admin account
// ---------------------------------------------------------

$adminSql = "
    SELECT id, full_name, email, phone, status
    FROM admins
    WHERE id = ?
    LIMIT 1
";

$adminStmt = $conn->prepare($adminSql);

if (!$adminStmt) {
    responseJson(
        false,
        'Unable to verify admin account.',
        [],
        500
    );
}

$adminStmt->bind_param('i', $adminId);
$adminStmt->execute();

$adminResult = $adminStmt->get_result();
$admin = $adminResult->fetch_assoc();

$adminStmt->close();

if (!$admin) {
    responseJson(
        false,
        'Admin account not found.',
        [],
        401
    );
}

if ($admin['status'] !== 'active') {
    responseJson(
        false,
        'Admin account is inactive.',
        [],
        403
    );
}


// ---------------------------------------------------------
// Only GET requests are allowed
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responseJson(
        false,
        'Only GET requests are allowed.',
        [],
        405
    );
}


// ---------------------------------------------------------
// Get order ID
// ---------------------------------------------------------

$orderId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$orderId) {
    $orderId = filter_input(
        INPUT_GET,
        'order_id',
        FILTER_VALIDATE_INT
    );
}

if (!$orderId || $orderId <= 0) {
    responseJson(
        false,
        'Valid order ID is required.',
        [],
        400
    );
}


// ---------------------------------------------------------
// Get order details
// ---------------------------------------------------------

$orderSql = "
    SELECT
        o.id,
        o.user_id,
        o.order_number,
        o.status,
        o.payment_method,
        o.payment_status,
        o.subtotal,
        o.delivery_charge,
        o.total_amount,
        o.full_name,
        o.phone,
        o.email,
        o.address,
        o.city,
        o.state,
        o.pincode,
        o.landmark,
        o.created_at,
        o.updated_at,

        u.full_name AS customer_name,
        u.email AS customer_email,
        u.phone AS customer_phone

    FROM orders o

    LEFT JOIN users u
        ON u.id = o.user_id

    WHERE o.id = ?

    LIMIT 1
";

$orderStmt = $conn->prepare($orderSql);

if (!$orderStmt) {
    responseJson(
        false,
        'Unable to prepare order query.',
        [],
        500
    );
}

$orderStmt->bind_param('i', $orderId);
$orderStmt->execute();

$orderResult = $orderStmt->get_result();
$order = $orderResult->fetch_assoc();

$orderStmt->close();

if (!$order) {
    responseJson(
        false,
        'Order not found.',
        [],
        404
    );
}


// ---------------------------------------------------------
// Get order items
// ---------------------------------------------------------

$itemsSql = "
    SELECT
        oi.id,
        oi.order_id,
        oi.medicine_id,
        oi.medicine_name,
        oi.price,
        oi.quantity,
        oi.total_price,

        m.name AS current_medicine_name,
        m.image AS medicine_image

    FROM order_items oi

    LEFT JOIN medicines m
        ON m.id = oi.medicine_id

    WHERE oi.order_id = ?

    ORDER BY oi.id ASC
";

$itemsStmt = $conn->prepare($itemsSql);

if (!$itemsStmt) {
    responseJson(
        false,
        'Unable to prepare order items query.',
        [],
        500
    );
}

$itemsStmt->bind_param('i', $orderId);
$itemsStmt->execute();

$itemsResult = $itemsStmt->get_result();

$items = [];

while ($item = $itemsResult->fetch_assoc()) {

    $image = $item['medicine_image'] ?? '';

    if ($image !== '') {
        $image = ltrim($image, '/');

        if (
            !str_starts_with($image, 'http://') &&
            !str_starts_with($image, 'https://')
        ) {
            $image = '../../' . $image;
        }
    }

    $items[] = [
        'id' => (int) $item['id'],
        'order_id' => (int) $item['order_id'],
        'medicine_id' => (int) $item['medicine_id'],
        'medicine_name' =>
        $item['medicine_name']
            ?: ($item['current_medicine_name'] ?? 'Medicine'),
        'price' => (float) $item['price'],
        'quantity' => (int) $item['quantity'],
        'total_price' => (float) $item['total_price'],
        'image' => $image
    ];
}

$itemsStmt->close();


// ---------------------------------------------------------
// Get tracking information
// ---------------------------------------------------------

$trackingSql = "
    SELECT
        id,
        order_id,
        status,
        title,
        description,
        created_at
    FROM order_tracking
    WHERE order_id = ?
    ORDER BY created_at ASC, id ASC
";

$trackingStmt = $conn->prepare($trackingSql);

$tracking = [];

if ($trackingStmt) {

    $trackingStmt->bind_param('i', $orderId);
    $trackingStmt->execute();

    $trackingResult = $trackingStmt->get_result();

    while ($track = $trackingResult->fetch_assoc()) {
        $tracking[] = [
            'id' => (int) $track['id'],
            'order_id' => (int) $track['order_id'],
            'status' => $track['status'],
            'title' => $track['title'],
            'description' => $track['description'],
            'created_at' => $track['created_at']
        ];
    }

    $trackingStmt->close();
}


// ---------------------------------------------------------
// Customer information
// ---------------------------------------------------------

$customerName =
    $order['full_name']
    ?: ($order['customer_name'] ?? '');

$customerEmail =
    $order['email']
    ?: ($order['customer_email'] ?? '');

$customerPhone =
    $order['phone']
    ?: ($order['customer_phone'] ?? '');


// ---------------------------------------------------------
// Prepare order response
// ---------------------------------------------------------

$orderData = [
    'id' => (int) $order['id'],
    'user_id' => (int) $order['user_id'],

    'order_number' =>
    $order['order_number']
        ?: ('ORD-' . str_pad(
            (string) $order['id'],
            6,
            '0',
            STR_PAD_LEFT
        )),

    'status' => $order['status'],

    'payment_method' => $order['payment_method'],
    'payment_status' => $order['payment_status'],

    'subtotal' => (float) $order['subtotal'],
    'delivery_charge' => (float) $order['delivery_charge'],
    'total_amount' => (float) $order['total_amount'],

    'customer' => [
        'name' => $customerName,
        'email' => $customerEmail,
        'phone' => $customerPhone
    ],

    'shipping_address' => [
        'full_name' => $order['full_name'],
        'phone' => $order['phone'],
        'email' => $order['email'],
        'address' => $order['address'],
        'city' => $order['city'],
        'state' => $order['state'],
        'pincode' => $order['pincode'],
        'landmark' => $order['landmark']
    ],

    'items' => $items,

    'tracking' => $tracking,

    'created_at' => $order['created_at'],
    'updated_at' => $order['updated_at']
];


// ---------------------------------------------------------
// Success response
// ---------------------------------------------------------

responseJson(
    true,
    'Order details loaded successfully.',
    [
        'order' => $orderData
    ]
);
