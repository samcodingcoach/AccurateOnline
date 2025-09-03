<?php
/**
 * Selling Price Adjustment List API
 * File: /sellingprice/list_spa.php
 * HTTP Method: GET
 * Scope: sellingprice_adjustment_view
 * Endpoint: /api/sellingprice-adjustment/list.do
 * X-Session-ID header required
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Add CORS headers if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Session-ID');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Hanya izinkan method GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed. Use GET method.', 405);
    }
    
    // Inisialisasi API class
    $api = new AccurateAPI();
    
    // Debug: Log semua headers yang diterima
    error_log('All headers: ' . print_r(getallheaders(), true));
    error_log('Server variables: ' . print_r($_SERVER, true));
    
    // Validasi session ID dari header - coba beberapa kemungkinan format
    $sessionId = null;
    
    // Coba format standar
    if (isset($_SERVER['HTTP_X_SESSION_ID'])) {
        $sessionId = $_SERVER['HTTP_X_SESSION_ID'];
    }
    // Coba format alternatif
    elseif (isset($_SERVER['X-Session-ID'])) {
        $sessionId = $_SERVER['X-Session-ID'];
    }
    // Coba dari getallheaders()
    else {
        $headers = getallheaders();
        if (isset($headers['X-Session-ID'])) {
            $sessionId = $headers['X-Session-ID'];
        }
    }
    
    // Jika masih tidak ditemukan, gunakan dari config sebagai fallback untuk testing
    if (!$sessionId) {
        $sessionId = ACCURATE_SESSION_ID ?? null;
        error_log('Using session ID from config as fallback: ' . $sessionId);
    }
    
    if (!$sessionId) {
        throw new Exception('X-Session-ID header is required. To get session ID, call GET /get_session_id.php or check your ACCURATE_SESSION_ID in config.php', 400);
    }
    
    // Set session ID jika berbeda dari config
    if ($sessionId !== ACCURATE_SESSION_ID) {
        $api->setSessionId($sessionId);
    }
    
    // Siapkan parameter opsional dari query string
    $params = [];
    
    // Tambahkan parameter jika disediakan dalam query string
    if (isset($_GET['page']) && !empty($_GET['page'])) {
        $params['sp.page'] = intval($_GET['page']);
    }
    
    if (isset($_GET['pageSize']) && !empty($_GET['pageSize'])) {
        $params['sp.pageSize'] = intval($_GET['pageSize']);
    }
    
    if (isset($_GET['fields']) && !empty($_GET['fields'])) {
        $params['fields'] = $_GET['fields'];
    }
    
    // Panggil API untuk mendapatkan list selling price adjustment menggunakan metode publik baru
    // Dengan parameter default sudah diatur di metode getSellingPriceAdjustmentList
    $result = $api->getSellingPriceAdjustmentList($params);
    
    if ($result['success']) {
        // Log successful request for debugging
        $page = $params['sp.page'] ?? 1;
        $pageSize = $params['sp.pageSize'] ?? 25;
        error_log("Selling Price Adjustment List API Success - Page: {$page}, PageSize: {$pageSize}");
        
        // Return response directly from Accurate API
        // Format response to match exactly what Accurate API returns
        echo json_encode($result['data']);
    } else {
        throw new Exception($result['error'] ?? 'Failed to get selling price adjustments', $result['http_code'] ?? 500);
    }
    
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    
    // Log error for debugging
    error_log("Selling Price Adjustment List API Error - Code: {$statusCode}, Message: " . $e->getMessage());
    
    // Set appropriate HTTP status code
    http_response_code($statusCode);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $statusCode,
        'request_info' => [
            'method' => 'GET',
            'scope_required' => 'sellingprice_adjustment_view',
            'endpoint' => '/accurate/api/sellingprice-adjustment/list.do',
            'timestamp' => date('Y-m-d H:i:s'),
            'help' => 'To get X-Session-ID: 1) Check ACCURATE_SESSION_ID in config.php, 2) Or call GET /get_session_id.php, 3) Or refresh session with refresh-session.php'
        ]
    ]);
}
?>