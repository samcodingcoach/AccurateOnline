<?php
/**
 * API untuk mendapatkan list unit dalam format JSON
 * File: /unit/list_unit.php
 * HTTP Method: GET
 * Scope: unit_view
 * Endpoint: /list.do
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // Inisialisasi API class
    $api = new AccurateAPI();

    // Get parameters dari query string
    $params = [];
    $allowedParams = ['page', 'limit'];

    foreach ($allowedParams as $param) {
        if (isset($_GET[$param]) && !empty($_GET[$param])) {
            $params[$param] = sanitizeInput($_GET[$param]);
        }
    }

    // Set default values dan validasi parameter
    $limit = isset($params['limit']) ? (int)$params['limit'] : 25;
    $page = isset($params['page']) ? (int)$params['page'] : 1;

    // Validasi limit (maksimal 100 untuk performa)
    if ($limit > 100) {
        $limit = 100;
    }
    if ($limit < 1) {
        $limit = 25;
    }

    // Validasi page
    if ($page < 1) {
        $page = 1;
    }

    // Get list unit dengan parameter yang benar
    $result = $api->getUnitList($limit, $page);

    if ($result['success']) {
        // Format response data
        $responseData = [
            'units' => $result['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_pages' => isset($result['data']['sp']['pageCount']) ? $result['data']['sp']['pageCount'] : 0,
                'row_count' => isset($result['data']['sp']['rowCount']) ? $result['data']['sp']['rowCount'] : 0,
            ],
            'meta' => [
                'scope_required' => 'unit_view',
                'http_code' => $result['http_code'],
                'endpoint' => '/accurate/api/unit/list.do'
            ]
        ];
        
        echo jsonResponse($responseData, true, 'Data unit berhasil diambil');
    } else {
        // Log error untuk debugging
        $errorMessage = $result['error'] ?? 'Unknown error';
        logError("Failed to get unit list: " . $errorMessage, __FILE__, __LINE__);
        
        http_response_code($result['http_code'] ?: 500);
        echo jsonResponse(null, false, 'Gagal mengambil data unit: ' . $errorMessage);
    }

} catch (Exception $e) {
    // Handle unexpected errors
    logError("Exception in unit list API: " . $e->getMessage(), __FILE__, $e->getLine());
    http_response_code(500);
    echo jsonResponse(null, false, 'Terjadi kesalahan internal server');
}
?>