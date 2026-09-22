<?php

/**
 * MediCare Pharmacy
 * Update Order Status API
 *
 * File:
 * backend/orders/update-order-status.php
 */

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';


// =========================================================
// HELPER
// =========================================================

function sendResponse(
    bool $success,
    string $message,
    int $statusCode = 200,
    array $extra = []
): void {

    http_response_code($statusCode);

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}


// =========================================================
// ONLY POST
// =========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Allow: POST');

    sendResponse(
        false,
        'Method not allowed. Please use POST.',
        405
    );
}


// =========================================================
// ADMIN LOGIN
// =========================================================

$isAdmin =
    isset($_SESSION['admin_id']) &&
    ($_SESSION['admin_logged_in'] ?? false) === true;


if (!$isAdmin) {

    sendResponse(
        false,
        'Admin login required.',
        401
    );
}


// =========================================================
// READ REQUEST
// =========================================================

$rawInput = file_get_contents('php://input');

$input = json_decode(
    $rawInput,
    true
);


if (!is_array($input)) {
    $input = $_POST;
}


// =========================================================
// ORDER ID
// =========================================================

$orderId = filter_var(
    $input['order_id'] ?? null,
    FILTER_VALIDATE_INT
);


if (
    $orderId === false ||
    $orderId === null ||
    $orderId < 1
) {

    sendResponse(
        false,
        'Invalid order ID.',
        400
    );
}


// =========================================================
// REQUESTED STATUS
// =========================================================

$status = trim(
    (string)($input['status'] ?? '')
);


// =========================================================
// ALLOWED STATUS
// =========================================================

$allowedStatuses = [

    'Pending',
    'Accepted',
    'Processing',
    'Shipped',
    'Delivered',
    'Cancelled'

];


// =========================================================
// NORMALIZE REQUESTED STATUS
// =========================================================

$statusKey = strtolower($status);

$statusKey = preg_replace(
    '/\s+/',
    ' ',
    $statusKey
);


$statusMap = [

    'pending' =>
    'Pending',

    'order placed' =>
    'Pending',

    'accepted' =>
    'Accepted',

    'processing' =>
    'Processing',

    'shipped' =>
    'Shipped',

    'delivered' =>
    'Delivered',

    'cancelled' =>
    'Cancelled',

    'canceled' =>
    'Cancelled'

];


$status =
    $statusMap[$statusKey] ?? '';


if ($status === '') {

    sendResponse(
        false,
        'Invalid order status.',
        400,
        [
            'allowed_statuses' =>
            $allowedStatuses
        ]
    );
}


// =========================================================
// GET CURRENT ORDER
// =========================================================

$checkSql = "
    SELECT
        id,
        status
    FROM orders
    WHERE id = ?
    LIMIT 1
";


$checkStmt =
    $conn->prepare($checkSql);


if (!$checkStmt) {

    sendResponse(
        false,
        'Unable to prepare order query.',
        500
    );
}


$checkStmt->bind_param(
    'i',
    $orderId
);


if (!$checkStmt->execute()) {

    $checkStmt->close();

    sendResponse(
        false,
        'Unable to check order.',
        500
    );
}


$result =
    $checkStmt->get_result();


$order =
    $result->fetch_assoc();


$checkStmt->close();


if (!$order) {

    sendResponse(
        false,
        'Order not found.',
        404
    );
}


// =========================================================
// CURRENT STATUS
// =========================================================

$rawCurrentStatus = trim(
    (string)($order['status'] ?? '')
);


$currentStatusKey =
    strtolower($rawCurrentStatus);


$currentStatusKey =
    preg_replace(
        '/\s+/',
        ' ',
        $currentStatusKey
    );


// ---------------------------------------------------------
// Normalize old statuses
// ---------------------------------------------------------

if (
    $currentStatusKey === '' ||
    $currentStatusKey === 'order placed' ||
    $currentStatusKey === 'pending'
) {

    $currentStatus = 'Pending';
} elseif (
    isset($statusMap[$currentStatusKey])
) {

    $currentStatus =
        $statusMap[$currentStatusKey];
} else {

    /*
     * Unknown old status.
     * Treat it as Pending so admin can manage it.
     */

    $currentStatus = 'Pending';
}


// =========================================================
// SAME STATUS
// =========================================================

if ($currentStatus === $status) {

    sendResponse(
        true,
        "Order #{$orderId} is already {$status}.",
        200,
        [
            'order_id' =>
            $orderId,

            'old_status' =>
            $currentStatus,

            'new_status' =>
            $status,

            'updated' =>
            false
        ]
    );
}


// =========================================================
// FINAL STATUS PROTECTION
// =========================================================

if ($currentStatus === 'Delivered') {

    sendResponse(
        false,
        'A delivered order cannot be changed.',
        400,
        [
            'current_status' =>
            $currentStatus
        ]
    );
}


if ($currentStatus === 'Cancelled') {

    sendResponse(
        false,
        'A cancelled order cannot be changed.',
        400,
        [
            'current_status' =>
            $currentStatus
        ]
    );
}


// =========================================================
// START TRANSACTION
// =========================================================

$conn->begin_transaction();


try {

    // =====================================================
    // UPDATE ORDER
    // =====================================================

    $updateSql = "
        UPDATE orders
        SET
            status = ?
        WHERE id = ?
        LIMIT 1
    ";


    $updateStmt =
        $conn->prepare($updateSql);


    if (!$updateStmt) {

        throw new Exception(
            'Unable to prepare order status update.'
        );
    }


    $updateStmt->bind_param(
        'si',
        $status,
        $orderId
    );


    if (!$updateStmt->execute()) {

        $error =
            $updateStmt->error;

        $updateStmt->close();

        throw new Exception(
            'Unable to update order status. ' .
                $error
        );
    }


    $affectedRows =
        $updateStmt->affected_rows;


    $updateStmt->close();


    // =====================================================
    // UPDATE TRACKING
    // =====================================================

    $tableResult =
        $conn->query(
            "SHOW TABLES LIKE 'order_tracking'"
        );


    if (
        $tableResult &&
        $tableResult->num_rows > 0
    ) {

        $trackingSql = "
            INSERT INTO order_tracking
            (
                order_id,
                status,
                description
            )
            VALUES
            (
                ?,
                ?,
                ?
            )
        ";


        $trackingStmt =
            $conn->prepare(
                $trackingSql
            );


        if ($trackingStmt) {

            $description =
                "Order status changed from {$currentStatus} to {$status} by admin.";


            $trackingStmt->bind_param(
                'iss',
                $orderId,
                $status,
                $description
            );


            /*
             * Tracking failure should not
             * prevent the main order status
             * from being updated.
             */

            $trackingStmt->execute();

            $trackingStmt->close();
        }
    }


    // =====================================================
    // COMMIT
    // =====================================================

    $conn->commit();


    // =====================================================
    // SUCCESS
    // =====================================================

    sendResponse(
        true,
        "Order #{$orderId} status changed to {$status}.",
        200,
        [
            'order_id' =>
            $orderId,

            'old_status' =>
            $currentStatus,

            'new_status' =>
            $status,

            'updated' =>
            true
        ]
    );
} catch (Throwable $e) {

    // =====================================================
    // ROLLBACK
    // =====================================================

    $conn->rollback();


    error_log(
        'Update Order Status Error: ' .
            $e->getMessage()
    );


    sendResponse(
        false,
        'Unable to update order status. Please try again.',
        500
    );
}
