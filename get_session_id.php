<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once 'bootstrap.php';
    require_once 'classes/AccurateAPI.php';
    
    $api = new AccurateAPI();
    
    // Get session ID dari constant atau config
    $sessionId = ACCURATE_SESSION_ID ?? '';
    
    if (empty($sessionId)) {
        throw new Exception('Session ID tidak tersedia. Silakan lakukan OAuth terlebih dahulu.');
    }
    
    echo json_encode([
        'success' => true,
        'sessionId' => $sessionId,
        'message' => 'Session ID berhasil diperoleh'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
