<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header("Access-Control-Allow-Headers: API_KEY");


// File to store logs
$logFile = 'dector_log.txt';

// APIKEY from the request headers
$clientKey = $headers['API_KEY'] ?? null;


if(!file_exists($logFile)) {
    file_put_contents($logFile, '');
}


require_once 'config.php';
if($_SERVER['REQUEST_METHOD'] == 'GET') {
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
    exit;
    
}

?>