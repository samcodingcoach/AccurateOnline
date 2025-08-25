<?php
/**
 * Customer List API
 * File: /api/customers.php
 * HTTP Method: GET
 * Scope: customer_view
 * Endpoint: /list.do
 * X-Session-ID header required
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo jsonResponse(null, false, 'Method tidak diizinkan. Gunakan GET.');
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

    // Get list customer dengan parameter yang benar
    $result = $api->getCustomerList($limit, $page);

    if ($result['success']) {
        // Format response data
        $responseData = [
            'customers' => $result['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => isset($result['data']['sp']['pageCount']) ? $result['data']['sp']['pageCount'] : 0,
                'has_more' => isset($result['data']['sp']['hasMore']) ? $result['data']['sp']['hasMore'] : false
            ],
            'meta' => [
                'scope_required' => 'customer_view',
                'http_code' => $result['http_code'],
                'endpoint' => '/accurate/api/customer/list.do'
            ]
        ];
        
        echo jsonResponse($responseData, true, 'Data customer berhasil diambil');
    } else {
        // Log error untuk debugging
        $errorMessage = $result['error'] ?? 'Unknown error';
        logError("Failed to get customer list: " . $errorMessage, __FILE__, __LINE__);
        
        echo jsonResponse(null, false, 'Gagal mengambil data customer: ' . $errorMessage);
    }

} catch (Exception $e) {
    // Handle unexpected errors
    logError("Exception in customer list API: " . $e->getMessage(), __FILE__, $e->getLine());
    echo jsonResponse(null, false, 'Terjadi kesalahan internal server');
}
?>
