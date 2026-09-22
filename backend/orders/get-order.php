<?php

/**
 * MediCare Pharmacy
 * Get Single Order API
 *
 * File:
 * backend/orders/get-order.php
 */

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';


// ---------------------------------------------------------
// Only GET requests are allowed
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    header('Allow: GET');

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use GET.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Check Login Session
// ---------------------------------------------------------

if (!isset($_SESSION['user_id'])) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Please login to view your order.',
        'login_required' => true
    ]);

    exit;
}


$userId = (int)$_SESSION['user_id'];


// ---------------------------------------------------------
// Validate User ID
// ---------------------------------------------------------

if ($userId <= 0) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid user session.',
        'login_required' => true
    ]);

    exit;
}


// ---------------------------------------------------------
// Get Order ID
// ---------------------------------------------------------

$orderId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


// ---------------------------------------------------------
// Support order_id Parameter
// ---------------------------------------------------------

if (
    $orderId === false ||
    $orderId === null ||
    $orderId <= 0
) {

    $orderId = filter_input(
        INPUT_GET,
        'order_id',
        FILTER_VALIDATE_INT
    );
}


// ---------------------------------------------------------
// Validate Order ID
// ---------------------------------------------------------

if (
    $orderId === false ||
    $orderId === null ||
    $orderId <= 0
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'A valid order ID is required.'
    ]);

    exit;
}


$orderId = (int)$orderId;


// ---------------------------------------------------------
// Get Order
// ---------------------------------------------------------

$orderSql = "
    SELECT
        o.id,
        o.user_id,
        o.order_number,
        o.total_amount,
        o.status,
        o.payment_method,
        o.payment_status,

        o.full_name,
        o.phone,
        o.email,
        o.address,
        o.city,
        o.state,
        o.pincode,
        o.landmark,

        o.created_at

    FROM orders o

    WHERE o.id = ?
      AND o.user_id = ?

    LIMIT 1
";


$orderStmt = $conn->prepare($orderSql);

if (!$orderStmt) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to prepare order query.'
    ]);

    exit;
}


$orderStmt->bind_param(
    'ii',
    $orderId,
    $userId
);


// ---------------------------------------------------------
// Execute Order Query
// ---------------------------------------------------------

if (!$orderStmt->execute()) {

    $orderStmt->close();

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load order.'
    ]);

    exit;
}


$orderResult = $orderStmt->get_result();


// ---------------------------------------------------------
// Order Not Found
// ---------------------------------------------------------

if ($orderResult->num_rows !== 1) {

    $orderStmt->close();

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Order not found.'
    ]);

    exit;
}


$order = $orderResult->fetch_assoc();

$orderStmt->close();


// ---------------------------------------------------------
// Get Order Items
// ---------------------------------------------------------

$itemsSql = "
    SELECT
        oi.id,
        oi.order_id,
        oi.medicine_id,
        oi.quantity,
        oi.price,

        m.name,
        m.description,
        m.image,

        c.name AS category

    FROM order_items oi

    INNER JOIN medicines m
        ON m.id = oi.medicine_id

    LEFT JOIN categories c
        ON c.id = m.category_id

    WHERE oi.order_id = ?

    ORDER BY oi.id ASC
";


$itemsStmt = $conn->prepare($itemsSql);

if (!$itemsStmt) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to prepare order items query.'
    ]);

    exit;
}


$itemsStmt->bind_param(
    'i',
    $orderId
);


// ---------------------------------------------------------
// Execute Items Query
// ---------------------------------------------------------

if (!$itemsStmt->execute()) {

    $itemsStmt->close();

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load order items.'
    ]);

    exit;
}


$itemsResult = $itemsStmt->get_result();

$items = [];

$subtotal = 0.00;

$totalItems = 0;


// ---------------------------------------------------------
// Format Order Items
// ---------------------------------------------------------

