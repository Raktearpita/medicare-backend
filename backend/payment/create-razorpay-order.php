<?php

/**
 * MediCare Pharmacy
 * Create Razorpay Order API
 *
 * File:
 * backend/payment/create-razorpay-order.php
 */

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/razorpay.php';


// ---------------------------------------------------------
// Only POST requests are allowed
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);
    header('Allow: POST');

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use POST.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Check Login
// ---------------------------------------------------------

if (empty($_SESSION['user_id'])) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Please login before making payment.',
        'login_required' => true
    ]);

    exit;
}

$userId = (int) $_SESSION['user_id'];

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
// Read Request
// ---------------------------------------------------------

$rawInput = file_get_contents('php://input');

$data = json_decode($rawInput, true);

if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Get Cart
// ---------------------------------------------------------
//
// IMPORTANT:
// Do not select m.name because the current medicines table
// does not contain a "name" column according to the error.
// Medicine name is not required for creating Razorpay order.
//

$cartSql = "
    SELECT
        c.medicine_id,
        c.quantity,
        m.price,
        m.stock
    FROM cart c
    INNER JOIN medicines m
        ON m.id = c.medicine_id
    WHERE c.user_id = ?
";


$cartStmt = $conn->prepare($cartSql);

if (!$cartStmt) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to prepare cart query.'
    ]);

    exit;
}


$cartStmt->bind_param(
    'i',
    $userId
);


// ---------------------------------------------------------
// Execute Cart Query
// ---------------------------------------------------------

if (!$cartStmt->execute()) {

    $cartStmt->close();

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load cart.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Bind Result
// ---------------------------------------------------------

$cartStmt->bind_result(
    $medicineId,
    $quantity,
    $price,
    $stock
);


// ---------------------------------------------------------
// Calculate Subtotal
// ---------------------------------------------------------

$subtotal = 0.00;

$cartCount = 0;


while ($cartStmt->fetch()) {

    $cartCount++;


    $medicineId = (int) $medicineId;

    $quantity = (int) $quantity;

    $price = (float) $price;

    $stock = (int) $stock;


    // -----------------------------------------------------
    // Validate Quantity
    // -----------------------------------------------------

    if ($quantity <= 0) {

        $cartStmt->close();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
            'Invalid quantity for medicine ID ' .
                $medicineId .
                '.'
        ]);

        exit;
    }


    // -----------------------------------------------------
    // Validate Stock
    // -----------------------------------------------------

    if ($stock <= 0) {

        $cartStmt->close();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
            'Medicine ID ' .
                $medicineId .
                ' is currently out of stock.'
        ]);

        exit;
    }


    // -----------------------------------------------------
    // Validate Requested Quantity
    // -----------------------------------------------------

    if ($quantity > $stock) {

        $cartStmt->close();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
            'Only ' .
                $stock .
                ' item(s) of medicine ID ' .
                $medicineId .
                ' are available.'
        ]);

        exit;
    }


    // -----------------------------------------------------
    // Validate Price
    // -----------------------------------------------------

    if ($price < 0) {

        $cartStmt->close();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
            'Invalid price for medicine ID ' .
                $medicineId .
                '.'
        ]);

        exit;
    }


    // -----------------------------------------------------
    // Calculate Item Total
    // -----------------------------------------------------

    $subtotal += (
        $price *
        $quantity
    );
}


$cartStmt->close();


// ---------------------------------------------------------
// Empty Cart Check
// ---------------------------------------------------------

