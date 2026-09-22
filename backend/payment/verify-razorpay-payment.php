<?php

/**
 * MediCare Pharmacy
 * Verify Razorpay Payment + Create Order API
 *
 * File:
 * backend/payment/verify-razorpay-payment.php
 */

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/razorpay.php';


// ---------------------------------------------------------
// Helper
// ---------------------------------------------------------

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
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


// ---------------------------------------------------------
// Only POST
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Allow: POST');

    sendResponse(
        false,
        'Method not allowed. Please use POST.',
        405
    );
}


// ---------------------------------------------------------
// Login
// ---------------------------------------------------------

$userId = (int)($_SESSION['user_id'] ?? 0);

if ($userId <= 0) {

    sendResponse(
        false,
        'Please login before completing payment.',
        401,
        [
            'login_required' => true
        ]
    );
}


// ---------------------------------------------------------
// Read JSON
// ---------------------------------------------------------

$rawInput = file_get_contents('php://input');

$data = json_decode(
    $rawInput,
    true
);

if (!is_array($data)) {

    sendResponse(
        false,
        'Invalid payment data.',
        400
    );
}


// ---------------------------------------------------------
// Razorpay IDs
// ---------------------------------------------------------

$razorpayPaymentId = trim(
    (string)($data['razorpay_payment_id'] ?? '')
);

$razorpayOrderId = trim(
    (string)($data['razorpay_order_id'] ?? '')
);

$razorpaySignature = trim(
    (string)($data['razorpay_signature'] ?? '')
);


if ($razorpayPaymentId === '') {

    sendResponse(
        false,
        'Razorpay payment ID is missing.',
        400
    );
}


if ($razorpayOrderId === '') {

    sendResponse(
        false,
        'Razorpay order ID is missing.',
        400
    );
}


if ($razorpaySignature === '') {

    sendResponse(
        false,
        'Payment signature is missing.',
        400
    );
}


// ---------------------------------------------------------
// Delivery Information
// ---------------------------------------------------------

$fullName = trim(
    (string)($data['full_name'] ?? '')
);

$phone = trim(
    (string)($data['phone'] ?? '')
);

$email = trim(
    (string)($data['email'] ?? '')
);

$address = trim(
    (string)($data['address'] ?? '')
);

$city = trim(
    (string)($data['city'] ?? '')
);

$state = trim(
    (string)($data['state'] ?? '')
);

$pincode = trim(
    (string)($data['pincode'] ?? '')
);

$landmark = trim(
    (string)($data['landmark'] ?? '')
);


// ---------------------------------------------------------
// Validate Delivery Information
// ---------------------------------------------------------

if (
    $fullName === '' ||
    $phone === '' ||
    $email === '' ||
    $address === '' ||
    $city === '' ||
    $state === '' ||
    $pincode === ''
) {

    sendResponse(
        false,
        'Please correct the checkout information.',
        400
    );
}


if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {

    sendResponse(
        false,
        'Please enter a valid 10-digit mobile number.',
        400
    );
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    sendResponse(
        false,
        'Please enter a valid email address.',
        400
    );
}


if (!preg_match('/^[0-9]{6}$/', $pincode)) {

    sendResponse(
        false,
        'Please enter a valid 6-digit pincode.',
        400
    );
}


// ---------------------------------------------------------
// Verify Razorpay Signature
// ---------------------------------------------------------

$signaturePayload =
    $razorpayOrderId .
    '|' .
    $razorpayPaymentId;


$expectedSignature = hash_hmac(
    'sha256',
    $signaturePayload,
    RAZORPAY_KEY_SECRET
);


if (
    !hash_equals(
        $expectedSignature,
        $razorpaySignature
    )
) {

    sendResponse(
        false,
        'Payment verification failed.',
        400
    );
}


// ---------------------------------------------------------
// Razorpay API Helper
// ---------------------------------------------------------

