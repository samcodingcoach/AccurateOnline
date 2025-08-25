<?php

/**
 * API Endpoint: /list.do
 * HTTP Method: GET
 * Scope: sales_receipt_view
 * 
 * Sales Receipt List API
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
    // Inisialisasi AccurateAPI
    $api = new AccurateAPI();
    
    // Get parameters dari query string
    $params = [];
    $allowedParams = ['page', 'limit', 'fromDate', 'toDate', 'customerNo', 'branchNo'];
    
    foreach ($allowedParams as $param) {
        if (isset($_GET[$param]) && !empty($_GET[$param])) {
            $params[$param] = $_GET[$param];
        }
    }
    
    // Validasi format tanggal jika ada
    if (isset($params['fromDate']) && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $params['fromDate'])) {
        echo jsonResponse(null, false, 'fromDate harus dalam format DD/MM/YYYY');
        exit;
    }
    
    if (isset($params['toDate']) && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $params['toDate'])) {
        echo jsonResponse(null, false, 'toDate harus dalam format DD/MM/YYYY');
        exit;
    }
    
    // Dapatkan data sales receipt list dari API
    $result = $api->getSalesReceiptList($params);
    
    if ($result['success']) {
        // Hapus raw_response dari output jika ada
        if (isset($result['raw_response'])) {
            unset($result['raw_response']);
        }
        
        // Response d nya apa adanya dari API
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo jsonResponse(null, false, $result['message'] ?? 'Failed to fetch sales receipt list');
    }
} catch (Exception $e) {
    echo jsonResponse(null, false, 'Error: ' . $e->getMessage());
}
?>