if ($cartCount === 0) {

    http_response_code(409);

    echo json_encode([
        'success' => false,
        'message' => 'Your cart is empty.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Round Subtotal
// ---------------------------------------------------------

$subtotal = round(
    $subtotal,
    2
);


// ---------------------------------------------------------
// Delivery Charge
// ---------------------------------------------------------

$deliveryCharge =
    $subtotal >= 500
    ? 0.00
    : 40.00;


// ---------------------------------------------------------
// Final Total
// ---------------------------------------------------------

$total = round(
    $subtotal +
        $deliveryCharge,
    2
);


// ---------------------------------------------------------
// Validate Final Amount
// ---------------------------------------------------------

$amountInPaise = (int) round(
    $total * 100
);


if ($amountInPaise <= 0) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid payment amount.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Create Unique Receipt
// ---------------------------------------------------------

try {

    $randomPart = strtoupper(
        substr(
            bin2hex(
                random_bytes(4)
            ),
            0,
            8
        )
    );
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to create payment reference.'
    ]);

    exit;
}


$receipt =
    'MED-' .
    date('YmdHis') .
    '-' .
    $randomPart;


// ---------------------------------------------------------
// Razorpay Order Data
// ---------------------------------------------------------

$razorpayData = [

    'amount' =>
    $amountInPaise,

    'currency' =>
    'INR',

    'receipt' =>
    $receipt,

    'notes' => [

        'user_id' =>
        (string) $userId,

        'project' =>
        'MediCare Pharmacy'
    ]
];


// ---------------------------------------------------------
// Encode Request
// ---------------------------------------------------------

$jsonData = json_encode(
    $razorpayData,
    JSON_UNESCAPED_SLASHES
);


if ($jsonData === false) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
        'Unable to prepare payment request.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Initialize cURL
// ---------------------------------------------------------

$ch = curl_init(
    RAZORPAY_API_URL . '/orders'
);


if ($ch === false) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
        'Unable to initialize payment service.'
    ]);

    exit;
}


// ---------------------------------------------------------
// cURL Configuration
// ---------------------------------------------------------

curl_setopt_array(
    $ch,
    [

        CURLOPT_RETURNTRANSFER =>
        true,

        CURLOPT_POST =>
        true,

        CURLOPT_POSTFIELDS =>
        $jsonData,

        CURLOPT_HTTPHEADER =>
        [

            'Content-Type: application/json',

            'Accept: application/json'
        ],

        CURLOPT_USERPWD =>
        RAZORPAY_KEY_ID .
            ':' .
            RAZORPAY_KEY_SECRET,

        CURLOPT_TIMEOUT =>
        30,

        CURLOPT_CONNECTTIMEOUT =>
        10,

        CURLOPT_SSL_VERIFYPEER =>
        true,

        CURLOPT_SSL_VERIFYHOST =>
        2

    ]
);


// ---------------------------------------------------------
// Send Request
// ---------------------------------------------------------

$response = curl_exec($ch);


$httpCode = (int) curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


$curlError = curl_error($ch);


curl_close($ch);


// ---------------------------------------------------------
// Handle Connection Error
// ---------------------------------------------------------

if (
    $response === false ||
    $curlError !== ''
) {

    http_response_code(502);

    echo json_encode([
        'success' => false,
        'message' =>
        'Unable to connect to Razorpay.',
        'curl_error' =>
        $curlError,
        'http_code' =>
        $httpCode
    ]);

    exit;
}


// ---------------------------------------------------------
// Decode Razorpay Response
// ---------------------------------------------------------

$razorpayOrder = json_decode(
    $response,
    true
);


// ---------------------------------------------------------
// Validate Razorpay Response
// ---------------------------------------------------------

if (
    $httpCode < 200 ||
    $httpCode >= 300 ||
    !is_array($razorpayOrder) ||
    empty($razorpayOrder['id'])
) {

    http_response_code(502);

    echo json_encode([
        'success' => false,
        'message' =>
        'Razorpay could not create the payment order.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Validate Razorpay Amount
// ---------------------------------------------------------

$razorpayAmount =
    isset($razorpayOrder['amount'])
    ? (int) $razorpayOrder['amount']
    : 0;


$razorpayCurrency =
    isset($razorpayOrder['currency'])
    ? (string) $razorpayOrder['currency']
    : '';


if (
    $razorpayAmount !== $amountInPaise ||
    strtoupper($razorpayCurrency) !== 'INR'
) {

    http_response_code(502);

    echo json_encode([
        'success' => false,
        'message' =>
        'Razorpay returned an unexpected payment amount.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Successful Response
// ---------------------------------------------------------

echo json_encode(

    [

        'success' =>
        true,

        'message' =>
        'Razorpay order created successfully.',

        'razorpay' => [

            'key_id' =>
            RAZORPAY_KEY_ID,

            'order_id' =>
            (string) $razorpayOrder['id'],

            'amount' =>
            $amountInPaise,

            'currency' =>
            'INR',

            'receipt' =>
            $receipt
        ],

        'summary' => [

            'subtotal' =>
            $subtotal,

            'delivery' =>
            $deliveryCharge,

            'total' =>
            $total
        ]

    ]

);

exit;