function razorpayGet(string $url): array
{
    $ch = curl_init($url);

    if ($ch === false) {

        return [
            'success' => false,
            'message' =>
            'Unable to initialize Razorpay connection.'
        ];
    }


    curl_setopt_array(
        $ch,
        [

            CURLOPT_RETURNTRANSFER =>
            true,

            CURLOPT_HTTPGET =>
            true,

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


    $response = curl_exec($ch);


    $httpCode = (int)curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


    $curlError = curl_error($ch);


    curl_close($ch);


    if (
        $response === false ||
        $curlError !== ''
    ) {

        return [
            'success' => false,
            'message' =>
            'Unable to connect to Razorpay.'
        ];
    }


    $decoded = json_decode(
        $response,
        true
    );


    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        !is_array($decoded)
    ) {

        return [
            'success' => false,
            'message' =>
            'Razorpay returned an invalid response.'
        ];
    }


    return [
        'success' => true,
        'data' => $decoded
    ];
}


// ---------------------------------------------------------
// Retrieve Razorpay Order
// ---------------------------------------------------------

$razorpayOrderResult = razorpayGet(
    RAZORPAY_API_URL .
        '/orders/' .
        rawurlencode($razorpayOrderId)
);


if (!$razorpayOrderResult['success']) {

    sendResponse(
        false,
        $razorpayOrderResult['message'],
        502
    );
}


$razorpayOrder =
    $razorpayOrderResult['data'];


// ---------------------------------------------------------
// Validate Razorpay Order ID
// ---------------------------------------------------------

if (
    (string)($razorpayOrder['id'] ?? '') !==
    $razorpayOrderId
) {

    sendResponse(
        false,
        'Razorpay order could not be verified.',
        400
    );
}


// ---------------------------------------------------------
// Validate User Ownership
// ---------------------------------------------------------

$notesUserId = '';

if (
    isset($razorpayOrder['notes']) &&
    is_array($razorpayOrder['notes'])
) {

    $notesUserId = (string)(
        $razorpayOrder['notes']['user_id'] ?? ''
    );
}


if (
    $notesUserId !== '' &&
    $notesUserId !== (string)$userId
) {

    sendResponse(
        false,
        'This payment does not belong to your account.',
        403
    );
}


// ---------------------------------------------------------
// Validate Currency
// ---------------------------------------------------------

$currency = strtoupper(
    (string)($razorpayOrder['currency'] ?? '')
);


if ($currency !== 'INR') {

    sendResponse(
        false,
        'Invalid payment currency.',
        400
    );
}


// ---------------------------------------------------------
// Razorpay Order Amount
// ---------------------------------------------------------

$razorpayAmount = (int)(
    $razorpayOrder['amount'] ?? 0
);


if ($razorpayAmount <= 0) {

    sendResponse(
        false,
        'Invalid Razorpay payment amount.',
        400
    );
}


// ---------------------------------------------------------
// Retrieve Razorpay Payment
// ---------------------------------------------------------

$paymentResult = razorpayGet(
    RAZORPAY_API_URL .
        '/payments/' .
        rawurlencode($razorpayPaymentId)
);


if (!$paymentResult['success']) {

    sendResponse(
        false,
        'Unable to retrieve Razorpay payment.',
        502
    );
}


$payment =
    $paymentResult['data'];


// ---------------------------------------------------------
// Validate Payment ID
// ---------------------------------------------------------

if (
    (string)($payment['id'] ?? '') !==
    $razorpayPaymentId
) {

    sendResponse(
        false,
        'Razorpay payment could not be verified.',
        400
    );
}


// ---------------------------------------------------------
// Validate Payment Order ID
// ---------------------------------------------------------

$paymentOrderId = (string)(
    $payment['order_id'] ?? ''
);


if (
    $paymentOrderId !==
    $razorpayOrderId
) {

    sendResponse(
        false,
        'Payment and order do not match.',
        400
    );
}


// ---------------------------------------------------------
// Validate Payment Amount
// ---------------------------------------------------------

$paymentAmount = (int)(
    $payment['amount'] ?? 0
);


if (
    $paymentAmount !==
    $razorpayAmount
) {

    sendResponse(
        false,
        'Payment amount does not match the order amount.',
        400
    );
}


// ---------------------------------------------------------
// Validate Payment Currency
// ---------------------------------------------------------

$paymentCurrency = strtoupper(
    (string)($payment['currency'] ?? '')
);


if (
    $paymentCurrency !==
    'INR'
) {

    sendResponse(
        false,
        'Payment currency does not match.',
        400
    );
}


// ---------------------------------------------------------
// Validate Payment Status
// ---------------------------------------------------------

$paymentStatus = strtolower(
    (string)($payment['status'] ?? '')
);


if (
    $paymentStatus !==
    'captured'
) {

    sendResponse(
        false,
        'Payment has not been successfully completed.',
        400,
        [
            'payment_status' =>
            $paymentStatus
        ]
    );
}


// ---------------------------------------------------------
// Begin Database Transaction
// ---------------------------------------------------------

$conn->begin_transaction();


try {

   
    // -----------------------------------------------------
    // Get Cart
    // -----------------------------------------------------
    //
    // IMPORTANT:
    // medicines.name is NOT selected.
    // Only ID, price and stock are required.
    //

    $cartStmt = $conn->prepare(
        "SELECT
            c.medicine_id,
            c.quantity,
            m.price,
            m.stock
         FROM cart c
         INNER JOIN medicines m
             ON m.id = c.medicine_id
         WHERE c.user_id = ?
         FOR UPDATE"
    );


    if (!$cartStmt) {

        throw new Exception(
            'Unable to load shopping cart.'
        );
    }


    $cartStmt->bind_param(
        'i',
        $userId
    );


    if (!$cartStmt->execute()) {

        $cartStmt->close();

        throw new Exception(
            'Unable to load shopping cart.'
        );
    }


    $cartStmt->store_result();


    if (
        $cartStmt->num_rows === 0
    ) {

        $cartStmt->close();

        throw new Exception(
            'Your cart is empty.'
        );
    }


    $cartStmt->bind_result(
        $medicineId,
        $quantity,
        $medicinePrice,
        $medicineStock
    );


    $cartItems = [];

    $subtotal = 0.00;


    // -----------------------------------------------------
    // Read Cart Items
    // -----------------------------------------------------

    while ($cartStmt->fetch()) {

        $medicineId =
            (int)$medicineId;

        $quantity =
            (int)$quantity;

        $medicinePrice =
            (float)$medicinePrice;

        $medicineStock =
            (int)$medicineStock;


        // -------------------------------------------------
        // Validate Quantity
        // -------------------------------------------------

        if (
            $quantity <= 0
        ) {

            $cartStmt->close();

            throw new Exception(
                'Invalid medicine quantity for medicine ID ' .
                    $medicineId .
                    '.'
            );
        }


        // -------------------------------------------------
        // Validate Stock
        // -------------------------------------------------

        if (
            $medicineStock <= 0
        ) {

            $cartStmt->close();

            throw new Exception(
                'Medicine ID ' .
                    $medicineId .
                    ' is currently out of stock.'
            );
        }


        // -------------------------------------------------
        // Validate Requested Quantity
        // -------------------------------------------------

        if (
            $quantity >
            $medicineStock
        ) {

            $cartStmt->close();

            throw new Exception(
                'Only ' .
                    $medicineStock .
                    ' item(s) are available for medicine ID ' .
                    $medicineId .
                    '.'
            );
        }


        // -------------------------------------------------
        // Validate Price
        // -------------------------------------------------

        if (
            $medicinePrice < 0
        ) {

            $cartStmt->close();

            throw new Exception(
                'Invalid price for medicine ID ' .
                    $medicineId .
                    '.'
            );
        }


        // -------------------------------------------------
        // Calculate Item Total
        // -------------------------------------------------

        $itemTotal =
            $medicinePrice *
            $quantity;


        $subtotal +=
            $itemTotal;


        $cartItems[] = [

            'medicine_id' =>
            $medicineId,

            'price' =>
            $medicinePrice,

            'quantity' =>
            $quantity,

            'stock' =>
            $medicineStock,

            'total_price' =>
            $itemTotal

        ];
    }


    $cartStmt->close();


    // -----------------------------------------------------
    // Calculate Delivery
    // -----------------------------------------------------

    $subtotal =
        round(
            $subtotal,
            2
        );


    $deliveryCharge =
        $subtotal >= 500
        ? 0.00
        : 40.00;


    // -----------------------------------------------------
    // Calculate Final Total
    // -----------------------------------------------------

    $totalAmount =
        round(
            $subtotal +
                $deliveryCharge,
            2
        );


    // -----------------------------------------------------
    // Compare With Razorpay
    // -----------------------------------------------------

    $expectedPaise =
        (int)round(
            $totalAmount * 100
        );


    if (
        $expectedPaise !==
        $razorpayAmount
    ) {

        throw new Exception(
            'Payment amount does not match the current cart total.'
        );
    }


    // -----------------------------------------------------
    // Duplicate Order Protection
    // -----------------------------------------------------

    $duplicateOrderId = 0;


    $duplicateStmt = $conn->prepare(
        "SELECT
            id
         FROM orders
         WHERE user_id = ?
           AND payment_method = 'online'
           AND payment_status = 'Paid'
           AND total_amount = ?
           AND created_at >=
               DATE_SUB(
                   NOW(),
                   INTERVAL 10 MINUTE
               )
         ORDER BY id DESC
         LIMIT 1"
    );


    if ($duplicateStmt) {

        $duplicateStmt->bind_param(
            'id',
            $userId,
            $totalAmount
        );


        if ($duplicateStmt->execute()) {

            $duplicateStmt->store_result();


            if (
                $duplicateStmt->num_rows > 0
            ) {

                $duplicateStmt->bind_result(
                    $duplicateOrderId
                );

                $duplicateStmt->fetch();
            }
        }


        $duplicateStmt->close();
    }


    if (
        $duplicateOrderId > 0
    ) {

        $conn->commit();


        sendResponse(
            true,
            'Payment already processed successfully.',
            200,
            [
                'order' => [
                    'id' =>
                    (int)$duplicateOrderId,

                    'order_id' =>
                    (int)$duplicateOrderId
                ]
            ]
        );
    }


    // -----------------------------------------------------
    // Generate Order Number
    // -----------------------------------------------------

    try {

        $orderNumber =
            'MED-' .
            date('YmdHis') .
            '-' .
            strtoupper(
                bin2hex(
                    random_bytes(3)
                )
            );
    } catch (Throwable $e) {

        throw new Exception(
            'Unable to generate order number.'
        );
    }


    // -----------------------------------------------------
    // Order Status
    // -----------------------------------------------------

    $orderStatus =
        'Order Placed';


    $paymentMethod =
        'online';


    $localPaymentStatus =
        'Paid';


    // -----------------------------------------------------
    // Insert Order
    // -----------------------------------------------------

    $orderStmt = $conn->prepare(
        "INSERT INTO orders
        (
            user_id,
            order_number,
            total_amount,
            status,
            payment_method,
            payment_status,
            full_name,
            phone,
            email,
            address,
            city,
            state,
            pincode,
            landmark
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )"
    );


    if (!$orderStmt) {

        throw new Exception(
            'Unable to prepare order.'
        );
    }


    $orderStmt->bind_param(
        'isdsssssssssss',
        $userId,
        $orderNumber,
        $totalAmount,
        $orderStatus,
        $paymentMethod,
        $localPaymentStatus,
        $fullName,
        $phone,
        $email,
        $address,
        $city,
        $state,
        $pincode,
        $landmark
    );


    if (
        !$orderStmt->execute()
    ) {

        $error =
            $orderStmt->error;

        $orderStmt->close();

        throw new Exception(
            'Unable to create your order. ' .
                $error
        );
    }


    $localOrderId =
        (int)$conn->insert_id;


    $orderStmt->close();


    // -----------------------------------------------------
    // Insert Order Items
    // -----------------------------------------------------

    $itemStmt = $conn->prepare(
        "INSERT INTO order_items
        (
            order_id,
            medicine_id,
            quantity,
            price,
            total_price
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )"
    );


    if (!$itemStmt) {

        throw new Exception(
            'Unable to prepare order items.'
        );
    }


    foreach (
        $cartItems as $item
    ) {

        $medicineId =
            (int)$item['medicine_id'];

        $quantity =
            (int)$item['quantity'];

        $price =
            (float)$item['price'];

        $itemTotal =
            (float)$item['total_price'];


        $itemStmt->bind_param(
            'iiidd',
            $localOrderId,
            $medicineId,
            $quantity,
            $price,
            $itemTotal
        );


        if (
            !$itemStmt->execute()
        ) {

            $error =
                $itemStmt->error;

            $itemStmt->close();

            throw new Exception(
                'Unable to save order items. ' .
                    $error
            );
        }
    }


    $itemStmt->close();


    // -----------------------------------------------------
    // Reduce Medicine Stock
    // -----------------------------------------------------

    $stockStmt = $conn->prepare(
        "UPDATE medicines
         SET stock = stock - ?
         WHERE id = ?
           AND stock >= ?"
    );


    if (!$stockStmt) {

        throw new Exception(
            'Unable to prepare stock update.'
        );
    }


    foreach (
        $cartItems as $item
    ) {

        $medicineId =
            (int)$item['medicine_id'];

        $quantity =
            (int)$item['quantity'];


        $stockStmt->bind_param(
            'iii',
            $quantity,
            $medicineId,
            $quantity
        );


        if (
            !$stockStmt->execute()
        ) {

            $error =
                $stockStmt->error;

            $stockStmt->close();

            throw new Exception(
                'Unable to update medicine stock. ' .
                    $error
            );
        }


        if (
            $stockStmt->affected_rows !== 1
        ) {

            $stockStmt->close();

            throw new Exception(
                'Medicine stock changed. Please try again.'
            );
        }
    }


    $stockStmt->close();


    // -----------------------------------------------------
    // Add Order Tracking
    // -----------------------------------------------------

    $trackingStmt = $conn->prepare(
        "INSERT INTO order_tracking
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
        )"
    );


    if ($trackingStmt) {

        $trackingDescription =
            'Online payment completed and order placed.';


        $trackingStmt->bind_param(
            'iss',
            $localOrderId,
            $orderStatus,
            $trackingDescription
        );


        $trackingStmt->execute();


        $trackingStmt->close();
    }


    // -----------------------------------------------------
    // Clear Cart
    // -----------------------------------------------------

    $clearCartStmt = $conn->prepare(
        "DELETE FROM cart
         WHERE user_id = ?"
    );


    if (!$clearCartStmt) {

        throw new Exception(
            'Unable to prepare cart cleanup.'
        );
    }


    $clearCartStmt->bind_param(
        'i',
        $userId
    );


    if (
        !$clearCartStmt->execute()
    ) {

        $error =
            $clearCartStmt->error;

        $clearCartStmt->close();

        throw new Exception(
            'Unable to clear shopping cart. ' .
                $error
        );
    }


    $clearCartStmt->close();


    // -----------------------------------------------------
    // Commit Transaction
    // -----------------------------------------------------

    $conn->commit();


    // -----------------------------------------------------
    // Success Response
    // -----------------------------------------------------

    sendResponse(
        true,
        'Payment verified and order placed successfully.',
        200,
        [

            'order' => [

                'id' =>
                $localOrderId,

                'order_id' =>
                $localOrderId,

                'order_number' =>
                $orderNumber,

                'status' =>
                $orderStatus,

                'payment_method' =>
                $paymentMethod,

                'payment_status' =>
                $localPaymentStatus,

                'subtotal' =>
                round(
                    $subtotal,
                    2
                ),

                'delivery_charge' =>
                round(
                    $deliveryCharge,
                    2
                ),

                'total_amount' =>
                round(
                    $totalAmount,
                    2
                )
            ],


            'payment' => [

                'payment_id' =>
                $razorpayPaymentId,

                'razorpay_order_id' =>
                $razorpayOrderId,

                'amount' =>
                round(
                    $paymentAmount / 100,
                    2
                ),

                'currency' =>
                $paymentCurrency,

                'status' =>
                $paymentStatus
            ]

        ]
    );
} catch (Throwable $e) {

    // -----------------------------------------------------
    // Rollback
    // -----------------------------------------------------

    $conn->rollback();


    error_log(
        'Razorpay verification/order error: ' .
            $e->getMessage()
    );


    sendResponse(
        false,
        $e->getMessage(),
        400
    );
}
