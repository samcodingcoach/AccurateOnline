<?php
/**
 * API untuk mendapatkan list price category dalam format JSON
 * File: /price/listprice_category.php
 * HTTP Method: GET
 * Scope: price_category_view
 * Endpoint: /list.do
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
    
    // Dapatkan data price category list dari API
    $result = $api->getPriceCategoryList();
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        echo jsonResponse(null, false, $result['message'] ?? 'Failed to fetch price category data');
    }
} catch (Exception $e) {
    echo jsonResponse(null, false, 'Error: ' . $e->getMessage());
}
?>
