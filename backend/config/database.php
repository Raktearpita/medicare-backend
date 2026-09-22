<?php

/**
 * MediCare Pharmacy
 * Database Configuration
 *
 * Location:
 * backend/config/database.php
 */

declare(strict_types=1);

// ---------------------------------------------------------
// Database Configuration
// ---------------------------------------------------------

$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = 'Om@98507';
$dbName = 'medicine_store';
$dbPort = 3306;


// ---------------------------------------------------------
// Create Database Connection
// ---------------------------------------------------------

$conn = new mysqli(
    $dbHost,
    $dbUser,
    $dbPassword,
    $dbName,
    $dbPort
);


// ---------------------------------------------------------
// Check Connection
// ---------------------------------------------------------

if ($conn->connect_error) {

    http_response_code(500);

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Set Character Encoding
// ---------------------------------------------------------

if (!$conn->set_charset('utf8mb4')) {

    http_response_code(500);

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'success' => false,
        'message' => 'Unable to configure database character set.'
    ]);

    exit;
}
