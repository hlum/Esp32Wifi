<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// file to store FCM token
$fcmTokenFile = 'fcm_token.txt';

// Initialize FCM token file if it doesn't exist
if(!file_exists($fcmTokenFile)) {
    file_put_contents($fcmTokenFile, '');
}

if($_GET['token']) {
    $newToken = [
        'token' => $_GET['token'],
    ];
    // Append the new token to the log file
    appendToLog($fcmTokenFile, $newToken);

    echo json_encode([
        'status' => 'success',
        'message' => 'FCM token saved successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    http_response_code(200);
    exit;
}

function appendToLog($file, $data) {
    $existing = [];

    if (file_exists($file)) {
        $content = file_get_contents($file);
        $existing = json_decode($content, true);
        if (!is_array($existing)) $existing = [];
    }

    // Check if the token already exists
    $tokenExists = false;
    foreach ($existing as $entry) {
        if (isset($entry['token']) && $entry['token'] === $data['token']) {
            $tokenExists = true;
            break;
        }
    }

    if (!$tokenExists) {
        $existing[] = $data;
        file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}



