<?php
/**
 * API untuk mendapatkan detail price category dalam format JSON
 * File: /price/detailprice_category.php
 * HTTP Method: GET
 * Scope: price_category_view
 * Endpoint: /detail.do
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo jsonResponse(null, false, 'Method tidak diizinkan. Gunakan GET.');
    exit;
}

// Validasi parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo jsonResponse(null, false, 'Parameter ID diperlukan');
    exit;
}

$id = $_GET['id'];

try {
    // Inisialisasi AccurateAPI
    $api = new AccurateAPI();
    
    // Dapatkan data price category detail dari API
    $result = $api->getPriceCategoryDetail($id);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        echo jsonResponse(null, false, $result['message'] ?? 'Failed to fetch price category detail');
    }
} catch (Exception $e) {
    echo jsonResponse(null, false, 'Error: ' . $e->getMessage());
}
?>
?>
