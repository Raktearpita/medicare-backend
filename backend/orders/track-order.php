<?php

/**
 * MediCare Pharmacy
 * Track Order API
 *
 * File:
 * backend/orders/track-order.php
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
        'message' => 'Please login to track your order.',
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
    'order_id',
    FILTER_VALIDATE_INT
);


// Support ?id=123 as well

if (
    $orderId === false ||
    $orderId === null ||
    $orderId <= 0
) {

    $orderId = filter_input(
        INPUT_GET,
        'id',
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
// Verify Order Belongs to User
// ---------------------------------------------------------

$orderSql = "
    SELECT
        id,
        order_number,
        status,
        created_at
    FROM orders
    WHERE id = ?
      AND user_id = ?
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

if (!$orderStmt->execute()) {

    $orderStmt->close();

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to verify order.'
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
// Tracking Status Definitions
// ---------------------------------------------------------

$statuses = [
    'Order Placed',
    'Confirmed',
    'Packed',
    'Shipped',
    'Out for Delivery',
    'Delivered'
];


// ---------------------------------------------------------
// Get Tracking Records
// ---------------------------------------------------------

$tracking = [];

$trackingSql = "
    SELECT
        id,
        status,
        description,
        created_at
    FROM order_tracking
    WHERE order_id = ?
    ORDER BY id ASC
";

$trackingStmt = $conn->prepare($trackingSql);


// ---------------------------------------------------------
// Process Tracking Table
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
            $row =
            $trackingResult->fetch_assoc()
        ) {

            $tracking[] = [
                'id' =>
                (int)$row['id'],

                'status' =>
                $row['status'],

                'description' =>
                $row['description'] ?? '',

                'created_at' =>
                $row['created_at'] ?? null
            ];
        }
    }

    $trackingStmt->close();
}


// ---------------------------------------------------------
// Current Order Status
// ---------------------------------------------------------

$currentStatus = trim(
    (string)($order['status'] ?? 'Order Placed')
);


// ---------------------------------------------------------
// Handle Cancelled Orders
// ---------------------------------------------------------

if (
    strtolower($currentStatus) === 'cancelled' ||
    strtolower($currentStatus) === 'canceled'
) {

    http_response_code(200);

    echo json_encode([
        'success' => true,
        'message' => 'Order tracking loaded successfully.',

        'order' => [
            'id' =>
            (int)$order['id'],

            'order_number' =>
            $order['order_number']
                ?? ('#' . $order['id']),

            'status' =>
            'Cancelled',

            'created_at' =>
            $order['created_at']
                ?? null
        ],

        'tracking' => [
            [
                'id' => null,

                'status' => 'Cancelled',

                'description' =>
                'This order has been cancelled.',

                'created_at' =>
                $order['created_at']
                    ?? null,

                'completed' => true,

                'current' => true
            ]
        ]
    ]);

    exit;
}


// ---------------------------------------------------------
// Find Current Status Position
// ---------------------------------------------------------

$currentIndex = array_search(
    $currentStatus,
    $statuses,
    true
);


// ---------------------------------------------------------
// Handle Unknown Status
// ---------------------------------------------------------

if ($currentIndex === false) {

    /*
     * If the database contains a status that is not
     * part of the standard timeline, treat the order
     * as being at the first stage.
     */

    $currentIndex = 0;
}


// ---------------------------------------------------------
// Create Timeline
// ---------------------------------------------------------

$timeline = [];


// ---------------------------------------------------------
// Build Standard Timeline
// ---------------------------------------------------------

foreach ($statuses as $index => $status) {

    $completed = $index < $currentIndex;

    $current = $index === $currentIndex;

    $statusDate = null;

    $description = '';


    // -----------------------------------------------------
    // Find Actual Tracking Record
    // -----------------------------------------------------

    foreach ($tracking as $record) {

        if (
            strtolower(
                trim($record['status'])
            ) === strtolower($status)
        ) {

            $statusDate =
                $record['created_at'];

            $description =
                $record['description'];

            break;
        }
    }


    // -----------------------------------------------------
    // Default Descriptions
    // -----------------------------------------------------

    if ($description === '') {

        switch ($status) {

            case 'Order Placed':
                $description =
                    'Your order has been placed successfully.';
                break;

            case 'Confirmed':
                $description =
                    'Your order has been confirmed.';
                break;

            case 'Packed':
                $description =
                    'Your medicines have been packed.';
                break;

            case 'Shipped':
                $description =
                    'Your order has been shipped.';
                break;

            case 'Out for Delivery':
                $description =
                    'Your order is out for delivery.';
                break;

            case 'Delivered':
                $description =
                    'Your order has been delivered.';
                break;
        }
    }


    // -----------------------------------------------------
    // If Previous Stage Has No Record
    // -----------------------------------------------------

    if (
        $completed &&
        $statusDate === null
    ) {

        /*
         * We know the stage was completed from the
         * current order status, but don't have an
         * exact timestamp for it.
         */
        $statusDate = null;
    }


    $timeline[] = [
        'id' => null,

        'status' => $status,

        'description' => $description,

        'created_at' => $statusDate,

        'completed' => $completed,

        'current' => $current,

        'step' => $index + 1
    ];
}


// ---------------------------------------------------------
// Add Actual Tracking Records Not in Standard List
// ---------------------------------------------------------

$knownStatuses = array_map(
    'strtolower',
    $statuses
);

foreach ($tracking as $record) {

    $recordStatus = trim(
        (string)$record['status']
    );

    if (
        !in_array(
            strtolower($recordStatus),
            $knownStatuses,
            true
        )
    ) {

        $timeline[] = [
            'id' =>
            $record['id'],

            'status' =>
            $recordStatus,

            'description' =>
            $record['description'],

            'created_at' =>
            $record['created_at'],

            'completed' => true,

            'current' =>
            strtolower($recordStatus)
                === strtolower($currentStatus),

            'step' => count($timeline) + 1
        ];
    }
}


// ---------------------------------------------------------
// Calculate Progress
// ---------------------------------------------------------

$totalSteps = count($statuses);

$progressPercent = round(
    (($currentIndex + 1) / $totalSteps) * 100
);


// ---------------------------------------------------------
// Successful Response
// ---------------------------------------------------------

http_response_code(200);

echo json_encode([
    'success' => true,

    'message' =>
    'Order tracking loaded successfully.',

    'order' => [
        'id' =>
        (int)$order['id'],

        'order_number' =>
        $order['order_number']
            ?? ('#' . $order['id']),

        'status' =>
        $currentStatus,

        'created_at' =>
        $order['created_at']
            ?? null
    ],

    'tracking' =>
    $timeline,

    'progress' => [
        'current_step' =>
        $currentIndex + 1,

        'total_steps' =>
        $totalSteps,

        'percentage' =>
        $progressPercent
    ]
]);

exit;
