<?php
/**
 * API untuk mendapatkan detail sales order dalam format JSON
 * File: /salesorder/detail_so.php
 * HTTP Method: GET
 * Scope: sales_order_view
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
    
    // Dapatkan data sales order detail dari API
    $result = $api->getSalesOrderDetail($id);
    
    if ($result['success']) {
        // Hapus raw_response dari output
        if (isset($result['raw_response'])) {
            unset($result['raw_response']);
        }
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        echo jsonResponse(null, false, $result['message'] ?? 'Failed to fetch sales order detail');
    }
} catch (Exception $e) {
    echo jsonResponse(null, false, 'Error: ' . $e->getMessage());
}
?>
