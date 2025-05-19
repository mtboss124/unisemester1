<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load configuration
$config = require __DIR__ . '/config/config.php';

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");



// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Dynamically detect base path (useful when hosted in a subdirectory)
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = rtrim($scriptName, '/');

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path from request URI to get route
$route = trim(preg_replace("#^$basePath#", '', $requestUri), '/');

// Split route into parts
$routeParts = explode('/', $route);
$mainRoute = $routeParts[0] ?? '';

// Debug log 
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Base Path: $basePath");
error_log("Resolved Route: $route");

// Routing logic
switch ($mainRoute) {
    case 'info':
        require $config['root_dir'] . '/api/info.php';
        break;


    case 'user':
        require $config['root_dir'] . '/api/user.php';
        break;

    case '':
        http_response_code(200);
        echo json_encode([
            'status' => 'running what the fuck',
            'version' => $config['version'],
            'port' => $config['port']
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => $config['root_dir']]);
        
        break;
}
