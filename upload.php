<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// ====== CONFIG ======
require_once 'config.php'; // defines API_KEY

$logFile = __DIR__ . '/dector_log.txt';
$uploadDir = __DIR__ . '/uploads';

// Ensure log file and upload folder exist
if (!file_exists(filename: $logFile)) file_put_contents($logFile, '');
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

// Get API key from headers
$headers = getallheaders();
$clientKey = $headers['Authorization'] ?? $headers['authorization'] ?? null;

// ====== GET Request Logic ======
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    // Show raw log content (no API key required)
    if ($action === 'check_log') {
        if (!file_exists(filename: $logFile)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Log file not found']);
            exit;
        }

        $logContent = file_get_contents($logFile);
        echo $logContent;
        exit;
    }

    // Validate API key
    if (empty($clientKey)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'API Key is required']);
        exit;
    }

    if (!defined('API_KEY') || $clientKey !== API_KEY) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
        exit;
    }

    // Success message
    echo json_encode(['status' => 'success', 'message' => 'API Key is valid']);

    // Append log
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'GET access'
    ];

    appendToLog($logFile, $logData);
    exit;
}

// ====== POST Request Logic (Upload Image) ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents("php://input");

    if (!$rawData) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No image data received']);
        exit;
    }

    // Generate unique image name
    $imageId = uniqid();
    $imageName = $imageId . '.jpg';
    $imagePath = $uploadDir . '/' . $imageName;

    if (file_put_contents($imagePath, $rawData)) {
        $imageUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/uploads/' . $imageName;

        // Log the upload
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'image_url' => $imageUrl
        ];
        appendToLog($logFile, $logData);

        echo json_encode([
            'status' => 'success',
            'image_url' => $imageUrl
            ]);

    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save image']);
    }

    exit;
}

// ====== Function to Append Log as JSON Array ======
function appendToLog($file, $data) {
    $existing = [];
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $existing = json_decode($content, true);
        if (!is_array($existing)) $existing = [];
    }
    $existing[] = $data;
    file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
