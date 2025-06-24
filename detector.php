<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Headers: Content-Type, Authorization');


// File to store logs
$logFile = 'dector_log.txt';

// APIKEY from the request headers
$headers = getallheaders();
$clientKey = $headers['Authorization'] ?? $headers['authorization'] ?? null;


if(!file_exists($logFile)) {
    file_put_contents($logFile, '');
}


require_once 'config.php';
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    // NO API KEY for checking log file
    if($_GET['action'] === 'check_log') {
        if(!file_exists(filename: $logFile)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Log file not found']);
            exit;
        } 

        $logContent = file_get_contents($logFile);

        if ($logContent === false) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to read log file']);
            exit;
        }


        echo $logContent;
        exit;
    }
    if (empty($clientKey)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Bad Request: API Key is required']);
        exit;
    }

    if(!defined('API_KEY')) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Internal Server Error: API Key is not defined']);
        exit;
    }

    if($clientKey !== API_KEY) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Forbidden: Invalid API Key'.$clientKey]);
        exit;
    }


    echo json_encode(['status' => 'success', 'message' => 'API Key is valid']);

    $data = [
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Read existing data
    $existing = [];
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $existing = json_decode($content, true);
        if (!is_array($existing)) {
            $existing = [];
        }
    }

    // Append new log
    $existing[] = $data;

    // Save as JSON array
    file_put_contents($logFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    exit;
    
}

