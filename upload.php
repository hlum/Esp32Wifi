<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Create upload folder if it doesn't exist
$uploadDir = __DIR__ . '/uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Read raw POST data (image bytes)
$rawData = file_get_contents("php://input");

if (!$rawData) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'No image data received'
    ]);
    exit;
}

// Generate a unique filename
$imageId = uniqid(); // e.g. "65f2d3b0aa5e0"
$imageName = $imageId . ".jpg";
$imagePath = $uploadDir . '/' . $imageName;

// Save image
if (file_put_contents($imagePath, $rawData)) {
    // Build image URL (update based on your domain/path)
    $imageUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/uploads/' . $imageName;

    echo json_encode([
        'status' => 'success',
        'image_url' => $imageUrl,
        'image_name' => $imageName
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save image'
    ]);
}
