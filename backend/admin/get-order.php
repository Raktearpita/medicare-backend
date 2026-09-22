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


// =========================================================
// JSON ERROR HANDLER
// =========================================================

set_error_handler(function (
    int $severity,
    string $message,
    string $file,
    int $line
): bool {

    if (!(error_reporting() & $severity)) {
        return false;
    }

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Server error while loading order.',
        'error' => $message
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
});


// =========================================================
// ONLY GET
// =========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use GET.'
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
// ORDER ID
// =========================================================

$orderId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$orderId || $orderId <= 0) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid order ID.'
    ]);

    exit;
}


// =========================================================
// CHECK COLUMN EXISTS
// =========================================================

function columnExists(
    mysqli $conn,
    string $table,
    string $column
): bool {

    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);

    $sql = "
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '$table'
          AND COLUMN_NAME = '$column'
    ";

    $result = $conn->query($sql);

    if (!$result) {
        return false;
    }

    $row = $result->fetch_row();

    return ((int) ($row[0] ?? 0)) > 0;
}


// =========================================================
// ORDER COLUMNS
// =========================================================

$orderColumns = [
    'id',
    'user_id',
    'order_number',
    'total_amount',
    'status',
    'payment_method',
    'payment_status',
    'full_name',
    'city',
    'state',
    'created_at'
];


// Optional columns
$optionalOrderColumns = [
    'phone',
    'email',
    'address',
    'pincode',
    'landmark',
    'shipping_address',
    'delivery_charge',
    'subtotal'
];


$selectColumns = $orderColumns;


foreach ($optionalOrderColumns as $column) {

    if (columnExists(
        $conn,
        'orders',
        $column
    )) {

        $selectColumns[] = $column;
    }
}


// =========================================================
// GET ORDER
// =========================================================

$selectSql = implode(
    ', ',
    array_map(
        fn($column) => 'o.' . $column,
        $selectColumns
    )
);


$orderSql = "
    SELECT
        $selectSql

    FROM orders o

    WHERE o.id = $orderId

    LIMIT 1
";


$orderResult = $conn->query($orderSql);


if (!$orderResult) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load order.',
        'error' => $conn->error
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
}


if ($orderResult->num_rows !== 1) {

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Order not found.'
    ]);

    exit;
}


$orderRow = $orderResult->fetch_assoc();


// =========================================================
// BASIC ORDER DATA
// =========================================================

$id =
    (int) ($orderRow['id'] ?? 0);

$userId =
    (int) ($orderRow['user_id'] ?? 0);

$orderNumber =
    $orderRow['order_number']
    ?? ('ORD-' . $id);

$totalAmount =
    round(
        (float) ($orderRow['total_amount'] ?? 0),
        2
    );

$status =
    $orderRow['status']
    ?? '';

$paymentMethod =
    $orderRow['payment_method']
    ?? '';

$paymentStatus =
    $orderRow['payment_status']
    ?? '';

$fullName =
    $orderRow['full_name']
    ?? '';

$city =
    $orderRow['city']
    ?? '';

$state =
    $orderRow['state']
    ?? '';

$createdAt =
    $orderRow['created_at']
    ?? '';


// =========================================================
// OPTIONAL ORDER DATA
// =========================================================

$phone =
    $orderRow['phone']
    ?? '';

$email =
    $orderRow['email']
    ?? '';

$address =
    $orderRow['address']
    ?? '';

$pincode =
    $orderRow['pincode']
    ?? '';

$landmark =
    $orderRow['landmark']
    ?? '';


// =========================================================
// CUSTOMER EMAIL FROM USERS
// =========================================================

if ($userId > 0) {

    $userSql = "
        SELECT email
        FROM users
        WHERE id = $userId
        LIMIT 1
    ";

    $userResult = $conn->query($userSql);

    if ($userResult && $userResult->num_rows === 1) {

        $userRow =
            $userResult->fetch_assoc();

        if (
            empty($email) &&
            isset($userRow['email'])
        ) {

            $email =
                $userRow['email'];
        }
    }
}


// =========================================================
// CUSTOMER
// =========================================================

$customer = [

    'name' =>
        $fullName ?: 'Customer',

    'email' =>
        $email,

    'phone' =>
        $phone
];


// =========================================================
// ORDER ITEMS
// =========================================================

$items = [];

$subtotal = 0.0;


$itemSql = "
    SELECT

        oi.id,
        oi.order_id,
        oi.medicine_id,
        oi.quantity,
        oi.price,

        m.name AS medicine_name,
        m.image

    FROM order_items oi

    LEFT JOIN medicines m
        ON m.id = oi.medicine_id

    WHERE oi.order_id = $orderId

    ORDER BY oi.id ASC
";


$itemResult = $conn->query($itemSql);


if (!$itemResult) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load order medicines.',
        'error' => $conn->error
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
}