while ($row = $itemsResult->fetch_assoc()) {

    $quantity = (int)$row['quantity'];

    $price = (float)$row['price'];

    $itemTotal = round(
        $quantity * $price,
        2
    );


    $subtotal += $itemTotal;

    $totalItems += $quantity;


    $items[] = [
        'id' => (int)$row['id'],

        'order_item_id' => (int)$row['id'],

        'order_id' => (int)$row['order_id'],

        'medicine_id' => (int)$row['medicine_id'],

        'name' => $row['name'],

        'medicine_name' => $row['name'],

        'description' =>
        $row['description'] ?? '',

        'category' =>
        $row['category'] ?? 'General',

        'price' => $price,

        'quantity' => $quantity,

        'image' =>
        $row['image'] ?? '',

        'item_total' => $itemTotal
    ];
}


$itemsStmt->close();


// ---------------------------------------------------------
// Calculate Delivery
// ---------------------------------------------------------

$totalAmount = (float)$order['total_amount'];

$calculatedDelivery = round(
    $totalAmount - $subtotal,
    2
);


// ---------------------------------------------------------
// Prevent Negative Delivery
// ---------------------------------------------------------

if ($calculatedDelivery < 0) {

    $calculatedDelivery = 0.00;
}


// ---------------------------------------------------------
// Get Tracking Information
// ---------------------------------------------------------

$tracking = [];

$trackingSql = "
    SELECT
        id,
        order_id,
        status,
        description,
        created_at
    FROM order_tracking
    WHERE order_id = ?
    ORDER BY id ASC
";


$trackingStmt = $conn->prepare(
    $trackingSql
);


// ---------------------------------------------------------
// Tracking Is Optional
// ---------------------------------------------------------

if ($trackingStmt) {

    $trackingStmt->bind_param(
        'i',
        $orderId
    );


    if ($trackingStmt->execute()) {

        $trackingResult =
            $trackingStmt->get_result();


        while (
            $trackingRow =
            $trackingResult->fetch_assoc()
        ) {

            $tracking[] = [
                'id' =>
                (int)$trackingRow['id'],

                'order_id' =>
                (int)$trackingRow['order_id'],

                'status' =>
                $trackingRow['status'],

                'description' =>
                $trackingRow['description'] ?? '',

                'created_at' =>
                $trackingRow['created_at'] ?? null
            ];
        }
    }


    $trackingStmt->close();
}


// ---------------------------------------------------------
// Fallback Tracking
// ---------------------------------------------------------

if (empty($tracking)) {

    $currentStatus =
        $order['status'] ?? 'Order Placed';


    $tracking[] = [
        'id' => null,

        'order_id' => $orderId,

        'status' => $currentStatus,

        'description' =>
        'Current order status.',

        'created_at' =>
        $order['created_at'] ?? null
    ];
}


// ---------------------------------------------------------
// Format Order
// ---------------------------------------------------------

$orderData = [

    'id' =>
    (int)$order['id'],

    'order_id' =>
    (int)$order['id'],

    'order_number' =>
    $order['order_number']
        ?? ('#' . $order['id']),

    'total_amount' =>
    $totalAmount,

    'total' =>
    $totalAmount,

    'subtotal' =>
    round($subtotal, 2),

    'delivery' =>
    $calculatedDelivery,

    'status' =>
    $order['status']
        ?? 'Order Placed',

    'payment_method' =>
    $order['payment_method']
        ?? '',

    'payment_status' =>
    $order['payment_status']
        ?? '',

    'created_at' =>
    $order['created_at']
        ?? null,

    'total_items' =>
    $totalItems,

    'unique_items' =>
    count($items),

    'items' =>
    $items,

    'medicines' =>
    $items,

    'delivery_address' => [

        'full_name' =>
        $order['full_name'] ?? '',

        'phone' =>
        $order['phone'] ?? '',

        'email' =>
        $order['email'] ?? '',

        'address' =>
        $order['address'] ?? '',

        'city' =>
        $order['city'] ?? '',

        'state' =>
        $order['state'] ?? '',

        'pincode' =>
        $order['pincode'] ?? '',

        'landmark' =>
        $order['landmark'] ?? ''
    ],

    'tracking' =>
    $tracking
];


// ---------------------------------------------------------
// Successful Response
// ---------------------------------------------------------

http_response_code(200);

echo json_encode([
    'success' => true,

    'message' =>
    'Order details loaded successfully.',

    'order' =>
    $orderData
]);

exit;
