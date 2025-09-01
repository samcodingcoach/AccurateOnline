<?php
/**
 * API untuk mendapatkan detail unit dalam format JSON
 * File: /unit/detail.php
 * HTTP Method: GET
 * Scope: unit_view
 * Endpoint: /detail.do
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['s' => false, 'd' => 'Method Not Allowed']);
    exit;
}

// Ambil ID dari query string
$unitId = $_GET['id'] ?? null;

if (empty($unitId)) {
    http_response_code(400);
    echo json_encode(['s' => false, 'd' => 'Parameter ID is required.']);
    exit;
}

try {
    // Inisialisasi API class
    $api = new AccurateAPI();

    // Panggil method untuk mendapatkan detail unit
    $result = $api->getUnitDetail($unitId);

    if ($result['success'] && isset($result['data'])) {
        // Jika berhasil, kembalikan data dari Accurate
        http_response_code(200);
        echo json_encode($result['data']);
    } else {
        // Jika gagal, berikan response error yang sesuai
        $http_code = $result['http_code'] ?? 500;
        $error_message = $result['error'] ?? 'Failed to get unit detail.';
        
        // Log error untuk debugging
        logError("Failed to get unit detail for ID: $unitId - $error_message", __FILE__, __LINE__);

        http_response_code($http_code);
        echo json_encode(['s' => false, 'd' => $error_message]);
    }

} catch (Exception $e) {
    // Handle kesalahan tak terduga
    logError("Exception in unit detail API: " . $e->getMessage(), __FILE__, $e->getLine());
    http_response_code(500);
    echo json_encode(['s' => false, 'd' => 'Internal Server Error']);
}
?>