while ($itemRow = $itemResult->fetch_assoc()) {

    $itemId =
        (int) ($itemRow['id'] ?? 0);

    $itemOrderId =
        (int) ($itemRow['order_id'] ?? 0);

    $medicineId =
        (int) ($itemRow['medicine_id'] ?? 0);

    $quantity =
        (int) ($itemRow['quantity'] ?? 0);

    $price =
        (float) ($itemRow['price'] ?? 0);

    $itemTotal =
        $quantity * $price;

    $subtotal += $itemTotal;


    $items[] = [

        'id' =>
            $itemId,

        'order_id' =>
            $itemOrderId,

        'medicine_id' =>
            $medicineId,

        'medicine_name' =>
            $itemRow['medicine_name']
            ?? 'Medicine',

        'name' =>
            $itemRow['medicine_name']
            ?? 'Medicine',

        'image' =>
            $itemRow['image']
            ?? '',

        'quantity' =>
            $quantity,

        'price' =>
            round(
                $price,
                2
            ),

        'total_price' =>
            round(
                $itemTotal,
                2
            ),

        'subtotal' =>
            round(
                $itemTotal,
                2
            )
    ];
}


// =========================================================
// SUBTOTAL / DELIVERY
// =========================================================

if (
    isset($orderRow['subtotal']) &&
    is_numeric($orderRow['subtotal'])
) {

    $orderSubtotal =
        round(
            (float) $orderRow['subtotal'],
            2
        );

} else {

    $orderSubtotal =
        round(
            $subtotal,
            2
        );
}


if (
    isset($orderRow['delivery_charge']) &&
    is_numeric($orderRow['delivery_charge'])
) {

    $deliveryCharge =
        round(
            (float) $orderRow['delivery_charge'],
            2
        );

} else {

    $deliveryCharge =
        $totalAmount - $orderSubtotal;

    if ($deliveryCharge < 0) {
        $deliveryCharge = 0;
    }

    $deliveryCharge =
        round(
            $deliveryCharge,
            2
        );
}


// =========================================================
// SHIPPING ADDRESS
// =========================================================

$shippingAddress = [

    'full_name' =>
        $fullName,

    'phone' =>
        $phone,

    'email' =>
        $email,

    'address' =>
        $address,

    'city' =>
        $city,

    'state' =>
        $state,

    'pincode' =>
        $pincode,

    'landmark' =>
        $landmark
];


// =========================================================
// TRACKING
// =========================================================

$tracking = [];


$statusValue =
    strtolower(
        trim(
            $status
        )
    );


$trackingOrder = [

    'order placed' => [
        'title' => 'Order Placed',
        'description' =>
            'Your order has been placed successfully.'
    ],

    'confirmed' => [
        'title' => 'Order Confirmed',
        'description' =>
            'The order has been confirmed.'
    ],

    'packed' => [
        'title' => 'Order Packed',
        'description' =>
            'The medicines have been packed.'
    ],

    'shipped' => [
        'title' => 'Order Shipped',
        'description' =>
            'The order has been handed over for delivery.'
    ],

    'out for delivery' => [
        'title' => 'Out for Delivery',
        'description' =>
            'The order is on the way to the customer.'
    ],

    'delivered' => [
        'title' => 'Delivered',
        'description' =>
            'The order has been delivered successfully.'
    ]
];


if ($statusValue === 'cancelled') {

    $tracking[] = [

        'status' =>
            'cancelled',

        'title' =>
            'Order Cancelled',

        'description' =>
            'This order has been cancelled.'
    ];

} else {

    $statusKeys =
        array_keys(
            $trackingOrder
        );


    $currentIndex =
        array_search(
            $statusValue,
            $statusKeys,
            true
        );


    if ($currentIndex === false) {
        $currentIndex = 0;
    }


    foreach (
        $statusKeys as $index => $trackStatus
    ) {

        if ($index > $currentIndex) {
            break;
        }


        $tracking[] = [

            'status' =>
                $trackStatus,

            'title' =>
                $trackingOrder[$trackStatus]['title'],

            'description' =>
                $trackingOrder[$trackStatus]['description']
        ];
    }
}


// =========================================================
// FINAL RESPONSE
// =========================================================

$order = [

    'id' =>
        $id,

    'user_id' =>
        $userId,

    'order_number' =>
        $orderNumber,

    'created_at' =>
        $createdAt,

    'status' =>
        $status,

    'payment_method' =>
        $paymentMethod,

    'payment_status' =>
        $paymentStatus,

    'subtotal' =>
        $orderSubtotal,

    'delivery_charge' =>
        $deliveryCharge,

    'total_amount' =>
        $totalAmount,

    'customer' =>
        $customer,

    'shipping_address' =>
        $shippingAddress,

    'items' =>
        $items,

    'tracking' =>
        $tracking
];


http_response_code(200);


echo json_encode(

    [

        'success' =>
            true,

        'message' =>
            'Order details loaded successfully.',

        'order' =>
            $order

    ],

    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

exit;