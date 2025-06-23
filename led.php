<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// File to store LED state
$stateFile = 'led_state.txt';

// Initialize state file if it doesn't exist
if (!file_exists(filename: $stateFile)) {
    file_put_contents($stateFile, '0');
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Check if action parameter is provided
    if (isset($_GET['action'])) {
        $action = strtolower($_GET['action']);
        
        switch ($action) {
            case 'on':
            case '1':
                // Turn LED on
                file_put_contents($stateFile, '1');
                $response = [
                    'status' => 'success',
                    'message' => 'LED turned ON',
                    'led_state' => 1,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                http_response_code(200);
                break;
                
            case 'off':
            case '0':
                // Turn LED off
                file_put_contents($stateFile, '0');
                $response = [
                    'status' => 'success',
                    'message' => 'LED turned OFF',
                    'led_state' => 0,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                http_response_code(200);
                break;
                
            case 'toggle':
                // Toggle LED state
                $currentState = (int)file_get_contents($stateFile);
                $newState = $currentState === 1 ? 0 : 1;
                file_put_contents($stateFile, (string)$newState);
                
                $response = [
                    'status' => 'success',
                    'message' => 'LED toggled to ' . ($newState === 1 ? 'ON' : 'OFF'),
                    'led_state' => $newState,
                    'previous_state' => $currentState,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                http_response_code(200);
                break;
                
            case 'status':
                // Get current LED state
                $currentState = (int)file_get_contents($stateFile);
                $response = [
                    'status' => 'success',
                    'message' => 'Current LED state retrieved',
                    'led_state' => $currentState,
                    'led_status' => $currentState === 1 ? 'ON' : 'OFF',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                http_response_code(200);
                break;
                
            default:
                // Invalid action
                $response = [
                    'status' => 'error',
                    'message' => 'Invalid action. Use: on, off, toggle, or status',
                    'valid_actions' => ['on', 'off', 'toggle', 'status'],
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                http_response_code(400);
                break;
        }
    } else {
        // No action parameter provided - show API documentation
        $currentState = (int)file_get_contents($stateFile);
        $response = [
            'status' => 'info',
            'message' => 'ESP32 LED Control API',
            'current_led_state' => $currentState,
            'current_led_status' => $currentState === 1 ? 'ON' : 'OFF',
            'usage' => [
                'Turn LED ON' => $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?action=on',
                'Turn LED OFF' => $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?action=off',
                'Toggle LED' => $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?action=toggle',
                'Get LED Status' => $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?action=status'
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        http_response_code(200);
    }
    
} else {
    // Method not allowed
    $response = [
        'status' => 'error',
        'message' => 'Only GET requests are allowed',
        'method_used' => $_SERVER['REQUEST_METHOD'],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    http_response_code(405);
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);

// Log the request (optional)
$logEntry = date(format: 'Y-m-d H:i:s') . ' - ' . $_SERVER['REQUEST_METHOD'] . ' - ' . 
           (isset($_GET['action']) ? $_GET['action'] : 'no_action') . ' - ' . 
           $_SERVER['REMOTE_ADDR'] . PHP_EOL;
file_put_contents('api_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
?>