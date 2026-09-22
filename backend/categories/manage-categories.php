<?php

/**
 * MediCare Pharmacy
 * Category Management API
 *
 * File:
 * backend/categories/manage-categories.php
 */

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';


// ---------------------------------------------------------
// Admin Authentication
// ---------------------------------------------------------

if (
    !isset($_SESSION['admin_id']) ||
    ($_SESSION['admin_logged_in'] ?? false) !== true
) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Admin login required.'
    ]);

    exit;
}


// ---------------------------------------------------------
// GET - Load Categories
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $sql = "
        SELECT
            id,
            name
        FROM categories
        ORDER BY name ASC
    ";

    $result = $conn->query($sql);

    if (!$result) {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Unable to load categories.'
        ]);

        exit;
    }

    $categories = [];

    while ($row = $result->fetch_assoc()) {

        $categories[] = [
            'id' => (int)$row['id'],
            'name' => $row['name']
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Categories loaded successfully.',
        'categories' => $categories
    ]);

    exit;
}


// ---------------------------------------------------------
// POST - Add Category
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rawInput = file_get_contents('php://input');

    $data = json_decode(
        $rawInput,
        true
    );


    if (!is_array($data)) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data.'
        ]);

        exit;
    }


    $name = trim(
        (string)($data['name'] ?? '')
    );


    // -----------------------------------------------------
    // Validate Name
    // -----------------------------------------------------

    if ($name === '') {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Category name is required.'
        ]);

        exit;
    }


    if (mb_strlen($name) < 2) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' =>
            'Category name must contain at least 2 characters.'
        ]);

        exit;
    }


    if (mb_strlen($name) > 100) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' =>
            'Category name cannot exceed 100 characters.'
        ]);

        exit;
    }


    // -----------------------------------------------------
    // Check Duplicate
    // -----------------------------------------------------

    $checkSql = "
        SELECT id
        FROM categories
        WHERE LOWER(TRIM(name)) = LOWER(TRIM(?))
        LIMIT 1
    ";

    $checkStmt = $conn->prepare($checkSql);

    if (!$checkStmt) {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Unable to check category.'
        ]);

        exit;
    }


    $checkStmt->bind_param(
        's',
        $name
    );


    if (!$checkStmt->execute()) {

        $checkStmt->close();

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Unable to check category.'
        ]);

        exit;
    }


    $checkResult =
        $checkStmt->get_result();


    if ($checkResult->num_rows > 0) {

        $checkStmt->close();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'This category already exists.'
        ]);

        exit;
    }


    $checkStmt->close();


    // -----------------------------------------------------
    // Insert Category
    // -----------------------------------------------------

    $insertSql = "
        INSERT INTO categories (name)
        VALUES (?)
    ";

    $insertStmt =
        $conn->prepare($insertSql);


    if (!$insertStmt) {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
            'Unable to prepare category creation.'
        ]);

        exit;
    }


    $insertStmt->bind_param(
        's',
        $name
    );


    if (!$insertStmt->execute()) {

        $error =
            $insertStmt->error;

        $insertStmt->close();

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
            'Unable to add category.',
            'error' => $error
        ]);

        exit;
    }


    $categoryId =
        $insertStmt->insert_id;


    $insertStmt->close();


    // -----------------------------------------------------
    // Success
    // -----------------------------------------------------

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' =>
        'Category added successfully.',
        'category' => [
            'id' => (int)$categoryId,
            'name' => $name
        ]
    ]);

    exit;
}


// ---------------------------------------------------------
// DELETE - Delete Category
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $rawInput =
        file_get_contents('php://input');

    $data =
        json_decode(
            $rawInput,
            true
        );


    $id = filter_var(
        $data['id'] ?? null,
        FILTER_VALIDATE_INT
    );


    if (
        $id === false ||
        $id === null ||
        $id <= 0
    ) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'A valid category ID is required.'
        ]);

        exit;
    }


    // -----------------------------------------------------
    // Check Medicines Using Category
    // -----------------------------------------------------

    $medicineSql = "
        SELECT COUNT(*) AS total
        FROM medicines
        WHERE category_id = ?
    ";

    $medicineStmt =
        $conn->prepare(
            $medicineSql
        );


    if (!$medicineStmt) {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
            'Unable to check category usage.'
        ]);

        exit;
    }


    $medicineStmt->bind_param(
        'i',
        $id
    );


    $medicineStmt->execute();


    $medicineResult =
        $medicineStmt->get_result();


    $medicineRow =
        $medicineResult->fetch_assoc();


    $medicineCount =
        (int)($medicineRow['total'] ?? 0);


    $medicineStmt->close();


    if ($medicineCount > 0) {

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
            'This category cannot be deleted because medicines are using it.',
            'medicine_count' =>
            $medicineCount
        ]);

        exit;
    }


    // -----------------------------------------------------
    // Delete Category
    // -----------------------------------------------------

    $deleteSql = "
        DELETE FROM categories
        WHERE id = ?
        LIMIT 1
    ";

    $deleteStmt =
        $conn->prepare(
            $deleteSql
        );


    if (!$deleteStmt) {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
            'Unable to prepare category deletion.'
        ]);

        exit;
    }


    $deleteStmt->bind_param(
        'i',
        $id
    );


    if (!$deleteStmt->execute()) {

        $deleteStmt->close();

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
            'Unable to delete category.'
        ]);

        exit;
    }


    if ($deleteStmt->affected_rows === 0) {

        $deleteStmt->close();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' =>
            'Category not found.'
        ]);

        exit;
    }


    $deleteStmt->close();


    // -----------------------------------------------------
    // Success
    // -----------------------------------------------------

    echo json_encode([
        'success' => true,
        'message' =>
        'Category deleted successfully.'
    ]);

    exit;
}


// ---------------------------------------------------------
// Unsupported Method
// ---------------------------------------------------------

http_response_code(405);

header(
    'Allow: GET, POST, DELETE'
);

echo json_encode([
    'success' => false,
    'message' =>
    'Method not allowed.'
]);

exit;
