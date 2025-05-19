<?php
// Ensure $config is available
if (!isset($config)) {
    $config = require __DIR__ . '/config.php';
}

try {
    $db = new PDO('sqlite:' . $config['root_dir'] . '\\data\\database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]);
    echo json_encode(['error' => 'Database connection failed', $config['root_dir'] . '\\data\\database.sqlite']);
    exit;
}
